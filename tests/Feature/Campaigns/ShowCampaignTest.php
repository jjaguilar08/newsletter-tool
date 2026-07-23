<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\Campaign;
use App\Models\User;

test('a staff user can view a campaign', function () {
    $staff = User::factory()->create();
    $campaign = Campaign::factory()->create();

    $response = $this->actingAs($staff)->getJson("/api/campaigns/{$campaign->id}");

    $response->assertOk();
    $response->assertJsonPath('data.id', $campaign->id);
});

test('viewing a missing campaign returns 404', function () {
    $staff = User::factory()->create();

    $response = $this->actingAs($staff)->getJson('/api/campaigns/999999');

    $response->assertNotFound();
});

test('a guest cannot view a campaign', function () {
    $campaign = Campaign::factory()->create();

    $response = $this->getJson("/api/campaigns/{$campaign->id}");

    $response->assertUnauthorized();
});

test('a non-staff user cannot view a campaign', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $campaign = Campaign::factory()->create();

    $response = $this->actingAs($admin)->getJson("/api/campaigns/{$campaign->id}");

    $response->assertForbidden();
});
