<?php

namespace App\Domain\OrderManagement\Services;

use App\Domain\Cart\Models\Cart;
use App\Domain\OrderManagement\Actions\CreateOrderAction;
use App\Domain\OrderManagement\DTOs\CreateOrderDTO;
use App\Domain\OrderManagement\DTOs\CreateOrderItemDTO;
use App\Domain\OrderManagement\Enums\PaymentMethod;
use App\Domain\OrderManagement\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function __construct(
        private readonly PaymentSimulatorService $paymentSimulatorService,
        private readonly CreateOrderAction $createOrderAction,
    ) {
    }

    public function checkout(User $user, PaymentMethod $paymentMethod): Order
    {
        $cart = Cart::query()
            ->where('user_id', $user->id)
            ->with(['items.product.vendor'])
            ->first();

        if ($cart === null || $cart->items->isEmpty()) {
            throw ValidationException::withMessages([
                'checkout' => __('Your cart is empty. Add products before checkout.'),
            ]);
        }

        $lineItems = [];
        $totalAmount = 0.0;

        foreach ($cart->items as $cartItem) {
            $product = $cartItem->product;

            if ($product === null || $product->vendor === null || ! $product->vendor->is_active || $product->status !== 'active') {
                throw ValidationException::withMessages([
                    'checkout' => __('One or more products are no longer available.'),
                ]);
            }

            if ($cartItem->quantity > $product->stock_quantity) {
                throw ValidationException::withMessages([
                    'checkout' => __('Insufficient stock for :product. Available: :stock.', [
                        'product' => $product->name,
                        'stock' => $product->stock_quantity,
                    ]),
                ]);
            }

            $lineTotal = round((float) $product->price * $cartItem->quantity, 2);
            $totalAmount += $lineTotal;

            $lineItems[] = new CreateOrderItemDTO(
                productId: $product->id,
                vendorId: $product->vendor->id,
                productName: $product->name,
                quantity: $cartItem->quantity,
                unitPrice: (float) $product->price,
                lineTotal: $lineTotal,
            );
        }

        $totalAmount = round($totalAmount, 2);

        if (! $this->paymentSimulatorService->isSuccessful($totalAmount)) {
            throw ValidationException::withMessages([
                'checkout' => __('Payment failed for orders over $999.00. Cart remains unchanged.'),
            ]);
        }

        return DB::transaction(function () use ($user, $paymentMethod, $lineItems, $totalAmount, $cart): Order {
            $order = $this->createOrderAction->execute(
                new CreateOrderDTO(
                    userId: $user->id,
                    paymentMethod: $paymentMethod,
                    totalAmount: $totalAmount,
                    items: $lineItems,
                ),
            );

            foreach ($cart->items as $cartItem) {
                $product = $cartItem->product()->lockForUpdate()->firstOrFail();

                if ($cartItem->quantity > $product->stock_quantity) {
                    throw ValidationException::withMessages([
                        'checkout' => __('Stock changed during checkout for :product.', [
                            'product' => $product->name,
                        ]),
                    ]);
                }

                $product->decrement('stock_quantity', $cartItem->quantity);
            }

            $cart->items()->delete();

            return $order;
        });
    }
}
