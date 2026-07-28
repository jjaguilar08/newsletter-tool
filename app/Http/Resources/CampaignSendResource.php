<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\CampaignSend;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin CampaignSend
 */
class CampaignSendResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'subscriber_email' => $this->subscriber->email,
            'status' => $this->status->value,
            'sent_at' => $this->sent_at?->toIso8601String(),
            'error_message' => $this->error_message,
        ];
    }
}
