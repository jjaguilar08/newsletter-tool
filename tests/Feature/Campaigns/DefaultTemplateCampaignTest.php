<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\User;

test('a staff user can fetch the default campaign template', function () {
    $staff = User::factory()->create();

    $response = $this->actingAs($staff)->getJson('/api/campaigns/default-template');

    $response->assertOk();
    $response->assertJsonStructure(['html']);
    expect($response->json('html'))->toContain('<!DOCTYPE html>');
});

test('a guest cannot fetch the default campaign template', function () {
    $response = $this->getJson('/api/campaigns/default-template');

    $response->assertUnauthorized();
});

test('a non-staff user cannot fetch the default campaign template', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $response = $this->actingAs($admin)->getJson('/api/campaigns/default-template');

    $response->assertForbidden();
});
