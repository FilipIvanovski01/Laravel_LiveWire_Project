<?php

namespace App\Domain\Cart\Actions;

use App\Domain\Cart\Models\Cart;
use App\Domain\Cart\Models\CartItem;
use App\Domain\Cart\Services\CartStockValidationService;
use App\Domain\ProductCatalog\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AddToCartAction
{
    public function __construct(
        private readonly CartStockValidationService $stockValidationService,
    ) {
    }

    /**
     * Add a product to the buyer cart.
     */
    public function execute(User $user, Product $product, int $quantity = 1): CartItem
    {
        return DB::transaction(function () use ($user, $product, $quantity): CartItem {
            $cart = Cart::query()->firstOrCreate([
                'user_id' => $user->id,
            ]);

            $existingItem = CartItem::query()
                ->where('cart_id', $cart->id)
                ->where('product_id', $product->id)
                ->first();

            $requestedQuantity = $existingItem === null
                ? $quantity
                : $existingItem->quantity + $quantity;

            $this->stockValidationService->validateRequestedQuantity($product, $requestedQuantity);

            if ($existingItem !== null) {
                $existingItem->update([
                    'quantity' => $requestedQuantity,
                ]);

                return $existingItem->fresh();
            }

            return CartItem::query()->create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'quantity' => $requestedQuantity,
                'unit_price' => $product->price,
            ]);
        });
    }
}
