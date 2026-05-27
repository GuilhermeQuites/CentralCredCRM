<?php

namespace Tests\Unit;

use App\Services\FirstDiscountDateService;
use PHPUnit\Framework\TestCase;

class FirstDiscountDateServiceTest extends TestCase
{
    public function test_contract_until_payroll_closing_uses_next_month(): void
    {
        $result = (new FirstDiscountDateService())
            ->calculate('2026-05-10')
            ->toDateString();

        $this->assertSame('2026-06-05', $result);
    }

    public function test_contract_after_payroll_closing_uses_subsequent_month(): void
    {
        $result = (new FirstDiscountDateService())
            ->calculate('2026-05-26')
            ->toDateString();

        $this->assertSame('2026-07-05', $result);
    }
}
