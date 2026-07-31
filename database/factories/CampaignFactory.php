<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CampaignStatus;
use App\Models\Campaign;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Campaign>
 */
class CampaignFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'subject' => fake()->sentence(),
            'content' => fake()->paragraphs(3, true),
            'body_html' => null,
            'design_json' => null,
            'status' => CampaignStatus::Draft,
            'scheduled_at' => null,
            'sent_at' => null,
            'created_by' => User::factory(),
        ];
    }

    public function withDesign(): static
    {
        return $this->state(fn (array $attributes) => [
            'body_html' => '<table><tr><td>'.fake()->paragraph().'</td></tr></table>',
            'design_json' => ['pages' => [], 'styles' => [], 'assets' => [], 'symbols' => []],
        ]);
    }

    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CampaignStatus::Scheduled,
            'scheduled_at' => now()->addDay(),
        ]);
    }

    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CampaignStatus::Sent,
            'scheduled_at' => now()->subDay(),
            'sent_at' => now(),
        ]);
    }
}
