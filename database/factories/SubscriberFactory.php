<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\SubscriberStatus;
use App\Models\Subscriber;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subscriber>
 */
class SubscriberFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'name' => fake()->name(),
            'status' => SubscriberStatus::Subscribed,
            'unsubscribe_token' => Subscriber::generateUnsubscribeToken(),
            'subscribed_at' => now(),
            'unsubscribed_at' => null,
        ];
    }

    public function unsubscribed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SubscriberStatus::Unsubscribed,
            'unsubscribed_at' => now(),
        ]);
    }

    public function bounced(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SubscriberStatus::Bounced,
        ]);
    }
}
