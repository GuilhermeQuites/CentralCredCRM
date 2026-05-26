<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_users_area(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'permissions' => [],
        ]);

        $this->actingAs($admin)
            ->get('/users')
            ->assertOk()
            ->assertSee('Usuarios');
    }

    public function test_seller_without_permission_cannot_open_users_area(): void
    {
        $seller = User::factory()->create([
            'role' => 'seller',
            'permissions' => [],
        ]);

        $this->actingAs($seller)
            ->get('/users')
            ->assertForbidden();
    }

    public function test_seller_with_bank_permission_can_open_banks_area(): void
    {
        $seller = User::factory()->create([
            'role' => 'seller',
            'permissions' => ['visualizar_bancos'],
        ]);

        $this->actingAs($seller)
            ->get('/banks')
            ->assertOk()
            ->assertSee('Bancos');
    }
}
