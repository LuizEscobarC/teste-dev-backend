<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SanctumTokensTest extends TestCase
{
    use RefreshDatabase;

    public function test_token_is_created_when_user_registers(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => UserRole::CANDIDATE,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_token_is_created_when_user_logs_in(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }
}