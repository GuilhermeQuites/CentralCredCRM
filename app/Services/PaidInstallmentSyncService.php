<?php

namespace App\Services;

use App\Models\Contract;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

class PaidInstallmentSyncService
{
    private const TIMEZONE = 'America/Sao_Paulo';

    public function sync(?CarbonInterface $today = null): int
    {
        $updated = 0;
        $today = $this->localDate($today);

        Contract::query()
            ->where('status', 'active')
            ->whereNotNull('first_discount_date')
            ->orderBy('id')
            ->each(function (Contract $contract) use ($today, &$updated): void {
                $paidInstallments = $this->calculatePaidInstallments(
                    $contract->first_discount_date,
                    $today,
                    $contract->total_installments,
                );

                if ($paidInstallments === $contract->paid_installments) {
                    return;
                }

                $contract->update(['paid_installments' => $paidInstallments]);
                $updated++;
            });

        return $updated;
    }

    public function calculatePaidInstallments(
        ?CarbonInterface $firstDiscountDate,
        ?CarbonInterface $today,
        int $totalInstallments,
    ): int {
        if (! $firstDiscountDate || $totalInstallments <= 0) {
            return 0;
        }

        $firstDiscountDate = $this->localDate($firstDiscountDate);
        $today = $this->localDate($today);

        if ($today->lt($firstDiscountDate)) {
            return 0;
        }

        $months = (($today->year - $firstDiscountDate->year) * 12)
            + ($today->month - $firstDiscountDate->month);

        if ($today->day < $firstDiscountDate->day) {
            $months--;
        }

        return min($totalInstallments, max(0, $months + 1));
    }

    private function localDate(?CarbonInterface $date): CarbonImmutable
    {
        return CarbonImmutable::parse($date ?? 'now', self::TIMEZONE)->startOfDay();
    }
}
