<?php

namespace App\Services;

class RefinancingService
{
    public function calculate(
        int $paidInstallments,
        int $minimumInstallmentsForRefinancing
    ): array {
        $remainingInstallments = max(0, $minimumInstallmentsForRefinancing - $paidInstallments);

        if ($paidInstallments >= $minimumInstallmentsForRefinancing) {
            return [
                'status' => 'eligible',
                'message' => 'Cliente elegivel para refinanciamento',
                'minimum_installments_for_refinancing' => $minimumInstallmentsForRefinancing,
                'remaining_installments' => 0,
            ];
        }

        return [
            'status' => 'waiting',
            'message' => "Faltam {$remainingInstallments} parcelas para refinanciamento",
            'minimum_installments_for_refinancing' => $minimumInstallmentsForRefinancing,
            'remaining_installments' => $remainingInstallments,
        ];
    }
}
