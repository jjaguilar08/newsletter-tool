<?php

declare(strict_types=1);

use App\Enums\SubscriberStatus;
use App\Enums\UserRole;
use App\Models\Subscriber;
use App\Models\User;

test('a staff user can create a subscriber', function () {
    $staff = User::factory()->create();

    $response = $this->actingAs($staff)->postJson('/api/subscribers', [
        'email' => 'new@example.com',
        'name' => 'New Subscriber',
        'status' => SubscriberStatus::Subscribed->value,
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.email', 'new@example.com');
    $response->assertJsonMissingPath('data.unsubscribe_token');

    $subscriber = Subscriber::where('email', 'new@example.com')->firstOrFail();
    expect($subscriber->unsubscribe_token)->not->toBeNull();
});

test('creating a subscriber without a status defaults to subscribed', function () {
    $staff = User::factory()->create();

    $response = $this->actingAs($staff)->postJson('/api/subscribers', [
        'email' => 'no-status@example.com',
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.status', SubscriberStatus::Subscribed->value);

    $subscriber = Subscriber::where('email', 'no-status@example.com')->firstOrFail();
    expect($subscriber->status)->toBe(SubscriberStatus::Subscribed);
});

test('creating a subscriber requires a unique email', function () {
    $staff = User::factory()->create();
    Subscriber::factory()->create(['email' => 'taken@example.com']);

    $response = $this->actingAs($staff)->postJson('/api/subscribers', [
        'email' => 'taken@example.com',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('email');
});

test('creating a subscriber stores the email lowercased', function () {
    $staff = User::factory()->create();

    $response = $this->actingAs($staff)->postJson('/api/subscribers', [
        'email' => 'Mixed.Case@Example.COM',
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.email', 'mixed.case@example.com');
});

test('creating a subscriber requires a unique email regardless of case', function () {
    $staff = User::factory()->create();
    Subscriber::factory()->create(['email' => 'alice@example.com']);

    $response = $this->actingAs($staff)->postJson('/api/subscribers', [
        'email' => 'Alice@Example.COM',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('email');
    expect(Subscriber::where('email', 'alice@example.com')->count())->toBe(1);
});

test('creating a subscriber rejects an invalid status', function () {
    $staff = User::factory()->create();

    $response = $this->actingAs($staff)->postJson('/api/subscribers', [
        'email' => 'valid@example.com',
        'status' => 'not-a-real-status',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('status');
});

test('a guest cannot create a subscriber', function () {
    $response = $this->postJson('/api/subscribers', ['email' => 'guest@example.com']);

    $response->assertUnauthorized();
});

test('a non-staff user cannot create a subscriber', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $response = $this->actingAs($admin)->postJson('/api/subscribers', ['email' => 'blocked@example.com']);

    $response->assertForbidden();
});
