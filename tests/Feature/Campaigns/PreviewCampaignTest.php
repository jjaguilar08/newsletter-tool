<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\Campaign;
use App\Models\Subscriber;
use App\Models\User;

test('a staff user can preview a campaign', function () {
    $staff = User::factory()->create();
    $campaign = Campaign::factory()->create([
        'subject' => 'Hello Preview',
        'content' => 'This is the campaign body.',
    ]);

    $response = $this->actingAs($staff)->getJson("/api/campaigns/{$campaign->id}/preview");

    $response->assertOk();
    $response->assertJsonPath('subject', 'Hello Preview');
    expect($response->json('html'))->toContain('This is the campaign body.');
    expect($response->json('html'))->toContain('/unsubscribe/');
});

test('previewing a campaign with a saved design renders body_html instead of the content template', function () {
    $staff = User::factory()->create();
    $campaign = Campaign::factory()->withDesign()->create([
        'content' => 'This plain content should not appear.',
        'body_html' => '<table><tr><td>Designed body</td></tr></table>',
    ]);

    $response = $this->actingAs($staff)->getJson("/api/campaigns/{$campaign->id}/preview");

    $response->assertOk();
    expect($response->json('html'))->toContain('Designed body');
    expect($response->json('html'))->not->toContain('This plain content should not appear.');
    expect($response->json('html'))->toContain('/unsubscribe/');
});

test('the preview uses a placeholder unsubscribe link, not a real subscriber token', function () {
    $staff = User::factory()->create();
    $campaign = Campaign::factory()->create();
    $subscriber = Subscriber::factory()->create();

    $response = $this->actingAs($staff)->getJson("/api/campaigns/{$campaign->id}/preview");

    $response->assertOk();
    expect($response->json('html'))->not->toContain($subscriber->unsubscribe_token);
});

test('a guest cannot preview a campaign', function () {
    $campaign = Campaign::factory()->create();

    $response = $this->getJson("/api/campaigns/{$campaign->id}/preview");

    $response->assertUnauthorized();
});

test('a non-staff user cannot preview a campaign', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $campaign = Campaign::factory()->create();

    $response = $this->actingAs($admin)->getJson("/api/campaigns/{$campaign->id}/preview");

    $response->assertForbidden();
});

test('previewing a missing campaign returns 404', function () {
    $staff = User::factory()->create();

    $response = $this->actingAs($staff)->getJson('/api/campaigns/999999/preview');

    $response->assertNotFound();
});
