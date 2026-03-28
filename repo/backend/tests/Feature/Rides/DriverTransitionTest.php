<?php

namespace Tests\Feature\Rides;

use App\Models\RideOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DriverTransitionTest extends TestCase
{
    use RefreshDatabase;

    public function test_driver_accepts_matching_ride(): void
    {
        $driver = User::factory()->create(['role' => 'driver']);
        $ride = RideOrder::factory()->create(['status' => 'matching']);

        Sanctum::actingAs($driver);

        $this->patchJson('/api/v1/ride-orders/'.$ride->id.'/transition', ['action' => 'accept'])
            ->assertStatus(200)
            ->assertJsonPath('order.status', 'accepted');

        $ride->refresh();

        $this->assertSame($driver->id, $ride->driver_id);
        $this->assertNotNull($ride->accepted_at);
    }

    public function test_driver_starts_an_accepted_ride(): void
    {
        $driver = User::factory()->create(['role' => 'driver']);
        $ride = RideOrder::factory()->create([
            'status' => 'accepted',
            'driver_id' => $driver->id,
            'accepted_at' => now()->subMinute(),
        ]);

        Sanctum::actingAs($driver);

        $this->patchJson('/api/v1/ride-orders/'.$ride->id.'/transition', ['action' => 'start'])
            ->assertStatus(200)
            ->assertJsonPath('order.status', 'in_progress');

        $this->assertNotNull($ride->fresh()->started_at);
    }

    public function test_driver_completes_an_in_progress_ride(): void
    {
        $driver = User::factory()->create(['role' => 'driver']);
        $ride = RideOrder::factory()->create([
            'status' => 'in_progress',
            'driver_id' => $driver->id,
            'accepted_at' => now()->subMinutes(5),
            'started_at' => now()->subMinutes(4),
        ]);

        Sanctum::actingAs($driver);

        $this->patchJson('/api/v1/ride-orders/'.$ride->id.'/transition', ['action' => 'complete'])
            ->assertStatus(200)
            ->assertJsonPath('order.status', 'completed');

        $this->assertNotNull($ride->fresh()->completed_at);
    }

    public function test_driver_flags_exception_with_reason_and_audit_metadata(): void
    {
        $driver = User::factory()->create(['role' => 'driver']);
        $ride = RideOrder::factory()->create([
            'status' => 'accepted',
            'driver_id' => $driver->id,
            'accepted_at' => now()->subMinute(),
        ]);

        Sanctum::actingAs($driver);

        $response = $this->patchJson('/api/v1/ride-orders/'.$ride->id.'/transition', [
            'action' => 'flag_exception',
            'reason' => 'Vehicle issue',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('order.status', 'exception');

        $exceptionLog = $ride->fresh()->auditLogs()->where('to_status', 'exception')->latest('id')->first();

        $this->assertNotNull($exceptionLog);
        $this->assertSame('Vehicle issue', $exceptionLog->metadata['exception_reason']);
    }

    public function test_after_exception_ride_auto_reassigns_to_matching(): void
    {
        $driver = User::factory()->create(['role' => 'driver']);
        $ride = RideOrder::factory()->create([
            'status' => 'in_progress',
            'driver_id' => $driver->id,
            'accepted_at' => now()->subMinutes(5),
            'started_at' => now()->subMinutes(4),
        ]);

        Sanctum::actingAs($driver);

        $this->patchJson('/api/v1/ride-orders/'.$ride->id.'/transition', [
            'action' => 'flag_exception',
            'reason' => 'Flat tire',
        ])->assertStatus(200);

        $ride->refresh();

        $this->assertSame('matching', $ride->status);
        $this->assertNull($ride->driver_id);
    }

    public function test_driver_cannot_accept_overlapping_active_ride(): void
    {
        $driver = User::factory()->create(['role' => 'driver']);
        RideOrder::factory()->create([
            'status' => 'accepted',
            'driver_id' => $driver->id,
            'time_window_start' => now()->addHour(),
            'time_window_end' => now()->addHours(3),
        ]);

        $target = RideOrder::factory()->create([
            'status' => 'matching',
            'time_window_start' => now()->addHours(2),
            'time_window_end' => now()->addHours(4),
        ]);

        Sanctum::actingAs($driver);

        $this->patchJson('/api/v1/ride-orders/'.$target->id.'/transition', ['action' => 'accept'])
            ->assertStatus(422)
            ->assertJsonPath('error', 'schedule_conflict');
    }

    public function test_driver_a_cannot_start_or_complete_driver_b_ride(): void
    {
        $driverA = User::factory()->create(['role' => 'driver']);
        $driverB = User::factory()->create(['role' => 'driver']);

        $accepted = RideOrder::factory()->create([
            'status' => 'accepted',
            'driver_id' => $driverB->id,
            'accepted_at' => now()->subMinute(),
        ]);

        $inProgress = RideOrder::factory()->create([
            'status' => 'in_progress',
            'driver_id' => $driverB->id,
            'accepted_at' => now()->subMinutes(5),
            'started_at' => now()->subMinutes(4),
        ]);

        Sanctum::actingAs($driverA);

        $this->patchJson('/api/v1/ride-orders/'.$accepted->id.'/transition', ['action' => 'start'])
            ->assertStatus(403);

        $this->patchJson('/api/v1/ride-orders/'.$inProgress->id.'/transition', ['action' => 'complete'])
            ->assertStatus(403);
    }

    public function test_driver_cannot_accept_already_accepted_ride(): void
    {
        $driver = User::factory()->create(['role' => 'driver']);
        $otherDriver = User::factory()->create(['role' => 'driver']);
        $ride = RideOrder::factory()->create([
            'status' => 'accepted',
            'driver_id' => $otherDriver->id,
            'accepted_at' => now()->subMinute(),
        ]);

        Sanctum::actingAs($driver);

        $this->patchJson('/api/v1/ride-orders/'.$ride->id.'/transition', ['action' => 'accept'])
            ->assertStatus(422);
    }

    public function test_rider_cannot_use_driver_transition_actions(): void
    {
        $rider = User::factory()->create(['role' => 'rider']);
        $ride = RideOrder::factory()->create(['status' => 'matching']);

        Sanctum::actingAs($rider);

        $this->patchJson('/api/v1/ride-orders/'.$ride->id.'/transition', ['action' => 'accept'])
            ->assertStatus(403);
    }
}
