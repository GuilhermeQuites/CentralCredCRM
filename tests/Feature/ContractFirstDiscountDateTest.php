<?php

namespace Tests\Feature;

use App\Models\Agreement;
use App\Models\Bank;
use App\Models\Client;
use App\Models\ClientRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContractFirstDiscountDateTest extends TestCase
{
    use RefreshDatabase;

    public function test_contract_with_zero_paid_installments_stores_calculated_first_discount_date(): void
    {
        $data = $this->contractData([
            'contract_date' => '2026-05-10',
            'paid_installments' => 0,
            'first_discount_date' => '',
        ]);

        $this->actingAs($data['user'])
            ->post('/contracts', $data['payload'])
            ->assertRedirect('/contracts');

        $this->assertDatabaseHas('contracts', [
            'contract_date' => '2026-05-10 00:00:00',
            'paid_installments' => 0,
            'first_discount_date' => '2026-06-05 00:00:00',
        ]);
    }

    public function test_contract_with_zero_paid_installments_rejects_inconsistent_first_discount_date(): void
    {
        $data = $this->contractData([
            'contract_date' => '2026-05-26',
            'paid_installments' => 0,
            'first_discount_date' => '2026-06-05',
        ]);

        $this->actingAs($data['user'])
            ->from('/contracts/create')
            ->post('/contracts', $data['payload'])
            ->assertRedirect('/contracts/create')
            ->assertSessionHasErrors('first_discount_date');
    }

    public function test_contract_accepts_brazilian_currency_mask_and_stores_decimal_values(): void
    {
        $data = $this->contractData([
            'contract_value' => 'R$ 10.000,00',
            'installment_value' => 'R$ 350,75',
        ]);

        $this->actingAs($data['user'])
            ->post('/contracts', $data['payload'])
            ->assertRedirect('/contracts');

        $this->assertDatabaseHas('contracts', [
            'contract_value' => 10000,
            'installment_value' => 350.75,
        ]);
    }

    public function test_contract_rejects_registration_from_another_client(): void
    {
        $data = $this->contractData();
        $otherClient = Client::create([
            'user_id' => $data['user']->id,
            'name' => 'Outro Cliente',
            'cpf' => '000.000.000-01',
            'phone' => '(11) 90000-0001',
        ]);
        $otherRegistration = ClientRegistration::create([
            'client_id' => $otherClient->id,
            'number' => '99999',
        ]);

        $payload = array_merge($data['payload'], [
            'client_registration_id' => $otherRegistration->id,
        ]);

        $this->actingAs($data['user'])
            ->from('/contracts/create')
            ->post('/contracts', $payload)
            ->assertRedirect('/contracts/create')
            ->assertSessionHasErrors('client_registration_id');
    }

    /**
     * @param array<string, mixed> $overrides
     *
     * @return array{user: User, payload: array<string, mixed>}
     */
    private function contractData(array $overrides = []): array
    {
        $user = User::factory()->create(['role' => 'admin']);
        $client = Client::create([
            'user_id' => $user->id,
            'name' => 'Cliente Teste',
            'cpf' => '000.000.000-00',
            'phone' => '(11) 90000-0000',
        ]);
        $registration = ClientRegistration::create([
            'client_id' => $client->id,
            'number' => '12345',
        ]);
        $bank = Bank::create(['name' => 'Banco Teste']);
        $agreement = Agreement::create(['name' => 'Convenio Teste']);

        return [
            'user' => $user,
            'payload' => array_merge([
                'client_id' => $client->id,
                'client_registration_id' => $registration->id,
                'bank_id' => $bank->id,
                'agreement_id' => $agreement->id,
                'contract_type' => 'new',
                'contract_value' => 10000,
                'installment_value' => 250,
                'total_installments' => 84,
                'paid_installments' => 0,
                'minimum_installments_for_refinancing' => 28,
                'contract_date' => '2026-05-10',
                'first_discount_date' => '',
            ], $overrides),
        ];
    }
}
