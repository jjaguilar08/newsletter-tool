<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\Subscriber;
use App\Models\User;

test('a staff user can view a subscriber', function () {
    $staff = User::factory()->create();
    $subscriber = Subscriber::factory()->create();

    $response = $this->actingAs($staff)->getJson("/api/subscribers/{$subscriber->id}");

    $response->assertOk();
    $response->assertJsonPath('data.id', $subscriber->id);
    $response->assertJsonMissingPath('data.unsubscribe_token');
});

test('viewing a missing subscriber returns 404', function () {
    $staff = User::factory()->create();

    $response = $this->actingAs($staff)->getJson('/api/subscribers/999999');

    $response->assertNotFound();
});

test('a guest cannot view a subscriber', function () {
    $subscriber = Subscriber::factory()->create();

    $response = $this->getJson("/api/subscribers/{$subscriber->id}");

    $response->assertUnauthorized();
});

test('a non-staff user cannot view a subscriber', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $subscriber = Subscriber::factory()->create();

    $response = $this->actingAs($admin)->getJson("/api/subscribers/{$subscriber->id}");

    $response->assertForbidden();
});
