<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\NotificationSubscription>
 */
class NotificationSubscriptionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'entity_type' => fake()->randomElement(['ride_order', 'product']),
            'entity_id' => fake()->numberBetween(1, 1000),
            'created_at' => now(),
        ];
    }
}
