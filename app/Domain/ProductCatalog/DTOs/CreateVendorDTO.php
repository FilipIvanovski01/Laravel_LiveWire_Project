<?php

namespace App\Domain\ProductCatalog\DTOs;

readonly class CreateVendorDTO
{
    public function __construct(
        public string $storeName,
        public ?string $description = null,
    ) {
    }
}
