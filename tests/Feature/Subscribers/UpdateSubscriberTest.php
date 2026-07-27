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

test('transitioning to unsubscribed sets unsubscribed_at', function () {
    $staff = User::factory()->create();
    $subscriber = Subscriber::factory()->create(['status' => SubscriberStatus::Subscribed]);

    $response = $this->actingAs($staff)->putJson("/api/subscribers/{$subscriber->id}", [
        'status' => SubscriberStatus::Unsubscribed->value,
    ]);

    $response->assertOk();
    $subscriber->refresh();
    expect($subscriber->status)->toBe(SubscriberStatus::Unsubscribed);
    expect($subscriber->unsubscribed_at)->not->toBeNull();
});

test('transitioning from bounced to unsubscribed sets unsubscribed_at', function () {
    $staff = User::factory()->create();
    $subscriber = Subscriber::factory()->bounced()->create();

    $response = $this->actingAs($staff)->putJson("/api/subscribers/{$subscriber->id}", [
        'status' => SubscriberStatus::Unsubscribed->value,
    ]);

    $response->assertOk();
    expect($subscriber->fresh()->unsubscribed_at)->not->toBeNull();
});

test('re-saving the same unsubscribed status leaves unsubscribed_at untouched', function () {
    $staff = User::factory()->create();
    $subscriber = Subscriber::factory()->unsubscribed()->create();
    $originalUnsubscribedAt = $subscriber->unsubscribed_at;

    $this->travel(1)->hour();

    $response = $this->actingAs($staff)->putJson("/api/subscribers/{$subscriber->id}", [
        'status' => SubscriberStatus::Unsubscribed->value,
    ]);

    $response->assertOk();
    expect($subscriber->fresh()->unsubscribed_at->toIso8601String())->toBe($originalUnsubscribedAt->toIso8601String());
});

test('transitioning to subscribed (reactivation) sets subscribed_at and clears unsubscribed_at', function () {
    $staff = User::factory()->create();
    $subscriber = Subscriber::factory()->unsubscribed()->create();

    $this->travel(1)->hour();

    $response = $this->actingAs($staff)->putJson("/api/subscribers/{$subscriber->id}", [
        'status' => SubscriberStatus::Subscribed->value,
    ]);

    $response->assertOk();
    $subscriber->refresh();
    expect($subscriber->status)->toBe(SubscriberStatus::Subscribed);
    expect($subscriber->unsubscribed_at)->toBeNull();
    expect($subscriber->subscribed_at->isAfter(now()->subMinutes(5)))->toBeTrue();
});

test('transitioning from bounced to subscribed sets subscribed_at', function () {
    $staff = User::factory()->create();
    $subscriber = Subscriber::factory()->bounced()->create();

    $response = $this->actingAs($staff)->putJson("/api/subscribers/{$subscriber->id}", [
        'status' => SubscriberStatus::Subscribed->value,
    ]);

    $response->assertOk();
    $subscriber->refresh();
    expect($subscriber->unsubscribed_at)->toBeNull();
    expect($subscriber->subscribed_at)->not->toBeNull();
});

test('transitioning to bounced touches neither timestamp', function () {
    $staff = User::factory()->create();
    $subscriber = Subscriber::factory()->create(['status' => SubscriberStatus::Subscribed]);
    $originalSubscribedAt = $subscriber->subscribed_at;

    $this->travel(1)->hour();

    $response = $this->actingAs($staff)->putJson("/api/subscribers/{$subscriber->id}", [
        'status' => SubscriberStatus::Bounced->value,
    ]);

    $response->assertOk();
    $subscriber->refresh();
    expect($subscriber->status)->toBe(SubscriberStatus::Bounced);
    expect($subscriber->unsubscribed_at)->toBeNull();
    expect($subscriber->subscribed_at->toIso8601String())->toBe($originalSubscribedAt->toIso8601String());
});

test('updating without a status field writes no timestamps', function () {
    $staff = User::factory()->create();
    $subscriber = Subscriber::factory()->create(['status' => SubscriberStatus::Subscribed]);
    $originalSubscribedAt = $subscriber->subscribed_at;

    $this->travel(1)->hour();

    $response = $this->actingAs($staff)->putJson("/api/subscribers/{$subscriber->id}", [
        'name' => 'Just a name change',
    ]);

    $response->assertOk();
    $subscriber->refresh();
    expect($subscriber->unsubscribed_at)->toBeNull();
    expect($subscriber->subscribed_at->toIso8601String())->toBe($originalSubscribedAt->toIso8601String());
});

test('re-saving the same subscribed status leaves subscribed_at untouched', function () {
    $staff = User::factory()->create();
    $subscriber = Subscriber::factory()->create(['status' => SubscriberStatus::Subscribed]);
    $originalSubscribedAt = $subscriber->subscribed_at;

    $this->travel(1)->hour();

    $response = $this->actingAs($staff)->putJson("/api/subscribers/{$subscriber->id}", [
        'status' => SubscriberStatus::Subscribed->value,
    ]);

    $response->assertOk();
    expect($subscriber->fresh()->subscribed_at->toIso8601String())->toBe($originalSubscribedAt->toIso8601String());
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
