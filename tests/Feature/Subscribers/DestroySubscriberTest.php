<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\Subscriber;
use App\Models\User;

test('a staff user can delete a subscriber', function () {
    $staff = User::factory()->create();
    $subscriber = Subscriber::factory()->create();

    $response = $this->actingAs($staff)->deleteJson("/api/subscribers/{$subscriber->id}");

    $response->assertNoContent();
    expect(Subscriber::find($subscriber->id))->toBeNull();
});

test('a guest cannot delete a subscriber', function () {
    $subscriber = Subscriber::factory()->create();

    $response = $this->deleteJson("/api/subscribers/{$subscriber->id}");

    $response->assertUnauthorized();
    expect(Subscriber::find($subscriber->id))->not->toBeNull();
});

test('a non-staff user cannot delete a subscriber', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $subscriber = Subscriber::factory()->create();

    $response = $this->actingAs($admin)->deleteJson("/api/subscribers/{$subscriber->id}");

    $response->assertForbidden();
    expect(Subscriber::find($subscriber->id))->not->toBeNull();
});
