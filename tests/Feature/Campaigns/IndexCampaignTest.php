<?php

declare(strict_types=1);

use App\Enums\CampaignStatus;
use App\Enums\UserRole;
use App\Models\Campaign;
use App\Models\User;

test('a staff user can list campaigns', function () {
    $staff = User::factory()->create();
    Campaign::factory()->count(3)->create();

    $response = $this->actingAs($staff)->getJson('/api/campaigns');

    $response->assertOk();
    $response->assertJsonCount(3, 'data');
});

test('a guest cannot list campaigns', function () {
    $response = $this->getJson('/api/campaigns');

    $response->assertUnauthorized();
});

test('a non-staff user cannot list campaigns', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $response = $this->actingAs($admin)->getJson('/api/campaigns');

    $response->assertForbidden();
});

test('campaigns can be filtered by status', function () {
    $staff = User::factory()->create();
    Campaign::factory()->count(2)->create(['status' => CampaignStatus::Draft]);
    Campaign::factory()->sent()->count(3)->create();

    $response = $this->actingAs($staff)->getJson('/api/campaigns?status=sent');

    $response->assertOk();
    $response->assertJsonCount(3, 'data');
});

test('campaigns are paginated', function () {
    $staff = User::factory()->create();
    Campaign::factory()->count(20)->create();

    $response = $this->actingAs($staff)->getJson('/api/campaigns');

    $response->assertOk();
    $response->assertJsonCount(15, 'data');
    $response->assertJsonPath('meta.total', 20);
    $response->assertJsonPath('meta.per_page', 15);

    $secondPage = $this->actingAs($staff)->getJson('/api/campaigns?page=2');
    $secondPage->assertJsonCount(5, 'data');
});
