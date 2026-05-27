<?php

namespace Tests\Feature;

use App\Models\Agreement;
use App\Models\Bank;
use App\Models\Client;
use App\Models\ClientRegistration;
use App\Models\Contract;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RefinancingNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_lists_eligible_refinancing_contracts(): void
    {
        $data = $this->eligibleContract();

        $this->actingAs($data['user'])
            ->getJson('/refinancing-notifications')
            ->assertOk()
            ->assertJsonPath('count', 1)
            ->assertJsonPath('show_login_alert', true)
            ->assertJsonPath('notifications.0.client_name', 'Cliente Refin');

        $this->actingAs($data['user'])
            ->getJson('/refinancing-notifications')
            ->assertOk()
            ->assertJsonPath('count', 1)
            ->assertJsonPath('show_login_alert', false);
    }

    public function test_viewed_notification_is_hidden_for_24_hours(): void
    {
        $data = $this->eligibleContract();

        $this->actingAs($data['user'])
            ->postJson("/refinancing-notifications/{$data['contract']->id}/viewed")
            ->assertOk()
            ->assertJsonPath('count', 0);

        $this->actingAs($data['user'])
            ->getJson('/refinancing-notifications')
            ->assertOk()
            ->assertJsonPath('count', 0);

        CarbonImmutable::setTestNow(now('America/Sao_Paulo')->addDay()->addMinute());

        $this->actingAs($data['user'])
            ->getJson('/refinancing-notifications')
            ->assertOk()
            ->assertJsonPath('count', 1);

        CarbonImmutable::setTestNow();
    }

    public function test_not_refinanced_notification_returns_after_selected_installments(): void
    {
        $data = $this->eligibleContract(['paid_installments' => 12, 'minimum_installments_for_refinancing' => 6]);

        $this->actingAs($data['user'])
            ->postJson("/refinancing-notifications/{$data['contract']->id}/not-refinanced", [
                'notify_after_paid_installments' => 15,
            ])
            ->assertOk()
            ->assertJsonPath('count', 0);

        $data['contract']->update(['paid_installments' => 14]);

        $this->actingAs($data['user'])
            ->getJson('/refinancing-notifications')
            ->assertOk()
            ->assertJsonPath('count', 0);

        $data['contract']->update(['paid_installments' => 15]);

        $this->actingAs($data['user'])
            ->getJson('/refinancing-notifications')
            ->assertOk()
            ->assertJsonPath('count', 1);
    }

    public function test_contract_page_can_mark_notification_as_viewed(): void
    {
        $data = $this->eligibleContract();

        $this->actingAs($data['user'])
            ->post("/contracts/{$data['contract']->id}/refinancing-notification/viewed")
            ->assertRedirect("/contracts/{$data['contract']->id}");

        $this->assertDatabaseHas('refinancing_notifications', [
            'contract_id' => $data['contract']->id,
            'status' => 'visualizado',
        ]);
    }

    public function test_contract_page_can_mark_notification_as_not_refinanced_with_target_installment(): void
    {
        $data = $this->eligibleContract(['paid_installments' => 12]);

        $this->actingAs($data['user'])
            ->post("/contracts/{$data['contract']->id}/refinancing-notification/not-refinanced", [
                'notify_after_paid_installments' => 15,
            ])
            ->assertRedirect("/contracts/{$data['contract']->id}");

        $this->assertDatabaseHas('refinancing_notifications', [
            'contract_id' => $data['contract']->id,
            'status' => 'nao_refinanciado',
            'notify_after_paid_installments' => 15,
        ]);
    }

    /**
     * @param array<string, mixed> $contractOverrides
     *
     * @return array{user: User, contract: Contract}
     */
    private function eligibleContract(array $contractOverrides = []): array
    {
        $user = User::factory()->create(['role' => 'admin']);
        $client = Client::create([
            'user_id' => $user->id,
            'name' => 'Cliente Refin',
            'cpf' => '111.111.111-11',
            'phone' => '(11) 91111-1111',
        ]);
        $registration = ClientRegistration::create([
            'client_id' => $client->id,
            'number' => 'REF123',
        ]);
        $bank = Bank::create(['name' => 'Banco Refin']);
        $agreement = Agreement::create(['name' => 'INSS']);

        $contract = Contract::create(array_merge([
            'client_id' => $client->id,
            'client_registration_id' => $registration->id,
            'bank_id' => $bank->id,
            'agreement_id' => $agreement->id,
            'bank' => $bank->name,
            'contract_type' => 'new',
            'contract_value' => 10000,
            'installment_value' => 300,
            'total_installments' => 84,
            'paid_installments' => 6,
            'minimum_installments_for_refinancing' => 6,
            'contract_date' => '2026-01-10',
            'status' => 'active',
        ], $contractOverrides));

        return ['user' => $user, 'contract' => $contract];
    }
}
