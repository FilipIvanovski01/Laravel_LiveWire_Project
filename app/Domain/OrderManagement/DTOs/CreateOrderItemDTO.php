<?php

namespace App\Domain\OrderManagement\DTOs;

readonly class CreateOrderItemDTO
{
    public function __construct(
        public string $productId,
        public string $vendorId,
        public string $productName,
        public int $quantity,
        public float $unitPrice,
        public float $lineTotal,
    ) {
    }
}
