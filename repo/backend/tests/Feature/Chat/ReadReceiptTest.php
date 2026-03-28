<?php

namespace Tests\Feature\Chat;

use App\Models\GroupChat;
use App\Models\RideOrder;
use App\Models\User;
use App\Services\RideOrderStateMachine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReadReceiptTest extends TestCase
{
    use RefreshDatabase;

    public function test_mark_messages_as_read_creates_receipts(): void
    {
        [$rider, $chat] = $this->seedChatAndMessage();
        Sanctum::actingAs($rider);

        $messageId = $chat->messages()->latest('id')->value('id');

        $response = $this->postJson('/api/v1/group-chats/'.$chat->id.'/read', [
            'up_to_message_id' => $messageId,
        ])->assertStatus(200);

        $this->assertGreaterThanOrEqual(1, $response->json('newly_marked'));

        $this->assertDatabaseHas('message_read_receipts', [
            'message_id' => $messageId,
            'user_id' => $rider->id,
        ]);
    }

    public function test_duplicate_mark_is_idempotent(): void
    {
        [$rider, $chat] = $this->seedChatAndMessage();
        Sanctum::actingAs($rider);

        $messageId = $chat->messages()->latest('id')->value('id');

        $this->postJson('/api/v1/group-chats/'.$chat->id.'/read', [
            'up_to_message_id' => $messageId,
        ])->assertStatus(200);

        $this->postJson('/api/v1/group-chats/'.$chat->id.'/read', [
            'up_to_message_id' => $messageId,
        ])->assertStatus(200)
            ->assertJsonPath('newly_marked', 0);
    }

    public function test_non_participant_cannot_mark_messages_read(): void
    {
        [, $chat] = $this->seedChatAndMessage();
        $outsider = User::factory()->create(['role' => 'rider']);
        Sanctum::actingAs($outsider);

        $messageId = $chat->messages()->latest('id')->value('id');

        $this->postJson('/api/v1/group-chats/'.$chat->id.'/read', [
            'up_to_message_id' => $messageId,
        ])->assertStatus(403);
    }

    /**
     * @return array{User, GroupChat}
     */
    private function seedChatAndMessage(): array
    {
        $stateMachine = app(RideOrderStateMachine::class);
        $rider = User::factory()->create(['role' => 'rider']);
        $driver = User::factory()->create(['role' => 'driver']);
        $ride = RideOrder::factory()->create(['status' => 'matching', 'rider_id' => $rider->id]);

        $stateMachine->transition($ride, 'accept', $driver);
        $chat = GroupChat::query()->where('ride_order_id', $ride->id)->firstOrFail();

        $chat->messages()->create([
            'sender_id' => $driver->id,
            'content' => 'Hello rider',
            'type' => 'user_message',
            'created_at' => now(),
        ]);

        return [$rider, $chat];
    }
}
