<?php

namespace Tests\Feature\Chat;

use App\Models\GroupChat;
use App\Models\RideOrder;
use App\Models\User;
use App\Services\RideOrderStateMachine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class GroupMessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_participant_can_send_message(): void
    {
        [$rider, $driver, $chat] = $this->seedAcceptedRideAndChat();

        Sanctum::actingAs($rider);

        $this->postJson('/api/v1/group-chats/'.$chat->id.'/messages', [
            'content' => 'On my way to pickup point.',
        ])->assertStatus(201);
    }

    public function test_non_participant_denied(): void
    {
        [, , $chat] = $this->seedAcceptedRideAndChat();
        $outsider = User::factory()->create(['role' => 'rider']);

        Sanctum::actingAs($outsider);

        $this->postJson('/api/v1/group-chats/'.$chat->id.'/messages', [
            'content' => 'I should not be here',
        ])->assertStatus(403);
    }

    public function test_message_to_disbanded_chat_denied(): void
    {
        [$rider, $driver, $chat, $ride] = $this->seedAcceptedRideAndChat();
        $stateMachine = app(RideOrderStateMachine::class);
        $inProgress = $stateMachine->transition($ride, 'start', $driver);
        $stateMachine->transition($inProgress, 'complete', $driver);

        Sanctum::actingAs($rider);

        $this->postJson('/api/v1/group-chats/'.$chat->id.'/messages', [
            'content' => 'Can you still see this?',
        ])->assertStatus(403)
            ->assertJsonPath('error', 'chat_disbanded');
    }

    public function test_content_over_2000_chars_returns_422(): void
    {
        [$rider, , $chat] = $this->seedAcceptedRideAndChat();
        Sanctum::actingAs($rider);

        $this->postJson('/api/v1/group-chats/'.$chat->id.'/messages', [
            'content' => str_repeat('a', 2001),
        ])->assertStatus(422);
    }

    public function test_empty_content_returns_422(): void
    {
        [$rider, , $chat] = $this->seedAcceptedRideAndChat();
        Sanctum::actingAs($rider);

        $this->postJson('/api/v1/group-chats/'.$chat->id.'/messages', [
            'content' => '',
        ])->assertStatus(422);
    }

    public function test_polling_returns_only_messages_after_given_id(): void
    {
        [$rider, , $chat] = $this->seedAcceptedRideAndChat();
        Sanctum::actingAs($rider);

        $first = $this->postJson('/api/v1/group-chats/'.$chat->id.'/messages', [
            'content' => 'First',
        ])->json('message.id');

        $this->postJson('/api/v1/group-chats/'.$chat->id.'/messages', [
            'content' => 'Second',
        ]);

        $response = $this->getJson('/api/v1/group-chats/'.$chat->id.'/messages?after_id='.$first);

        $response->assertStatus(200)
            ->assertJsonCount(1, 'messages')
            ->assertJsonPath('messages.0.content', 'Second');
    }

    /**
     * @return array{User, User, GroupChat, RideOrder}
     */
    private function seedAcceptedRideAndChat(): array
    {
        $stateMachine = app(RideOrderStateMachine::class);
        $rider = User::factory()->create(['role' => 'rider']);
        $driver = User::factory()->create(['role' => 'driver']);
        $ride = RideOrder::factory()->create(['status' => 'matching', 'rider_id' => $rider->id]);

        $stateMachine->transition($ride, 'accept', $driver);
        $chat = GroupChat::query()->where('ride_order_id', $ride->id)->firstOrFail();

        return [$rider, $driver, $chat, $ride->fresh()];
    }
}
