<?php

namespace Tests\Unit\OrderManagement;

use App\Domain\OrderManagement\Services\PaymentSimulatorService;
use Tests\TestCase;

class PaymentSimulatorServiceTest extends TestCase
{
    public function test_it_succeeds_for_totals_at_or_below_threshold(): void
    {
        $service = new PaymentSimulatorService();

        $this->assertTrue($service->isSuccessful(999.00));
        $this->assertTrue($service->isSuccessful(50.25));
    }

    public function test_it_fails_for_totals_above_threshold(): void
    {
        $service = new PaymentSimulatorService();

        $this->assertFalse($service->isSuccessful(999.01));
        $this->assertFalse($service->isSuccessful(1500.00));
    }
}
