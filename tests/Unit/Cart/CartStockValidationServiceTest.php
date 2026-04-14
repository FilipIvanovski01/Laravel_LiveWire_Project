<?php

namespace Tests\Unit\Cart;

use App\Domain\Cart\Services\CartStockValidationService;
use App\Domain\ProductCatalog\Models\Product;
use App\Domain\ProductCatalog\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CartStockValidationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_allows_quantity_within_stock_limit(): void
    {
        $service = new CartStockValidationService();
        $product = Product::factory()->for(Vendor::factory()->create())->create(['stock_quantity' => 3]);

        $service->validateRequestedQuantity($product, 3);

        $this->assertTrue(true);
    }

    public function test_it_rejects_quantity_above_stock_limit(): void
    {
        $service = new CartStockValidationService();
        $product = Product::factory()->for(Vendor::factory()->create())->create(['stock_quantity' => 1]);

        $this->expectException(ValidationException::class);
        $service->validateRequestedQuantity($product, 2);
    }

    public function test_it_rejects_zero_or_negative_quantities(): void
    {
        $service = new CartStockValidationService();
        $product = Product::factory()->for(Vendor::factory()->create())->create(['stock_quantity' => 5]);

        $this->expectException(ValidationException::class);
        $service->validateRequestedQuantity($product, 0);
    }
}
