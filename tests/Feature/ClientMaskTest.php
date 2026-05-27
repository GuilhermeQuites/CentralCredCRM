<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientMaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_store_formats_cpf_and_phone_before_saving(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)
            ->post('/clients', [
                'user_id' => $user->id,
                'name' => 'Cliente Mascara',
                'cpf' => '12345678901',
                'phone' => '11987654321',
                'email' => 'cliente@email.com',
                'birth_date' => null,
                'notes' => null,
                'registrations' => ['12345'],
            ])
            ->assertRedirect('/clients');

        $this->assertDatabaseHas('clients', [
            'cpf' => '123.456.789-01',
            'phone' => '(11) 98765-4321',
            'email' => 'cliente@email.com',
        ]);

        $this->assertDatabaseHas('client_registrations', [
            'number' => '12345',
        ]);
    }

    public function test_client_rejects_duplicate_registrations(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)
            ->from('/clients/create')
            ->post('/clients', [
                'user_id' => $user->id,
                'name' => 'Cliente Duplicado',
                'cpf' => '12345678902',
                'phone' => '11987654322',
                'registration_count' => '2',
                'registrations' => ['ABC123', 'ABC123'],
            ])
            ->assertRedirect('/clients/create')
            ->assertSessionHasErrors('registrations.0');
    }

    public function test_client_rejects_invalid_email(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)
            ->from('/clients/create')
            ->post('/clients', [
                'user_id' => $user->id,
                'name' => 'Cliente Email Invalido',
                'cpf' => '12345678904',
                'phone' => '11987654324',
                'email' => 'email-invalido',
                'registrations' => ['MAIL123'],
            ])
            ->assertRedirect('/clients/create')
            ->assertSessionHasErrors('email');
    }

    public function test_client_duplicate_cpf_shows_readable_message(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)->post('/clients', [
            'user_id' => $user->id,
            'name' => 'Cliente Original',
            'cpf' => '12345678905',
            'phone' => '11987654325',
            'registrations' => ['CPF001'],
        ]);

        $this->actingAs($user)
            ->from('/clients/create')
            ->post('/clients', [
                'user_id' => $user->id,
                'name' => 'Cliente Repetido',
                'cpf' => '12345678905',
                'phone' => '11987654326',
                'registrations' => ['CPF002'],
            ])
            ->assertRedirect('/clients/create')
            ->assertSessionHasErrors([
                'cpf' => 'CPF ja possui cadastro.',
            ]);
    }

    public function test_create_client_form_selects_authenticated_user_by_default(): void
    {
        $user = User::factory()->create([
            'name' => 'Usuario Logado',
            'role' => 'seller',
        ]);

        $this->actingAs($user)
            ->get('/clients/create')
            ->assertOk()
            ->assertSee('value="' . $user->id . '" selected', false);
    }

    public function test_client_accepts_explicit_one_registration_option(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)
            ->post('/clients', [
                'user_id' => $user->id,
                'name' => 'Cliente Uma Matricula',
                'cpf' => '12345678903',
                'phone' => '11987654323',
                'registration_count' => '1',
                'registrations' => ['UNI123'],
            ])
            ->assertRedirect('/clients');

        $this->assertDatabaseHas('client_registrations', [
            'number' => 'UNI123',
        ]);
    }
}
