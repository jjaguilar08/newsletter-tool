<?php

declare(strict_types=1);

use App\Enums\CampaignStatus;
use App\Jobs\SendCampaignJob;
use App\Models\Campaign;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Queue;

test('a due scheduled campaign is claimed and its job dispatched', function () {
    Queue::fake();

    $campaign = Campaign::factory()->create([
        'status' => CampaignStatus::Scheduled,
        'scheduled_at' => Carbon::parse('2026-08-01 09:00:00'),
    ]);

    $this->travelTo(Carbon::parse('2026-08-01 09:00:01'));

    $this->artisan('campaigns:dispatch-scheduled');

    expect($campaign->fresh()->status)->toBe(CampaignStatus::Sending);
    Queue::assertPushed(SendCampaignJob::class, fn ($job) => $job->campaign->id === $campaign->id);
});

test('a scheduled campaign whose time has not arrived yet is left alone', function () {
    Queue::fake();

    $campaign = Campaign::factory()->create([
        'status' => CampaignStatus::Scheduled,
        'scheduled_at' => Carbon::parse('2026-08-01 09:00:00'),
    ]);

    $this->travelTo(Carbon::parse('2026-08-01 08:59:59'));

    $this->artisan('campaigns:dispatch-scheduled');

    expect($campaign->fresh()->status)->toBe(CampaignStatus::Scheduled);
    Queue::assertNotPushed(SendCampaignJob::class);
});

test('a draft campaign is ignored by the dispatcher', function () {
    Queue::fake();

    $campaign = Campaign::factory()->create([
        'status' => CampaignStatus::Draft,
        'scheduled_at' => Carbon::parse('2026-08-01 09:00:00'),
    ]);

    $this->travelTo(Carbon::parse('2026-08-01 09:00:01'));

    $this->artisan('campaigns:dispatch-scheduled');

    expect($campaign->fresh()->status)->toBe(CampaignStatus::Draft);
    Queue::assertNotPushed(SendCampaignJob::class);
});
