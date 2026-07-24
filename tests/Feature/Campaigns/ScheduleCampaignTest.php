<?php

declare(strict_types=1);

use App\Enums\CampaignStatus;
use App\Enums\UserRole;
use App\Models\Campaign;
use App\Models\User;

test('a staff user can schedule a draft campaign for a future time', function () {
    $staff = User::factory()->create();
    $campaign = Campaign::factory()->create(['status' => CampaignStatus::Draft]);
    $scheduledAt = now()->addDay();

    $response = $this->actingAs($staff)->postJson("/api/campaigns/{$campaign->id}/schedule", [
        'scheduled_at' => $scheduledAt->toIso8601String(),
    ]);

    $response->assertOk();
    $response->assertJsonPath('data.status', CampaignStatus::Scheduled->value);

    $campaign->refresh();
    expect($campaign->status)->toBe(CampaignStatus::Scheduled);
    expect($campaign->scheduled_at->timestamp)->toBe($scheduledAt->timestamp);
});

test('scheduling with a past scheduled_at is rejected', function () {
    $staff = User::factory()->create();
    $campaign = Campaign::factory()->create(['status' => CampaignStatus::Draft]);

    $response = $this->actingAs($staff)->postJson("/api/campaigns/{$campaign->id}/schedule", [
        'scheduled_at' => now()->subDay()->toIso8601String(),
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('scheduled_at');
    expect($campaign->fresh()->status)->toBe(CampaignStatus::Draft);
});

test('scheduling requires a scheduled_at', function () {
    $staff = User::factory()->create();
    $campaign = Campaign::factory()->create(['status' => CampaignStatus::Draft]);

    $response = $this->actingAs($staff)->postJson("/api/campaigns/{$campaign->id}/schedule", []);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('scheduled_at');
});

test('scheduling a non-draft campaign returns 409', function () {
    $staff = User::factory()->create();
    $campaign = Campaign::factory()->create(['status' => CampaignStatus::Sending]);

    $response = $this->actingAs($staff)->postJson("/api/campaigns/{$campaign->id}/schedule", [
        'scheduled_at' => now()->addDay()->toIso8601String(),
    ]);

    $response->assertStatus(409);
    expect($campaign->fresh()->status)->toBe(CampaignStatus::Sending);
});

test('a guest cannot schedule a campaign', function () {
    $campaign = Campaign::factory()->create(['status' => CampaignStatus::Draft]);

    $response = $this->postJson("/api/campaigns/{$campaign->id}/schedule", [
        'scheduled_at' => now()->addDay()->toIso8601String(),
    ]);

    $response->assertUnauthorized();
    expect($campaign->fresh()->status)->toBe(CampaignStatus::Draft);
});

test('a non-staff user cannot schedule a campaign', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $campaign = Campaign::factory()->create(['status' => CampaignStatus::Draft]);

    $response = $this->actingAs($admin)->postJson("/api/campaigns/{$campaign->id}/schedule", [
        'scheduled_at' => now()->addDay()->toIso8601String(),
    ]);

    $response->assertForbidden();
    expect($campaign->fresh()->status)->toBe(CampaignStatus::Draft);
});
