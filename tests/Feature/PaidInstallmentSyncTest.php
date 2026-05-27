<?php

namespace Tests\Feature;

use App\Models\Agreement;
use App\Models\Bank;
use App\Models\Client;
use App\Models\ClientRegistration;
use App\Models\Contract;
use App\Models\User;
use App\Services\PaidInstallmentSyncService;
use App\Services\RefinancingNotificationService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaidInstallmentSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_paid_installments_are_calculated_from_first_discount_date(): void
    {
        $service = app(PaidInstallmentSyncService::class);
        $firstDiscountDate = CarbonImmutable::parse('2026-02-05', 'America/Sao_Paulo');

        $this->assertSame(0, $service->calculatePaidInstallments(
            $firstDiscountDate,
            CarbonImmutable::parse('2026-02-04', 'America/Sao_Paulo'),
            84,
        ));

        $this->assertSame(1, $service->calculatePaidInstallments(
            $firstDiscountDate,
            CarbonImmutable::parse('2026-02-05', 'America/Sao_Paulo'),
            84,
        ));

        $this->assertSame(1, $service->calculatePaidInstallments(
            $firstDiscountDate,
            CarbonImmutable::parse('2026-03-04', 'America/Sao_Paulo'),
            84,
        ));

        $this->assertSame(2, $service->calculatePaidInstallments(
            $firstDiscountDate,
            CarbonImmutable::parse('2026-03-05', 'America/Sao_Paulo'),
            84,
        ));
    }

    public function test_daily_sync_updates_paid_installments_and_makes_notification_available(): void
    {
        $contract = $this->contract([
            'first_discount_date' => '2026-02-05',
            'paid_installments' => 5,
            'minimum_installments_for_refinancing' => 6,
        ]);

        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-07-05', 'America/Sao_Paulo'));

        $updated = app(PaidInstallmentSyncService::class)->sync();

        $this->assertSame(1, $updated);
        $this->assertSame(6, $contract->fresh()->paid_installments);

        $notification = collect(app(RefinancingNotificationService::class)->activePayload())
            ->firstWhere('contract_id', $contract->id);

        $this->assertNotNull($notification);

        CarbonImmutable::setTestNow();
    }

    public function test_42_installment_contract_notifies_when_all_installments_are_paid(): void
    {
        $contract = $this->contract([
            'first_discount_date' => '2026-02-05',
            'total_installments' => 42,
            'paid_installments' => 41,
            'minimum_installments_for_refinancing' => 42,
        ]);

        CarbonImmutable::setTestNow(CarbonImmutable::parse('2029-07-04', 'America/Sao_Paulo'));

        app(PaidInstallmentSyncService::class)->sync();

        $this->assertSame(41, $contract->fresh()->paid_installments);
        $this->assertNull(
            collect(app(RefinancingNotificationService::class)->activePayload())
                ->firstWhere('contract_id', $contract->id),
        );

        CarbonImmutable::setTestNow(CarbonImmutable::parse('2029-07-05', 'America/Sao_Paulo'));

        app(PaidInstallmentSyncService::class)->sync();

        $this->assertSame(42, $contract->fresh()->paid_installments);
        $this->assertNotNull(
            collect(app(RefinancingNotificationService::class)->activePayload())
                ->firstWhere('contract_id', $contract->id),
        );

        CarbonImmutable::setTestNow();
    }

    public function test_sync_does_not_update_contract_before_next_discount_day(): void
    {
        $contract = $this->contract([
            'first_discount_date' => '2026-02-05',
            'paid_installments' => 5,
            'minimum_installments_for_refinancing' => 6,
        ]);

        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-07-04', 'America/Sao_Paulo'));

        $updated = app(PaidInstallmentSyncService::class)->sync();

        $this->assertSame(0, $updated);
        $this->assertSame(5, $contract->fresh()->paid_installments);

        CarbonImmutable::setTestNow();
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function contract(array $overrides = []): Contract
    {
        $user = User::factory()->create(['role' => 'admin']);
        $client = Client::create([
            'user_id' => $user->id,
            'name' => 'Cliente Sync',
            'cpf' => '444.444.444-44',
            'phone' => '(11) 94444-4444',
        ]);
        $registration = ClientRegistration::create([
            'client_id' => $client->id,
            'number' => 'SYNC001',
        ]);
        $bank = Bank::create(['name' => 'Banco Sync']);
        $agreement = Agreement::create(['name' => 'Convenio Sync']);

        return Contract::create(array_merge([
            'client_id' => $client->id,
            'client_registration_id' => $registration->id,
            'bank_id' => $bank->id,
            'agreement_id' => $agreement->id,
            'bank' => $bank->name,
            'contract_type' => 'new',
            'contract_value' => 10000,
            'installment_value' => 300,
            'total_installments' => 84,
            'paid_installments' => 0,
            'minimum_installments_for_refinancing' => 6,
            'contract_date' => '2026-01-10',
            'first_discount_date' => '2026-02-05',
            'status' => 'active',
        ], $overrides));
    }
}
