<?php

namespace Tests\Feature\Chat;

use App\Models\GroupChat;
use App\Models\RideOrder;
use App\Models\User;
use App\Services\RideOrderStateMachine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupChatCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_chat_auto_created_when_ride_transitions_to_accepted(): void
    {
        $stateMachine = app(RideOrderStateMachine::class);
        $rider = User::factory()->create(['role' => 'rider']);
        $driver = User::factory()->create(['role' => 'driver']);
        $ride = RideOrder::factory()->create(['status' => 'matching', 'rider_id' => $rider->id]);

        $stateMachine->transition($ride, 'accept', $driver);

        $chat = GroupChat::query()->where('ride_order_id', $ride->id)->first();

        $this->assertNotNull($chat);
    }

    public function test_both_rider_and_driver_are_participants(): void
    {
        $stateMachine = app(RideOrderStateMachine::class);
        $rider = User::factory()->create(['role' => 'rider']);
        $driver = User::factory()->create(['role' => 'driver']);
        $ride = RideOrder::factory()->create(['status' => 'matching', 'rider_id' => $rider->id]);

        $stateMachine->transition($ride, 'accept', $driver);

        $chat = GroupChat::query()->where('ride_order_id', $ride->id)->firstOrFail();

        $this->assertDatabaseHas('group_chat_participants', ['group_chat_id' => $chat->id, 'user_id' => $rider->id]);
        $this->assertDatabaseHas('group_chat_participants', ['group_chat_id' => $chat->id, 'user_id' => $driver->id]);
    }

    public function test_system_notice_generated_on_creation(): void
    {
        $stateMachine = app(RideOrderStateMachine::class);
        $rider = User::factory()->create(['role' => 'rider', 'username' => 'rider01']);
        $driver = User::factory()->create(['role' => 'driver', 'username' => 'driver01']);
        $ride = RideOrder::factory()->create(['status' => 'matching', 'rider_id' => $rider->id]);

        $stateMachine->transition($ride, 'accept', $driver);
        $chat = GroupChat::query()->where('ride_order_id', $ride->id)->firstOrFail();

        $this->assertDatabaseHas('group_messages', [
            'group_chat_id' => $chat->id,
            'type' => 'system_notice',
            'sender_id' => null,
        ]);
    }

    public function test_chat_disbanded_when_ride_completes(): void
    {
        $stateMachine = app(RideOrderStateMachine::class);
        $rider = User::factory()->create(['role' => 'rider']);
        $driver = User::factory()->create(['role' => 'driver']);
        $ride = RideOrder::factory()->create(['status' => 'matching', 'rider_id' => $rider->id]);

        $accepted = $stateMachine->transition($ride, 'accept', $driver);
        $inProgress = $stateMachine->transition($accepted, 'start', $driver);
        $stateMachine->transition($inProgress, 'complete', $driver);

        $chat = GroupChat::query()->where('ride_order_id', $ride->id)->firstOrFail();
        $this->assertSame('disbanded', $chat->status);
        $this->assertNotNull($chat->disbanded_at);
    }

    public function test_chat_disbanded_when_ride_canceled(): void
    {
        $stateMachine = app(RideOrderStateMachine::class);
        $rider = User::factory()->create(['role' => 'rider']);
        $driver = User::factory()->create(['role' => 'driver']);
        $ride = RideOrder::factory()->create(['status' => 'matching', 'rider_id' => $rider->id]);

        $accepted = $stateMachine->transition($ride, 'accept', $driver);
        $stateMachine->transition($accepted, 'cancel', $rider, ['reason' => 'rider_canceled']);

        $chat = GroupChat::query()->where('ride_order_id', $ride->id)->firstOrFail();
        $this->assertSame('disbanded', $chat->status);
    }

    public function test_no_duplicate_chats_for_same_ride_order(): void
    {
        $stateMachine = app(RideOrderStateMachine::class);
        $rider = User::factory()->create(['role' => 'rider']);
        $driver = User::factory()->create(['role' => 'driver']);
        $ride = RideOrder::factory()->create(['status' => 'matching', 'rider_id' => $rider->id]);

        $accepted = $stateMachine->transition($ride, 'accept', $driver);
        $stateMachine->transition($accepted, 'accept', $driver);

        $this->assertSame(1, GroupChat::query()->where('ride_order_id', $ride->id)->count());
    }
}
