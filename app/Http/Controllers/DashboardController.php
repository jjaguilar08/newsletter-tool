<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\CampaignSendStatus;
use App\Enums\CampaignStatus;
use App\Enums\SubscriberStatus;
use App\Models\Campaign;
use App\Models\CampaignSend;
use App\Models\Subscriber;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function stats(): JsonResponse
    {
        return response()->json([
            'subscribers' => collect(SubscriberStatus::cases())->mapWithKeys(
                fn (SubscriberStatus $status) => [$status->value => Subscriber::where('status', $status)->count()],
            ),
            'campaigns' => collect(CampaignStatus::cases())->mapWithKeys(
                fn (CampaignStatus $status) => [$status->value => Campaign::where('status', $status)->count()],
            ),
            'recent_campaigns' => Campaign::query()
                ->latest()
                ->latest('id')
                ->take(5)
                ->get(['subject', 'status', 'sent_at'])
                ->map(fn (Campaign $campaign) => [
                    'subject' => $campaign->subject,
                    'status' => $campaign->status->value,
                    'sent_at' => $campaign->sent_at?->toIso8601String(),
                ]),
            'campaign_sends' => [
                'sent' => CampaignSend::where('status', CampaignSendStatus::Sent)->count(),
                'failed' => CampaignSend::where('status', CampaignSendStatus::Failed)->count(),
            ],
        ]);
    }
}
