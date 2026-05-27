<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_login_returns_bearer_token_and_allows_authenticated_request(): void
    {
        User::factory()->create([
            'email' => 'api@centralcred.com',
            'password' => 'password',
            'role' => 'admin',
        ]);

        $token = $this->postJson('/api/login', [
            'email' => 'api@centralcred.com',
            'password' => 'password',
        ])
            ->assertOk()
            ->assertJsonPath('token_type', 'Bearer')
            ->json('access_token');

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('user.email', 'api@centralcred.com');
    }

    public function test_api_rejects_request_without_token(): void
    {
        $this->getJson('/api/clients')
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Token nao informado.');
    }

    public function test_api_can_create_client_with_registration(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $token = $this->tokenFor($user);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/clients', [
                'user_id' => $user->id,
                'name' => 'Cliente API',
                'cpf' => '12345678909',
                'phone' => '11987654329',
                'registrations' => ['API123'],
            ])
            ->assertCreated()
            ->assertJsonPath('name', 'Cliente API');

        $this->assertDatabaseHas('clients', [
            'cpf' => '123.456.789-09',
        ]);
        $this->assertDatabaseHas('client_registrations', [
            'number' => 'API123',
        ]);
    }

    private function tokenFor(User $user): string
    {
        return $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->json('access_token');
    }
}
