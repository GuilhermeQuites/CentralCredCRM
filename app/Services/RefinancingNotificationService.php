<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\RefinancingNotification;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class RefinancingNotificationService
{
    private const TIMEZONE = 'America/Sao_Paulo';

    /**
     * @return Collection<int, Contract>
     */
    public function activeContracts(): Collection
    {
        $now = CarbonImmutable::now(self::TIMEZONE);

        return Contract::query()
            ->with(['client', 'clientRegistration', 'bankRecord', 'refinancingNotification'])
            ->where('status', 'active')
            ->whereColumn('paid_installments', '>=', 'minimum_installments_for_refinancing')
            ->orderByDesc('paid_installments')
            ->get()
            ->filter(fn (Contract $contract) => $this->isNotificationActive($contract, $now))
            ->values();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function activePayload(): array
    {
        return $this->activeContracts()
            ->map(function (Contract $contract): array {
                $notification = $this->ensurePendingNotification($contract);

                return [
                    'id' => $notification->id,
                    'contract_id' => $contract->id,
                    'client_name' => $contract->client?->name ?? '-',
                    'registration' => $contract->clientRegistration?->number ?? '-',
                    'bank' => $contract->bankName(),
                    'paid_installments' => $contract->paid_installments,
                    'first_discount_date' => $contract->first_discount_date?->format('d/m/Y') ?? '-',
                    'installment_value' => 'R$ ' . number_format((float) $contract->installment_value, 2, ',', '.'),
                    'available_date' => CarbonImmutable::now(self::TIMEZONE)->format('d/m/Y'),
                    'show_url' => route('contracts.show', $contract),
                    'viewed_url' => route('refinancing-notifications.viewed', $contract),
                    'not_refinanced_url' => route('refinancing-notifications.not-refinanced', $contract),
                    'api_show_url' => url("/api/contracts/{$contract->id}"),
                    'api_viewed_url' => url("/api/contracts/{$contract->id}/refinancing-notification/viewed"),
                    'api_not_refinanced_url' => url("/api/contracts/{$contract->id}/refinancing-notification/not-refinanced"),
                ];
            })
            ->values()
            ->all();
    }

    public function markViewed(Contract $contract): RefinancingNotification
    {
        return RefinancingNotification::query()->updateOrCreate(
            ['contract_id' => $contract->id],
            [
                'status' => RefinancingNotification::STATUS_VIEWED,
                'viewed_at' => CarbonImmutable::now(self::TIMEZONE),
                'notify_after_paid_installments' => null,
                'marked_not_refinanced_at' => null,
            ],
        );
    }

    public function markNotRefinanced(Contract $contract, int $notifyAfterPaidInstallments): RefinancingNotification
    {
        return RefinancingNotification::query()->updateOrCreate(
            ['contract_id' => $contract->id],
            [
                'status' => RefinancingNotification::STATUS_NOT_REFINANCED,
                'viewed_at' => null,
                'notify_after_paid_installments' => $notifyAfterPaidInstallments,
                'marked_not_refinanced_at' => CarbonImmutable::now(self::TIMEZONE),
            ],
        );
    }

    private function isNotificationActive(Contract $contract, CarbonImmutable $now): bool
    {
        $notification = $contract->refinancingNotification;

        if (! $notification) {
            return true;
        }

        if ($notification->status === RefinancingNotification::STATUS_VIEWED) {
            return ! $notification->viewed_at || $notification->viewed_at->timezone(self::TIMEZONE)->addDay()->lte($now);
        }

        if ($notification->status === RefinancingNotification::STATUS_NOT_REFINANCED) {
            return $notification->notify_after_paid_installments !== null
                && $contract->paid_installments >= $notification->notify_after_paid_installments;
        }

        return true;
    }

    private function ensurePendingNotification(Contract $contract): RefinancingNotification
    {
        return RefinancingNotification::query()->updateOrCreate(
            ['contract_id' => $contract->id],
            ['status' => RefinancingNotification::STATUS_PENDING],
        );
    }
}
