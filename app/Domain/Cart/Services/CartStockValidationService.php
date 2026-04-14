<?php

namespace App\Domain\Cart\Services;

use App\Domain\ProductCatalog\Models\Product;
use Illuminate\Validation\ValidationException;

class CartStockValidationService
{
    /**
     * Ensure requested quantity does not exceed current stock.
     */
    public function validateRequestedQuantity(Product $product, int $requestedQuantity): void
    {
        if ($requestedQuantity < 1) {
            throw ValidationException::withMessages([
                'quantity' => __('Quantity must be at least 1.'),
            ]);
        }

        if ($requestedQuantity > $product->stock_quantity) {
            throw ValidationException::withMessages([
                'quantity' => __('Only :stock item(s) are currently available.', [
                    'stock' => $product->stock_quantity,
                ]),
            ]);
        }
    }
}
