<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OtpAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_customer_can_login_with_otp(): void
    {
        $this->postJson('/api/v1/auth/otp/request', ['mobile' => '09123456789'])->assertOk();
        $response = $this->postJson('/api/v1/auth/otp/verify', ['mobile' => '09123456789', 'code' => '12345']);
        $response->assertOk()->assertJsonPath('is_new_user', true);
        $this->assertDatabaseHas('users', ['mobile' => '09123456789']);
        $this->withToken($response->json('token'))->getJson('/api/v1/auth/me')->assertOk()->assertJsonPath('mobile', '09123456789');
    }

    public function test_invalid_otp_is_rejected(): void
    {
        $this->postJson('/api/v1/auth/otp/request', ['mobile' => '09123456780']);
        $this->postJson('/api/v1/auth/otp/verify', ['mobile' => '09123456780', 'code' => '99999'])->assertUnprocessable();
    }
}
