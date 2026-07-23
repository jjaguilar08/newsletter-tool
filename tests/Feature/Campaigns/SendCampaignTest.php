<?php

declare(strict_types=1);

use App\Enums\CampaignStatus;
use App\Enums\UserRole;
use App\Jobs\SendCampaignJob;
use App\Models\Campaign;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

test('a staff user can send a draft campaign', function () {
    Queue::fake();

    $staff = User::factory()->create();
    $campaign = Campaign::factory()->create(['status' => CampaignStatus::Draft]);

    $response = $this->actingAs($staff)->postJson("/api/campaigns/{$campaign->id}/send");

    $response->assertOk();
    $response->assertJsonPath('data.status', CampaignStatus::Sending->value);

    expect($campaign->fresh()->status)->toBe(CampaignStatus::Sending);

    Queue::assertPushed(SendCampaignJob::class, fn ($job) => $job->campaign->id === $campaign->id);
});

test('sending a non-draft campaign returns 409', function () {
    Queue::fake();

    $staff = User::factory()->create();
    $campaign = Campaign::factory()->create(['status' => CampaignStatus::Sending]);

    $response = $this->actingAs($staff)->postJson("/api/campaigns/{$campaign->id}/send");

    $response->assertStatus(409);

    expect($campaign->fresh()->status)->toBe(CampaignStatus::Sending);
    Queue::assertNotPushed(SendCampaignJob::class);
});

test('sending an already-sent campaign returns 409', function () {
    Queue::fake();

    $staff = User::factory()->create();
    $campaign = Campaign::factory()->sent()->create();

    $response = $this->actingAs($staff)->postJson("/api/campaigns/{$campaign->id}/send");

    $response->assertStatus(409);
    Queue::assertNotPushed(SendCampaignJob::class);
});

test('a guest cannot send a campaign', function () {
    Queue::fake();

    $campaign = Campaign::factory()->create(['status' => CampaignStatus::Draft]);

    $response = $this->postJson("/api/campaigns/{$campaign->id}/send");

    $response->assertUnauthorized();
    expect($campaign->fresh()->status)->toBe(CampaignStatus::Draft);
    Queue::assertNotPushed(SendCampaignJob::class);
});

test('a non-staff user cannot send a campaign', function () {
    Queue::fake();

    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $campaign = Campaign::factory()->create(['status' => CampaignStatus::Draft]);

    $response = $this->actingAs($admin)->postJson("/api/campaigns/{$campaign->id}/send");

    $response->assertForbidden();
    expect($campaign->fresh()->status)->toBe(CampaignStatus::Draft);
    Queue::assertNotPushed(SendCampaignJob::class);
});
