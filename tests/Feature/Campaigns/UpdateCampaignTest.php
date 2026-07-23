<?php

declare(strict_types=1);

use App\Enums\CampaignStatus;
use App\Enums\UserRole;
use App\Models\Campaign;
use App\Models\User;

test('a staff user can update a campaign', function () {
    $staff = User::factory()->create();
    $campaign = Campaign::factory()->create(['subject' => 'Old Subject']);

    $response = $this->actingAs($staff)->putJson("/api/campaigns/{$campaign->id}", [
        'subject' => 'New Subject',
        'content' => 'New content.',
    ]);

    $response->assertOk();
    $response->assertJsonPath('data.subject', 'New Subject');
    $response->assertJsonPath('data.content', 'New content.');
    expect($campaign->fresh()->subject)->toBe('New Subject');
});

test('updating a campaign requires subject and content to be non-empty when provided', function () {
    $staff = User::factory()->create();
    $campaign = Campaign::factory()->create();

    $response = $this->actingAs($staff)->putJson("/api/campaigns/{$campaign->id}", [
        'subject' => '',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('subject');
});

test('updating a campaign rejects an attempt to set status', function () {
    $staff = User::factory()->create();
    $campaign = Campaign::factory()->create(['status' => CampaignStatus::Draft]);

    $response = $this->actingAs($staff)->putJson("/api/campaigns/{$campaign->id}", [
        'status' => CampaignStatus::Sent->value,
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('status');
    expect($campaign->fresh()->status)->toBe(CampaignStatus::Draft);
});

test('a guest cannot update a campaign', function () {
    $campaign = Campaign::factory()->create();

    $response = $this->putJson("/api/campaigns/{$campaign->id}", ['subject' => 'Nope']);

    $response->assertUnauthorized();
});

test('a non-staff user cannot update a campaign', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $campaign = Campaign::factory()->create();

    $response = $this->actingAs($admin)->putJson("/api/campaigns/{$campaign->id}", ['subject' => 'Nope']);

    $response->assertForbidden();
});
