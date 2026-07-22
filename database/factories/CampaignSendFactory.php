<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CampaignSendStatus;
use App\Models\Campaign;
use App\Models\CampaignSend;
use App\Models\Subscriber;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CampaignSend>
 */
class CampaignSendFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'campaign_id' => Campaign::factory(),
            'subscriber_id' => Subscriber::factory(),
            'status' => CampaignSendStatus::Pending,
            'sent_at' => null,
            'error_message' => null,
        ];
    }

    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CampaignSendStatus::Sent,
            'sent_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CampaignSendStatus::Failed,
            'error_message' => fake()->sentence(),
        ]);
    }

    public function bounced(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CampaignSendStatus::Bounced,
            'sent_at' => now(),
        ]);
    }
}
