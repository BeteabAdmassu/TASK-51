<?php

namespace Tests\Feature\Rides;

use App\Models\RideOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DriverAvailableRidesTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_only_matching_rides_within_time_window(): void
    {
        $driver = User::factory()->create(['role' => 'driver']);
        Sanctum::actingAs($driver);

        RideOrder::factory()->create([
            'status' => 'matching',
            'time_window_start' => now()->addMinutes(30),
            'time_window_end' => now()->addMinutes(90),
        ]);

        $response = $this->getJson('/api/v1/driver/available-rides');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_does_not_return_rides_outside_time_window(): void
    {
        $driver = User::factory()->create(['role' => 'driver']);
        Sanctum::actingAs($driver);

        RideOrder::factory()->create([
            'status' => 'matching',
            'time_window_start' => now()->addHours(3),
            'time_window_end' => now()->addHours(4),
        ]);

        $this->getJson('/api/v1/driver/available-rides')
            ->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }

    public function test_does_not_return_non_matching_rides(): void
    {
        $driver = User::factory()->create(['role' => 'driver']);
        Sanctum::actingAs($driver);

        RideOrder::factory()->create([
            'status' => 'accepted',
            'time_window_start' => now()->addMinutes(30),
            'time_window_end' => now()->addMinutes(60),
        ]);

        $this->getJson('/api/v1/driver/available-rides')
            ->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }

    public function test_pagination_works_correctly(): void
    {
        $driver = User::factory()->create(['role' => 'driver']);
        Sanctum::actingAs($driver);

        RideOrder::factory()->count(16)->create([
            'status' => 'matching',
            'time_window_start' => now()->addMinutes(30),
            'time_window_end' => now()->addMinutes(90),
        ]);

        $this->getJson('/api/v1/driver/available-rides?per_page=15')
            ->assertStatus(200)
            ->assertJsonCount(15, 'data')
            ->assertJsonPath('per_page', 15)
            ->assertJsonPath('last_page', 2);
    }

    public function test_rider_cannot_access_available_rides_endpoint(): void
    {
        $rider = User::factory()->create(['role' => 'rider']);
        Sanctum::actingAs($rider);

        $this->getJson('/api/v1/driver/available-rides')->assertStatus(403);
    }

    public function test_empty_response_when_no_rides_available(): void
    {
        $driver = User::factory()->create(['role' => 'driver']);
        Sanctum::actingAs($driver);

        $this->getJson('/api/v1/driver/available-rides')
            ->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }
}
