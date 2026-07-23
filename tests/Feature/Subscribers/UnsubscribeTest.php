<?php

declare(strict_types=1);

use App\Enums\SubscriberStatus;
use App\Models\Subscriber;
use Illuminate\Support\Str;

test('a valid token unsubscribes the subscriber and persists the timestamp', function () {
    $token = Str::random(40);
    $subscriber = Subscriber::factory()->create([
        'unsubscribe_token' => $token,
    ]);

    $response = $this->getJson("/api/unsubscribe/{$token}");

    $response->assertOk();

    $subscriber->refresh();
    expect($subscriber->status)->toBe(SubscriberStatus::Unsubscribed);
    expect($subscriber->unsubscribed_at)->not->toBeNull();
});

test('hitting an already-unsubscribed token again still succeeds', function () {
    $token = Str::random(40);
    $subscriber = Subscriber::factory()->unsubscribed()->create([
        'unsubscribe_token' => $token,
    ]);
    $originalUnsubscribedAt = $subscriber->unsubscribed_at;

    $response = $this->getJson("/api/unsubscribe/{$token}");

    $response->assertOk();

    $subscriber->refresh();
    expect($subscriber->status)->toBe(SubscriberStatus::Unsubscribed);
    expect($subscriber->unsubscribed_at->toIso8601String())->toBe($originalUnsubscribedAt->toIso8601String());
});

test('an invalid token returns 404', function () {
    $response = $this->getJson('/api/unsubscribe/not-a-real-token');

    $response->assertNotFound();
});

test('unsubscribe requires no authentication', function () {
    $token = Str::random(40);
    Subscriber::factory()->create([
        'unsubscribe_token' => $token,
    ]);

    $response = $this->getJson("/api/unsubscribe/{$token}");

    $response->assertOk();
});
