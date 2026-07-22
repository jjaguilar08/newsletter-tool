<?php

declare(strict_types=1);

use App\Enums\SubscriberStatus;
use App\Enums\UserRole;
use App\Models\Subscriber;
use App\Models\User;

test('a staff user can list subscribers', function () {
    $staff = User::factory()->create();
    Subscriber::factory()->count(3)->create();

    $response = $this->actingAs($staff)->getJson('/api/subscribers');

    $response->assertOk();
    $response->assertJsonCount(3, 'data');
});

test('a guest cannot list subscribers', function () {
    $response = $this->getJson('/api/subscribers');

    $response->assertUnauthorized();
});

test('a non-staff user cannot list subscribers', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $response = $this->actingAs($admin)->getJson('/api/subscribers');

    $response->assertForbidden();
});

test('subscribers can be filtered by status', function () {
    $staff = User::factory()->create();
    Subscriber::factory()->count(2)->create(['status' => SubscriberStatus::Subscribed]);
    Subscriber::factory()->unsubscribed()->count(3)->create();

    $response = $this->actingAs($staff)->getJson('/api/subscribers?status=unsubscribed');

    $response->assertOk();
    $response->assertJsonCount(3, 'data');
});

test('subscribers can be searched by email or name', function () {
    $staff = User::factory()->create();
    Subscriber::factory()->create(['email' => 'jane@example.com', 'name' => 'Jane Doe']);
    Subscriber::factory()->create(['email' => 'someone@example.com', 'name' => 'Jane Smith']);
    Subscriber::factory()->create(['email' => 'other@example.com', 'name' => 'No Match']);

    $response = $this->actingAs($staff)->getJson('/api/subscribers?search=Jane');

    $response->assertOk();
    $response->assertJsonCount(2, 'data');
});

test('subscribers are paginated', function () {
    $staff = User::factory()->create();
    Subscriber::factory()->count(20)->create();

    $response = $this->actingAs($staff)->getJson('/api/subscribers');

    $response->assertOk();
    $response->assertJsonCount(15, 'data');
    $response->assertJsonPath('meta.total', 20);
    $response->assertJsonPath('meta.per_page', 15);

    $secondPage = $this->actingAs($staff)->getJson('/api/subscribers?page=2');
    $secondPage->assertJsonCount(5, 'data');
});
