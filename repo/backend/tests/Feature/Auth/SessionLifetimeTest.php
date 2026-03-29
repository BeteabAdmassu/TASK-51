<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;

class SessionLifetimeTest extends TestCase
{
    public function test_session_lifetime_is_configured_for_twelve_hours(): void
    {
        $this->assertSame(720, (int) config('session.lifetime'));
    }

    public function test_csrf_endpoint_sets_twelve_hour_session_cookie_ttl(): void
    {
        $response = $this->get('/sanctum/csrf-cookie');

        $response->assertNoContent();

        $setCookieHeader = implode("\n", $response->headers->all('set-cookie'));

        $this->assertStringContainsString('laravel_session=', $setCookieHeader);
        $this->assertStringContainsString('Max-Age=43200', $setCookieHeader);
    }
}
