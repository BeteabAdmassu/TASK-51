<?php

namespace Tests\Feature\Chat;

use App\Models\GroupChat;
use App\Models\RideOrder;
use App\Models\User;
use App\Services\DndService;
use App\Services\RideOrderStateMachine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DndTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_update_dnd_settings_returns_200(): void
    {
        [$rider, $chat] = $this->seedChat();
        Sanctum::actingAs($rider);

        $this->patchJson('/api/v1/group-chats/'.$chat->id.'/dnd', [
            'dnd_start' => '09:00',
            'dnd_end' => '17:00',
        ])->assertStatus(200)
            ->assertJsonPath('participant.dnd_start', '09:00:00');
    }

    public function test_is_in_dnd_window_true_during_dnd_hours(): void
    {
        [$rider, $chat] = $this->seedChat();
        $participant = $chat->participants()->where('user_id', $rider->id)->firstOrFail();
        $participant->update(['dnd_start' => '09:00:00', 'dnd_end' => '17:00:00']);

        Carbon::setTestNow(Carbon::parse('2026-03-25 10:00:00'));

        $this->assertTrue(app(DndService::class)->isInDndWindow($rider, $chat));
    }

    public function test_is_in_dnd_window_false_outside_dnd_hours(): void
    {
        [$rider, $chat] = $this->seedChat();
        $participant = $chat->participants()->where('user_id', $rider->id)->firstOrFail();
        $participant->update(['dnd_start' => '09:00:00', 'dnd_end' => '17:00:00']);

        Carbon::setTestNow(Carbon::parse('2026-03-25 18:00:00'));

        $this->assertFalse(app(DndService::class)->isInDndWindow($rider, $chat));
    }

    public function test_cross_midnight_dnd_window_works(): void
    {
        [$rider, $chat] = $this->seedChat();
        $participant = $chat->participants()->where('user_id', $rider->id)->firstOrFail();
        $participant->update(['dnd_start' => '22:00:00', 'dnd_end' => '07:00:00']);

        Carbon::setTestNow(Carbon::parse('2026-03-25 23:00:00'));
        $this->assertTrue(app(DndService::class)->isInDndWindow($rider, $chat));

        Carbon::setTestNow(Carbon::parse('2026-03-25 12:00:00'));
        $this->assertFalse(app(DndService::class)->isInDndWindow($rider, $chat));
    }

    /**
     * @return array{User, GroupChat}
     */
    private function seedChat(): array
    {
        $stateMachine = app(RideOrderStateMachine::class);
        $rider = User::factory()->create(['role' => 'rider']);
        $driver = User::factory()->create(['role' => 'driver']);
        $ride = RideOrder::factory()->create(['status' => 'matching', 'rider_id' => $rider->id]);

        $stateMachine->transition($ride, 'accept', $driver);

        return [$rider, GroupChat::query()->where('ride_order_id', $ride->id)->firstOrFail()];
    }
}
