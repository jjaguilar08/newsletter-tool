<?php

declare(strict_types=1);

use App\Enums\CampaignSendStatus;
use App\Enums\CampaignStatus;
use App\Enums\UserRole;
use App\Models\Campaign;
use App\Models\CampaignSend;
use App\Models\Subscriber;
use App\Models\User;

test('dashboard stats reflect seeded counts', function () {
    $staff = User::factory()->create();

    Subscriber::factory()->count(3)->create();
    Subscriber::factory()->unsubscribed()->count(2)->create();
    Subscriber::factory()->bounced()->create();

    Campaign::factory()->count(2)->create(['status' => CampaignStatus::Draft]);
    Campaign::factory()->scheduled()->create();
    Campaign::factory()->create(['status' => CampaignStatus::Sending]);
    $sent = Campaign::factory()->sent()->count(3)->create();

    $sendCampaign = $sent->first();
    $sendSubscriber = Subscriber::factory()->create();

    CampaignSend::factory()->count(4)->create([
        'campaign_id' => $sendCampaign->id,
        'subscriber_id' => $sendSubscriber->id,
        'status' => CampaignSendStatus::Sent,
    ]);
    CampaignSend::factory()->count(2)->create([
        'campaign_id' => $sendCampaign->id,
        'subscriber_id' => $sendSubscriber->id,
        'status' => CampaignSendStatus::Failed,
    ]);

    $response = $this->actingAs($staff)->getJson('/api/dashboard/stats');

    $response->assertOk();
    $response->assertJsonPath('subscribers.subscribed', 4);
    $response->assertJsonPath('subscribers.unsubscribed', 2);
    $response->assertJsonPath('subscribers.bounced', 1);
    $response->assertJsonPath('campaigns.draft', 2);
    $response->assertJsonPath('campaigns.scheduled', 1);
    $response->assertJsonPath('campaigns.sending', 1);
    $response->assertJsonPath('campaigns.sent', 3);
    $response->assertJsonCount(5, 'recent_campaigns');
    $response->assertJsonPath('campaign_sends.sent', 4);
    $response->assertJsonPath('campaign_sends.failed', 2);

    expect($sent->pluck('subject')->contains($response->json('recent_campaigns.0.subject')))->toBeTrue();
});

test('a guest cannot view dashboard stats', function () {
    $response = $this->getJson('/api/dashboard/stats');

    $response->assertUnauthorized();
});

test('a non-staff user cannot view dashboard stats', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $response = $this->actingAs($admin)->getJson('/api/dashboard/stats');

    $response->assertForbidden();
});
