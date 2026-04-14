<?php

namespace App\Domain\OrderManagement\DTOs;

use App\Domain\OrderManagement\Enums\PaymentMethod;

readonly class CreateOrderDTO
{
    /**
     * @param  array<int, CreateOrderItemDTO>  $items
     */
    public function __construct(
        public string $userId,
        public PaymentMethod $paymentMethod,
        public float $totalAmount,
        public array $items,
    ) {
    }
}
