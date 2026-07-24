<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\CampaignStatus;
use App\Jobs\SendCampaignJob;
use App\Models\Campaign;
use Illuminate\Console\Command;

class DispatchScheduledCampaigns extends Command
{
    protected $signature = 'campaigns:dispatch-scheduled';

    protected $description = 'Claim and dispatch every scheduled campaign whose scheduled_at has arrived';

    public function handle(): void
    {
        $due = Campaign::where('status', CampaignStatus::Scheduled)
            ->where('scheduled_at', '<=', now())
            ->get();

        foreach ($due as $campaign) {
            // Claiming (Scheduled -> Sending) atomically means two overlapping
            // runs of this command can't both dispatch the same campaign.
            if (Campaign::claimForSending($campaign->id, CampaignStatus::Scheduled)) {
                SendCampaignJob::dispatch($campaign->fresh());
            }
        }
    }
}
