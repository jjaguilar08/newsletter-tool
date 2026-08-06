<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\Campaign;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('creates a staff user with the given email and password', function () {
    $response = $this->postJson('/api/testing/users', [
        'email' => 'e2e-fixture@example.com',
        'password' => 'a-real-password',
        'name' => 'E2E Fixture',
    ]);

    $response->assertCreated();
    $response->assertJsonPath('email', 'e2e-fixture@example.com');
    $response->assertJsonMissingPath('password');

    $user = User::where('email', 'e2e-fixture@example.com')->firstOrFail();
    expect($user->role)->toBe(UserRole::Staff);
    expect(Hash::check('a-real-password', $user->password))->toBeTrue();

    // The created user can actually log in with the password just sent -
    // the whole point of this endpoint.
    $login = $this->postJson('/api/login', [
        'email' => 'e2e-fixture@example.com',
        'password' => 'a-real-password',
    ]);
    $login->assertOk();
});

test('defaults the name when none is given', function () {
    $response = $this->postJson('/api/testing/users', [
        'email' => 'no-name@example.com',
        'password' => 'a-real-password',
    ]);

    $response->assertCreated();
    $response->assertJsonPath('name', 'E2E Test User');
});

test('validates required fields', function () {
    $response = $this->postJson('/api/testing/users', []);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['email', 'password']);
});

test('rejects a duplicate email', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $response = $this->postJson('/api/testing/users', [
        'email' => 'taken@example.com',
        'password' => 'a-real-password',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['email']);
});

test('deletes the user and cascades their campaigns and sends', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['created_by' => $user->id]);

    $response = $this->deleteJson("/api/testing/users/{$user->id}");

    $response->assertNoContent();
    expect(User::find($user->id))->toBeNull();
    expect(Campaign::find($campaign->id))->toBeNull();
});
