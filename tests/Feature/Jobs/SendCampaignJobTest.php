<?php

declare(strict_types=1);

use App\Enums\CampaignSendStatus;
use App\Enums\CampaignStatus;
use App\Jobs\SendCampaignJob;
use App\Mail\CampaignMail;
use App\Models\Campaign;
use App\Models\CampaignSend;
use App\Models\Subscriber;
use Illuminate\Support\Facades\Mail;

test('only subscribed recipients get a campaign send row and an email', function () {
    Mail::fake();

    $campaign = Campaign::factory()->create(['status' => CampaignStatus::Sending]);
    $subscribed = Subscriber::factory()->count(2)->create();
    $unsubscribed = Subscriber::factory()->unsubscribed()->create();
    $bounced = Subscriber::factory()->bounced()->create();

    (new SendCampaignJob($campaign))->handle();

    expect(CampaignSend::where('campaign_id', $campaign->id)->count())->toBe(2);

    foreach ($subscribed as $subscriber) {
        $send = CampaignSend::where('campaign_id', $campaign->id)
            ->where('subscriber_id', $subscriber->id)
            ->firstOrFail();

        expect($send->status)->toBe(CampaignSendStatus::Sent);
        expect($send->sent_at)->not->toBeNull();
    }

    expect(CampaignSend::where('subscriber_id', $unsubscribed->id)->exists())->toBeFalse();
    expect(CampaignSend::where('subscriber_id', $bounced->id)->exists())->toBeFalse();

    Mail::assertSent(CampaignMail::class, 2);
});

test('the campaign is marked sent after the job runs', function () {
    Mail::fake();

    $campaign = Campaign::factory()->create(['status' => CampaignStatus::Sending]);
    Subscriber::factory()->count(2)->create();

    (new SendCampaignJob($campaign))->handle();

    $campaign->refresh();
    expect($campaign->status)->toBe(CampaignStatus::Sent);
    expect($campaign->sent_at)->not->toBeNull();
});

test('a per-subscriber send failure is recorded but does not stop the rest of the batch or leave the campaign stuck', function () {
    Mail::fake();

    $campaign = Campaign::factory()->create(['status' => CampaignStatus::Sending]);
    $failing = Subscriber::factory()->create(['email' => 'fails@example.com']);
    $succeeding = Subscriber::factory()->count(2)->create();

    // SendCampaignJob::sendMailTo() is `protected` specifically so a test can
    // override just that one call - simulating one subscriber's send throwing
    // (e.g. a transport-level failure) without mocking the whole Mail facade
    // or hitting a real provider. Every other subscriber still goes through
    // the real sendMailTo() -> Mail::fake() path.
    $job = new class($campaign) extends SendCampaignJob
    {
        protected function sendMailTo(Subscriber $subscriber): void
        {
            if ($subscriber->email === 'fails@example.com') {
                throw new RuntimeException('Simulated transport failure');
            }

            parent::sendMailTo($subscriber);
        }
    };

    $job->handle();

    $failingSend = CampaignSend::where('subscriber_id', $failing->id)->firstOrFail();
    expect($failingSend->status)->toBe(CampaignSendStatus::Failed);
    expect($failingSend->error_message)->toBe('Simulated transport failure');

    foreach ($succeeding as $subscriber) {
        $send = CampaignSend::where('subscriber_id', $subscriber->id)->firstOrFail();
        expect($send->status)->toBe(CampaignSendStatus::Sent);
    }

    $campaign->refresh();
    expect($campaign->status)->toBe(CampaignStatus::Sent);
    expect($campaign->sent_at)->not->toBeNull();

    Mail::assertSent(CampaignMail::class, 2);
});

test('the unsubscribe link embedded in the email uses the recipient\'s plaintext token', function () {
    Mail::fake();

    $campaign = Campaign::factory()->create(['status' => CampaignStatus::Sending]);
    $subscriber = Subscriber::factory()->create();

    (new SendCampaignJob($campaign))->handle();

    Mail::assertSent(CampaignMail::class, function (CampaignMail $mail) use ($subscriber) {
        $rendered = $mail->render();

        return str_contains($rendered, $subscriber->unsubscribe_token);
    });
});
