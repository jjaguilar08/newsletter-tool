<?php

declare(strict_types=1);

use App\Models\Campaign;
use App\Models\CampaignSend;
use App\Models\Subscriber;
use App\Models\User;

test('a campaign belongs to the user who created it', function () {
    $user = User::factory()->create();
    $campaign = Campaign::factory()->create(['created_by' => $user->id]);

    expect($campaign->creator)->toBeInstanceOf(User::class);
    expect($campaign->creator->id)->toBe($user->id);
    expect($user->campaigns->pluck('id'))->toContain($campaign->id);
});

test('a campaign has many campaign sends', function () {
    $campaign = Campaign::factory()->create();
    $sends = CampaignSend::factory()->count(3)->create(['campaign_id' => $campaign->id]);

    expect($campaign->campaignSends)->toHaveCount(3);
    expect($campaign->campaignSends->pluck('id')->sort()->values())
        ->toEqual($sends->pluck('id')->sort()->values());
});

test('a subscriber has many campaign sends', function () {
    $subscriber = Subscriber::factory()->create();
    CampaignSend::factory()->count(2)->create(['subscriber_id' => $subscriber->id]);

    expect($subscriber->campaignSends)->toHaveCount(2);
});

test('a campaign send belongs to a campaign and a subscriber', function () {
    $campaign = Campaign::factory()->create();
    $subscriber = Subscriber::factory()->create();
    $send = CampaignSend::factory()->create([
        'campaign_id' => $campaign->id,
        'subscriber_id' => $subscriber->id,
    ]);

    expect($send->campaign->id)->toBe($campaign->id);
    expect($send->subscriber->id)->toBe($subscriber->id);
});

test('deleting a campaign cascades to its campaign sends', function () {
    $campaign = Campaign::factory()->create();
    CampaignSend::factory()->count(3)->create(['campaign_id' => $campaign->id]);

    expect(CampaignSend::query()->where('campaign_id', $campaign->id)->count())->toBe(3);

    $campaign->delete();

    expect(CampaignSend::query()->where('campaign_id', $campaign->id)->count())->toBe(0);
});

test('deleting a subscriber cascades to its campaign sends', function () {
    $subscriber = Subscriber::factory()->create();
    CampaignSend::factory()->count(2)->create(['subscriber_id' => $subscriber->id]);

    expect(CampaignSend::query()->where('subscriber_id', $subscriber->id)->count())->toBe(2);

    $subscriber->delete();

    expect(CampaignSend::query()->where('subscriber_id', $subscriber->id)->count())->toBe(0);
});
