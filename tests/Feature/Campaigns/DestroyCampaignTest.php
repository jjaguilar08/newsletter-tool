<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\Campaign;
use App\Models\User;

test('a staff user can delete a campaign', function () {
    $staff = User::factory()->create();
    $campaign = Campaign::factory()->create();

    $response = $this->actingAs($staff)->deleteJson("/api/campaigns/{$campaign->id}");

    $response->assertNoContent();
    expect(Campaign::find($campaign->id))->toBeNull();
});

test('a guest cannot delete a campaign', function () {
    $campaign = Campaign::factory()->create();

    $response = $this->deleteJson("/api/campaigns/{$campaign->id}");

    $response->assertUnauthorized();
    expect(Campaign::find($campaign->id))->not->toBeNull();
});

test('a non-staff user cannot delete a campaign', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $campaign = Campaign::factory()->create();

    $response = $this->actingAs($admin)->deleteJson("/api/campaigns/{$campaign->id}");

    $response->assertForbidden();
    expect(Campaign::find($campaign->id))->not->toBeNull();
});
