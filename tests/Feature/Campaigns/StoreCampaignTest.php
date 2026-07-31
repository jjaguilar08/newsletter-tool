<?php

declare(strict_types=1);

use App\Enums\CampaignStatus;
use App\Enums\UserRole;
use App\Models\Campaign;
use App\Models\User;

test('a staff user can create a campaign', function () {
    $staff = User::factory()->create();

    $response = $this->actingAs($staff)->postJson('/api/campaigns', [
        'subject' => 'New Campaign',
        'content' => 'Some content.',
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.subject', 'New Campaign');
    $response->assertJsonPath('data.status', CampaignStatus::Draft->value);
    $response->assertJsonPath('data.created_by', $staff->id);

    $campaign = Campaign::where('subject', 'New Campaign')->firstOrFail();
    expect($campaign->status)->toBe(CampaignStatus::Draft);
    expect($campaign->created_by)->toBe($staff->id);
});

test('creating a campaign requires subject and content', function () {
    $staff = User::factory()->create();

    $response = $this->actingAs($staff)->postJson('/api/campaigns', []);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['subject', 'content']);
});

test('creating a campaign always sets status to draft regardless of client input', function () {
    $staff = User::factory()->create();

    $response = $this->actingAs($staff)->postJson('/api/campaigns', [
        'subject' => 'New Campaign',
        'content' => 'Some content.',
        'status' => CampaignStatus::Sent->value,
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.status', CampaignStatus::Draft->value);

    $campaign = Campaign::where('subject', 'New Campaign')->firstOrFail();
    expect($campaign->status)->toBe(CampaignStatus::Draft);
});

test('creating a campaign ignores a client-supplied created_by', function () {
    $staff = User::factory()->create();
    $otherUser = User::factory()->create();

    $response = $this->actingAs($staff)->postJson('/api/campaigns', [
        'subject' => 'New Campaign',
        'content' => 'Some content.',
        'created_by' => $otherUser->id,
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.created_by', $staff->id);
});

test('a staff user can create a campaign with a design attached', function () {
    $staff = User::factory()->create();

    $response = $this->actingAs($staff)->postJson('/api/campaigns', [
        'subject' => 'New Campaign',
        'content' => 'Some content.',
        'body_html' => '<table><tr><td>Hello</td></tr></table>',
        'design_json' => ['pages' => [], 'styles' => [], 'assets' => [], 'symbols' => []],
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.body_html', '<table><tr><td>Hello</td></tr></table>');
    $response->assertJsonPath('data.design_json.pages', []);

    $campaign = Campaign::where('subject', 'New Campaign')->firstOrFail();
    expect($campaign->body_html)->toBe('<table><tr><td>Hello</td></tr></table>');
    expect($campaign->design_json)->toBe(['pages' => [], 'styles' => [], 'assets' => [], 'symbols' => []]);
});

test('creating a campaign without a design leaves body_html and design_json null', function () {
    $staff = User::factory()->create();

    $response = $this->actingAs($staff)->postJson('/api/campaigns', [
        'subject' => 'New Campaign',
        'content' => 'Some content.',
    ]);

    $response->assertCreated();
    $response->assertJsonPath('data.body_html', null);
    $response->assertJsonPath('data.design_json', null);

    $campaign = Campaign::where('subject', 'New Campaign')->firstOrFail();
    expect($campaign->body_html)->toBeNull();
    expect($campaign->design_json)->toBeNull();
});

test('a guest cannot create a campaign', function () {
    $response = $this->postJson('/api/campaigns', [
        'subject' => 'New Campaign',
        'content' => 'Some content.',
    ]);

    $response->assertUnauthorized();
});

test('a non-staff user cannot create a campaign', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $response = $this->actingAs($admin)->postJson('/api/campaigns', [
        'subject' => 'New Campaign',
        'content' => 'Some content.',
    ]);

    $response->assertForbidden();
});
