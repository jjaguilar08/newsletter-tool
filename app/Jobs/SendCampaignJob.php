<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\CampaignSendStatus;
use App\Enums\CampaignStatus;
use App\Enums\SubscriberStatus;
use App\Mail\CampaignMail;
use App\Models\Campaign;
use App\Models\CampaignSend;
use App\Models\Subscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Campaign $campaign) {}

    public function handle(): void
    {
        $subscribers = Subscriber::where('status', SubscriberStatus::Subscribed)->get();

        $sends = $subscribers->mapWithKeys(fn (Subscriber $subscriber) => [
            $subscriber->id => CampaignSend::create([
                'campaign_id' => $this->campaign->id,
                'subscriber_id' => $subscriber->id,
                'status' => CampaignSendStatus::Pending,
            ]),
        ]);

        foreach ($subscribers as $subscriber) {
            $send = $sends[$subscriber->id];

            try {
                $this->sendMailTo($subscriber);

                $send->update([
                    'status' => CampaignSendStatus::Sent,
                    'sent_at' => now(),
                ]);
            } catch (Throwable $e) {
                $send->update([
                    'status' => CampaignSendStatus::Failed,
                    'error_message' => $e->getMessage(),
                ]);
            }
        }

        $this->campaign->update([
            'status' => CampaignStatus::Sent,
            'sent_at' => now(),
        ]);
    }

    // Isolated to its own method so tests can override just the send call
    // (e.g. to simulate a per-subscriber failure) without faking the whole job.
    protected function sendMailTo(Subscriber $subscriber): void
    {
        Mail::to($subscriber->email)->send(new CampaignMail($this->campaign, $subscriber));
    }
}
