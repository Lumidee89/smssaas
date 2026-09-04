<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_readiness_endpoint_checks_database_and_cache(): void
    {
        $this->getJson('/health/ready')
            ->assertOk()
            ->assertJsonPath('status', 'ready')
            ->assertJsonPath('checks.database', 'ok')
            ->assertJsonPath('checks.cache', 'ok');
    }

    public function test_responses_include_security_and_request_headers(): void
    {
        $response = $this->withHeader('X-Request-ID', 'schoolos-test-request')->get('/up');

        $response->assertOk()
            ->assertHeader('X-Request-ID', 'schoolos-test-request')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_invalid_request_id_is_replaced(): void
    {
        $response = $this->withHeader('X-Request-ID', "bad\nvalue")->get('/up');

        $this->assertNotSame("bad\nvalue", $response->headers->get('X-Request-ID'));
        $this->assertMatchesRegularExpression('/^[0-9a-f-]{36}$/', (string) $response->headers->get('X-Request-ID'));
    }
}
