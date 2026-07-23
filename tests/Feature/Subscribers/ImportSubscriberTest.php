<?php

declare(strict_types=1);

use App\Enums\SubscriberStatus;
use App\Enums\UserRole;
use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Http\UploadedFile;

test('a staff user can import subscribers from a valid csv', function () {
    $staff = User::factory()->create();

    $csv = "email,name\nnew1@example.com,New One\nnew2@example.com,New Two\n";
    $file = UploadedFile::fake()->createWithContent('subscribers.csv', $csv);

    $response = $this->actingAs($staff)->postJson('/api/subscribers/import', ['file' => $file]);

    $response->assertOk();
    $response->assertJson([
        'created' => 2,
        'updated' => 0,
        'skipped' => 0,
        'skipped_rows' => [],
    ]);

    $subscriber = Subscriber::where('email', 'new1@example.com')->firstOrFail();
    expect($subscriber->name)->toBe('New One');
    expect($subscriber->status)->toBe(SubscriberStatus::Subscribed);
    expect($subscriber->subscribed_at)->not->toBeNull();
    expect($subscriber->unsubscribe_token)->not->toBeNull();
});

test('importing a csv with mixed valid and invalid rows produces correct counts', function () {
    $staff = User::factory()->create();
    Subscriber::factory()->create(['email' => 'existing@example.com', 'name' => 'Old Name']);

    $csv = "email,name\n".
        "existing@example.com,Updated Name\n".
        "not-an-email,Bad Row\n".
        ",Missing Email\n".
        "brandnew@example.com,Brand New\n";
    $file = UploadedFile::fake()->createWithContent('subscribers.csv', $csv);

    $response = $this->actingAs($staff)->postJson('/api/subscribers/import', ['file' => $file]);

    $response->assertOk();
    $response->assertJson([
        'created' => 1,
        'updated' => 1,
        'skipped' => 2,
    ]);
    $response->assertJsonPath('skipped_rows.0.row', 3);
    $response->assertJsonPath('skipped_rows.1.row', 4);

    $existing = Subscriber::where('email', 'existing@example.com')->firstOrFail();
    expect($existing->name)->toBe('Updated Name');
});

test('importing does not touch status or timestamps on an existing subscriber', function () {
    $staff = User::factory()->create();
    $existing = Subscriber::factory()->unsubscribed()->create(['email' => 'existing@example.com']);
    $originalUnsubscribedAt = $existing->unsubscribed_at;

    $csv = "email,name\nexisting@example.com,New Name\n";
    $file = UploadedFile::fake()->createWithContent('subscribers.csv', $csv);

    $this->actingAs($staff)->postJson('/api/subscribers/import', ['file' => $file])->assertOk();

    $existing->refresh();
    expect($existing->name)->toBe('New Name');
    expect($existing->status)->toBe(SubscriberStatus::Unsubscribed);
    expect($existing->unsubscribed_at->toIso8601String())->toBe($originalUnsubscribedAt->toIso8601String());
});

test('a guest cannot import subscribers', function () {
    $file = UploadedFile::fake()->createWithContent('subscribers.csv', "email,name\na@example.com,A\n");

    $response = $this->postJson('/api/subscribers/import', ['file' => $file]);

    $response->assertUnauthorized();
});

test('a non-staff user cannot import subscribers', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $file = UploadedFile::fake()->createWithContent('subscribers.csv', "email,name\na@example.com,A\n");

    $response = $this->actingAs($admin)->postJson('/api/subscribers/import', ['file' => $file]);

    $response->assertForbidden();
});

test('importing rejects a non-csv file', function () {
    $staff = User::factory()->create();
    $file = UploadedFile::fake()->create('subscribers.pdf', 10, 'application/pdf');

    $response = $this->actingAs($staff)->postJson('/api/subscribers/import', ['file' => $file]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('file');
});

test('importing requires a file', function () {
    $staff = User::factory()->create();

    $response = $this->actingAs($staff)->postJson('/api/subscribers/import', []);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('file');
});
