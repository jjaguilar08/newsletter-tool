<?php

declare(strict_types=1);

use App\Models\User;

test('an authenticated user can log out', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/logout');

    $response->assertNoContent();
    $this->assertGuest('web');
});

test('a guest cannot log out', function () {
    $response = $this->postJson('/api/logout');

    $response->assertUnauthorized();
});
