<?php

namespace Tests\Feature\Rides;

use App\Models\RideOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DriverOverlapTest extends TestCase
{
    use RefreshDatabase;

    public function test_driver_with_one_to_three_cannot_accept_two_to_four(): void
    {
        $driver = User::factory()->create(['role' => 'driver']);

        RideOrder::factory()->create([
            'status' => 'accepted',
            'driver_id' => $driver->id,
            'time_window_start' => now()->setTime(13, 0),
            'time_window_end' => now()->setTime(15, 0),
        ]);

        $target = RideOrder::factory()->create([
            'status' => 'matching',
            'time_window_start' => now()->setTime(14, 0),
            'time_window_end' => now()->setTime(16, 0),
        ]);

        Sanctum::actingAs($driver);

        $this->patchJson('/api/v1/ride-orders/'.$target->id.'/transition', ['action' => 'accept'])
            ->assertStatus(422)
            ->assertJsonPath('error', 'schedule_conflict');
    }

    public function test_driver_with_one_to_three_can_accept_four_to_six(): void
    {
        $driver = User::factory()->create(['role' => 'driver']);

        RideOrder::factory()->create([
            'status' => 'accepted',
            'driver_id' => $driver->id,
            'time_window_start' => now()->setTime(13, 0),
            'time_window_end' => now()->setTime(15, 0),
        ]);

        $target = RideOrder::factory()->create([
            'status' => 'matching',
            'time_window_start' => now()->setTime(16, 0),
            'time_window_end' => now()->setTime(18, 0),
        ]);

        Sanctum::actingAs($driver);

        $this->patchJson('/api/v1/ride-orders/'.$target->id.'/transition', ['action' => 'accept'])
            ->assertStatus(200)
            ->assertJsonPath('order.status', 'accepted');
    }

    public function test_completed_rides_do_not_count_as_overlap(): void
    {
        $driver = User::factory()->create(['role' => 'driver']);

        RideOrder::factory()->create([
            'status' => 'completed',
            'driver_id' => $driver->id,
            'time_window_start' => now()->setTime(13, 0),
            'time_window_end' => now()->setTime(15, 0),
        ]);

        $target = RideOrder::factory()->create([
            'status' => 'matching',
            'time_window_start' => now()->setTime(14, 0),
            'time_window_end' => now()->setTime(16, 0),
        ]);

        Sanctum::actingAs($driver);

        $this->patchJson('/api/v1/ride-orders/'.$target->id.'/transition', ['action' => 'accept'])
            ->assertStatus(200);
    }
}
