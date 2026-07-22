<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\User;

test('an authenticated staff user can fetch their own user record', function () {
    $user = User::factory()->create(['role' => UserRole::Staff]);

    $response = $this->actingAs($user)->getJson('/api/user');

    $response->assertOk();
    $response->assertJsonFragment(['email' => $user->email]);
});

test('a guest cannot fetch the user record', function () {
    $response = $this->getJson('/api/user');

    $response->assertUnauthorized();
});
