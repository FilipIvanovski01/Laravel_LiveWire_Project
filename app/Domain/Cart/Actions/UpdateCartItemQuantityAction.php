<?php

namespace App\Domain\Cart\Actions;

use App\Domain\Cart\Models\CartItem;
use App\Domain\Cart\Services\CartStockValidationService;
use App\Models\User;

class UpdateCartItemQuantityAction
{
    public function __construct(
        private readonly CartStockValidationService $stockValidationService,
    ) {
    }

    /**
     * Update quantity for a cart item belonging to the buyer.
     */
    public function execute(User $user, CartItem $cartItem, int $quantity): CartItem
    {
        abort_unless($cartItem->cart->user_id === $user->id, 403);

        $product = $cartItem->product()->firstOrFail();
        $this->stockValidationService->validateRequestedQuantity($product, $quantity);

        $cartItem->update([
            'quantity' => $quantity,
            'unit_price' => $product->price,
        ]);

        return $cartItem->fresh();
    }
}
