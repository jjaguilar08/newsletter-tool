<?php

declare(strict_types=1);

use App\Enums\CampaignSendStatus;
use App\Enums\CampaignStatus;
use App\Enums\SubscriberStatus;
use App\Models\Campaign;
use App\Models\CampaignSend;
use App\Models\Subscriber;

test('the unsubscribed subscriber factory state sets status and timestamp', function () {
    $subscriber = Subscriber::factory()->unsubscribed()->create();

    expect($subscriber->status)->toBe(SubscriberStatus::Unsubscribed);
    expect($subscriber->unsubscribed_at)->not->toBeNull();
});

test('the sent campaign factory state sets status and timestamp', function () {
    $campaign = Campaign::factory()->sent()->create();

    expect($campaign->status)->toBe(CampaignStatus::Sent);
    expect($campaign->sent_at)->not->toBeNull();
});

test('the failed campaign send factory state sets status and error message', function () {
    $send = CampaignSend::factory()->failed()->create();

    expect($send->status)->toBe(CampaignSendStatus::Failed);
    expect($send->error_message)->not->toBeNull();
});
