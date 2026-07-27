<?php

declare(strict_types=1);

use App\Enums\SubscriberStatus;
use App\Enums\UserRole;
use App\Models\Subscriber;
use App\Models\User;

test('a staff user can update a subscriber', function () {
    $staff = User::factory()->create();
    $subscriber = Subscriber::factory()->create(['name' => 'Old Name']);

    $response = $this->actingAs($staff)->putJson("/api/subscribers/{$subscriber->id}", [
        'name' => 'New Name',
        'status' => SubscriberStatus::Unsubscribed->value,
    ]);

    $response->assertOk();
    $response->assertJsonPath('data.name', 'New Name');
    $response->assertJsonPath('data.status', SubscriberStatus::Unsubscribed->value);
    expect($subscriber->fresh()->name)->toBe('New Name');
});

test('updating a subscriber to keep the same email does not fail uniqueness', function () {
    $staff = User::factory()->create();
    $subscriber = Subscriber::factory()->create(['email' => 'same@example.com']);

    $response = $this->actingAs($staff)->putJson("/api/subscribers/{$subscriber->id}", [
        'email' => 'same@example.com',
    ]);

    $response->assertOk();
});

test('updating a subscriber to a taken email fails validation', function () {
    $staff = User::factory()->create();
    Subscriber::factory()->create(['email' => 'taken@example.com']);
    $subscriber = Subscriber::factory()->create(['email' => 'mine@example.com']);

    $response = $this->actingAs($staff)->putJson("/api/subscribers/{$subscriber->id}", [
        'email' => 'taken@example.com',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('email');
});

test('updating a subscriber to a taken email fails validation regardless of case', function () {
    $staff = User::factory()->create();
    Subscriber::factory()->create(['email' => 'taken@example.com']);
    $subscriber = Subscriber::factory()->create(['email' => 'mine@example.com']);

    $response = $this->actingAs($staff)->putJson("/api/subscribers/{$subscriber->id}", [
        'email' => 'Taken@Example.COM',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('email');
    expect($subscriber->fresh()->email)->toBe('mine@example.com');
});

test('updating a subscriber to keep the same email in a different case does not fail uniqueness', function () {
    $staff = User::factory()->create();
    $subscriber = Subscriber::factory()->create(['email' => 'same@example.com']);

    $response = $this->actingAs($staff)->putJson("/api/subscribers/{$subscriber->id}", [
        'email' => 'Same@Example.COM',
    ]);

    $response->assertOk();
    expect($subscriber->fresh()->email)->toBe('same@example.com');
});

test('updating a subscriber rejects an invalid status', function () {
    $staff = User::factory()->create();
    $subscriber = Subscriber::factory()->create();

    $response = $this->actingAs($staff)->putJson("/api/subscribers/{$subscriber->id}", [
        'status' => 'not-a-real-status',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('status');
});

test('a guest cannot update a subscriber', function () {
    $subscriber = Subscriber::factory()->create();

    $response = $this->putJson("/api/subscribers/{$subscriber->id}", ['name' => 'Nope']);

    $response->assertUnauthorized();
});

test('a non-staff user cannot update a subscriber', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $subscriber = Subscriber::factory()->create();

    $response = $this->actingAs($admin)->putJson("/api/subscribers/{$subscriber->id}", ['name' => 'Nope']);

    $response->assertForbidden();
});
