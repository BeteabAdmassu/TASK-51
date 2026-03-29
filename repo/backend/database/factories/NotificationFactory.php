<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notification>
 */
class NotificationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => fake()->randomElement(['reply', 'mention', 'follower', 'ride_update', 'order_update']),
            'priority' => fake()->randomElement(['normal', 'high']),
            'title' => fake()->sentence(4),
            'body' => fake()->sentence(),
            'data' => null,
            'group_key' => null,
            'is_read' => false,
            'read_at' => null,
            'created_at' => now(),
        ];
    }
}
