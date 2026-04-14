<?php

namespace App\Domain\OrderManagement\Services;

class PaymentSimulatorService
{
    public function isSuccessful(float $orderTotal): bool
    {
        return $orderTotal <= 999.00;
    }
}
