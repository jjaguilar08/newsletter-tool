<?php

declare(strict_types=1);

use App\Enums\CampaignSendStatus;
use App\Enums\UserRole;
use App\Models\Campaign;
use App\Models\CampaignSend;
use App\Models\Subscriber;
use App\Models\User;

test('a staff user can list a campaign\'s sends', function () {
    $staff = User::factory()->create();
    $campaign = Campaign::factory()->sent()->create();
    $otherCampaign = Campaign::factory()->sent()->create();

    $subscriber = Subscriber::factory()->create(['email' => 'recipient@example.com']);
    $send = CampaignSend::factory()->create([
        'campaign_id' => $campaign->id,
        'subscriber_id' => $subscriber->id,
        'status' => CampaignSendStatus::Sent,
        'sent_at' => now(),
    ]);
    $failedSend = CampaignSend::factory()->create([
        'campaign_id' => $campaign->id,
        'status' => CampaignSendStatus::Failed,
        'error_message' => 'Mailbox does not exist.',
    ]);

    // Belongs to a different campaign - must not show up in the response.
    CampaignSend::factory()->create(['campaign_id' => $otherCampaign->id]);

    $response = $this->actingAs($staff)->getJson("/api/campaigns/{$campaign->id}/sends");

    $response->assertOk();
    $response->assertJsonCount(2, 'data');

    $ids = collect($response->json('data'))->pluck('id');
    expect($ids)->toContain($send->id, $failedSend->id);

    $response->assertJsonFragment([
        'id' => $send->id,
        'subscriber_email' => 'recipient@example.com',
        'status' => 'sent',
    ]);
    $response->assertJsonFragment([
        'id' => $failedSend->id,
        'status' => 'failed',
        'error_message' => 'Mailbox does not exist.',
    ]);
});

test('a campaign\'s sends are paginated', function () {
    $staff = User::factory()->create();
    $campaign = Campaign::factory()->sent()->create();
    CampaignSend::factory()->count(20)->create(['campaign_id' => $campaign->id]);

    $response = $this->actingAs($staff)->getJson("/api/campaigns/{$campaign->id}/sends");

    $response->assertOk();
    $response->assertJsonCount(15, 'data');
    $response->assertJsonPath('meta.total', 20);
    $response->assertJsonPath('meta.per_page', 15);

    $secondPage = $this->actingAs($staff)->getJson("/api/campaigns/{$campaign->id}/sends?page=2");
    $secondPage->assertJsonCount(5, 'data');
});

test('a guest cannot list a campaign\'s sends', function () {
    $campaign = Campaign::factory()->create();

    $response = $this->getJson("/api/campaigns/{$campaign->id}/sends");

    $response->assertUnauthorized();
});

test('a non-staff user cannot list a campaign\'s sends', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $campaign = Campaign::factory()->create();

    $response = $this->actingAs($admin)->getJson("/api/campaigns/{$campaign->id}/sends");

    $response->assertForbidden();
});

test('listing sends for a missing campaign returns 404', function () {
    $staff = User::factory()->create();

    $response = $this->actingAs($staff)->getJson('/api/campaigns/999999/sends');

    $response->assertNotFound();
});
