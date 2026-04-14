<?php

namespace App\Domain\ProductCatalog\DTOs;

readonly class UpdateProductDTO
{
    public function __construct(
        public string $name,
        public string $description,
        public float $price,
        public int $stockQuantity,
        public string $imageUrl,
        public string $status,
    ) {
    }
}
