<?php

namespace App\Domain\Cart\Actions;

use App\Domain\Cart\Models\CartItem;
use App\Models\User;

class RemoveFromCartAction
{
    /**
     * Remove a cart item that belongs to the buyer.
     */
    public function execute(User $user, CartItem $cartItem): void
    {
        abort_unless($cartItem->cart->user_id === $user->id, 403);

        $cartItem->delete();
    }
}
