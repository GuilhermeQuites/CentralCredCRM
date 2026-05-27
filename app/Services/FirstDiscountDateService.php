<?php

namespace App\Services;

use Carbon\CarbonImmutable;

class FirstDiscountDateService
{
    private const TIMEZONE = 'America/Sao_Paulo';

    public function calculate(string $contractDate): CarbonImmutable
    {
        $date = CarbonImmutable::parse($contractDate, self::TIMEZONE)->startOfDay();
        $monthsToAdd = $date->day <= 15 ? 1 : 2;

        return $date
            ->addMonthsNoOverflow($monthsToAdd)
            ->day(5)
            ->startOfDay();
    }
}
