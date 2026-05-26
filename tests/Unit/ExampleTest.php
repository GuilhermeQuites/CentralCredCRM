<?php

namespace Tests\Unit;

use App\Services\RefinancingService;
use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    public function test_refinancing_service_marks_eligible_contracts(): void
    {
        $result = (new RefinancingService())->calculate(28, 28);

        $this->assertSame('eligible', $result['status']);
        $this->assertSame(0, $result['remaining_installments']);
    }

    public function test_refinancing_service_calculates_remaining_installments(): void
    {
        $result = (new RefinancingService())->calculate(24, 28);

        $this->assertSame('waiting', $result['status']);
        $this->assertSame(4, $result['remaining_installments']);
    }
}
