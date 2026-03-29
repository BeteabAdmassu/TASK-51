<?php

namespace Tests\Feature\Health;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_readiness_reports_ready_when_notification_type_column_exists(): void
    {
        Schema::partialMock()
            ->shouldReceive('hasColumn')
            ->with('notification_frequency_logs', 'type')
            ->andReturn(true);

        $this->getJson('/api/v1/readiness')
            ->assertStatus(200)
            ->assertJsonPath('checks.notification_frequency_type_column', true)
            ->assertJsonPath('status', 'ready');
    }

    public function test_readiness_reports_degraded_when_notification_type_column_is_missing(): void
    {
        Schema::partialMock()
            ->shouldReceive('hasColumn')
            ->with('notification_frequency_logs', 'type')
            ->andReturn(false);

        $this->getJson('/api/v1/readiness')
            ->assertStatus(503)
            ->assertJsonPath('checks.notification_frequency_type_column', false)
            ->assertJsonPath('status', 'degraded');
    }
}
