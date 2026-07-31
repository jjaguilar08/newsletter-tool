<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('a staff user can upload a campaign image asset', function () {
    Storage::fake('public');
    $staff = User::factory()->create();

    // create(), not image() - image() shells out to the GD extension to
    // generate real pixel data, which isn't installed in this environment.
    // The `mimes` rule only maps the reported MIME type to an extension
    // (Symfony's File::guessExtension(), no content sniffing), so a
    // zero-byte fake with an explicit mimeType satisfies it the same way.
    $file = UploadedFile::fake()->create('banner.png', 100, 'image/png');

    $response = $this->actingAs($staff)->postJson('/api/campaigns/assets', [
        'image' => $file,
    ]);

    $response->assertOk();
    $response->assertJsonStructure(['url']);

    $url = $response->json('url');
    expect($url)->toContain('/storage/campaign-assets/');

    $path = 'campaign-assets/'.basename((string) parse_url($url, PHP_URL_PATH));
    Storage::disk('public')->assertExists($path);
});

test('uploading a non-image file is rejected', function () {
    Storage::fake('public');
    $staff = User::factory()->create();

    $file = UploadedFile::fake()->create('not-an-image.txt', 10, 'text/plain');

    $response = $this->actingAs($staff)->postJson('/api/campaigns/assets', [
        'image' => $file,
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['image']);
});

test('uploading an oversized image is rejected', function () {
    Storage::fake('public');
    $staff = User::factory()->create();

    $file = UploadedFile::fake()->create('huge.jpg', 5121, 'image/jpeg');

    $response = $this->actingAs($staff)->postJson('/api/campaigns/assets', [
        'image' => $file,
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['image']);
});

test('uploading without a file is rejected', function () {
    Storage::fake('public');
    $staff = User::factory()->create();

    $response = $this->actingAs($staff)->postJson('/api/campaigns/assets', []);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['image']);
});

test('a guest cannot upload a campaign asset', function () {
    Storage::fake('public');

    $file = UploadedFile::fake()->create('banner.png', 100, 'image/png');

    $response = $this->postJson('/api/campaigns/assets', [
        'image' => $file,
    ]);

    $response->assertUnauthorized();
});

test('a non-staff user cannot upload a campaign asset', function () {
    Storage::fake('public');
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $file = UploadedFile::fake()->create('banner.png', 100, 'image/png');

    $response = $this->actingAs($admin)->postJson('/api/campaigns/assets', [
        'image' => $file,
    ]);

    $response->assertForbidden();
});
