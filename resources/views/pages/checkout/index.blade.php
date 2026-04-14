<?php

use App\Domain\Cart\Models\Cart;
use App\Domain\Cart\Models\CartItem;
use App\Domain\OrderManagement\Enums\PaymentMethod;
use App\Domain\OrderManagement\Services\CheckoutService;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Checkout')] class extends Component {
    public string $payment_method = 'credit_card';

    public function placeOrder(CheckoutService $checkoutService): void
    {
        $validated = $this->validate([
            'payment_method' => ['required', 'in:credit_card,wallet'],
        ]);

        try {
            $order = $checkoutService->checkout(
                Auth::user(),
                PaymentMethod::from($validated['payment_method']),
            );

            Flux::toast(variant: 'success', text: __('Order placed successfully.'));
            $this->redirect(route('buyer.orders.show', $order), navigate: true);
        } catch (ValidationException $exception) {
            $this->addError('checkout', $exception->getMessage());
        }
    }

    #[Computed]
    public function cart(): ?Cart
    {
        return Cart::query()
            ->where('user_id', Auth::id())
            ->with(['items.product.vendor'])
            ->first();
    }

    #[Computed]
    public function groupedItems(): array
    {
        if ($this->cart === null) {
            return [];
        }

        return $this->cart->items
            ->filter(fn (CartItem $item): bool => $item->product !== null && $item->product->vendor !== null)
            ->groupBy(fn (CartItem $item): string => $item->product->vendor->id)
            ->map(function ($vendorItems): array {
                $first = $vendorItems->first();
                $subtotal = $vendorItems->sum(fn (CartItem $item): float => (float) $item->unit_price * $item->quantity);

                return [
                    'vendor_name' => $first->product->vendor->store_name,
                    'items' => $vendorItems,
                    'subtotal' => $subtotal,
                ];
            })
            ->values()
            ->all();
    }

    #[Computed]
    public function totalAmount(): float
    {
        return collect($this->groupedItems)->sum('subtotal');
    }
}; ?>

<section class="space-y-6">
    <div class="space-y-2">
        <flux:heading size="xl">{{ __('Checkout') }}</flux:heading>
        <flux:text>{{ __('Review your order and complete payment.') }}</flux:text>
    </div>

    @if (empty($this->groupedItems))
        <div class="rounded-xl border border-dashed border-neutral-300 p-8 text-center dark:border-neutral-700">
            <flux:text>{{ __('Your cart is empty. Add products before checkout.') }}</flux:text>
            <div class="mt-4">
                <flux:button :href="route('home')" wire:navigate>{{ __('Browse Products') }}</flux:button>
            </div>
        </div>
    @else
        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-4 lg:col-span-2">
                @foreach ($this->groupedItems as $group)
                    <article class="rounded-xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-700 dark:bg-zinc-900">
                        <div class="mb-3 flex items-center justify-between">
                            <flux:heading size="sm">{{ $group['vendor_name'] }}</flux:heading>
                            <flux:text class="font-semibold">
                                {{ __('Subtotal: $:amount', ['amount' => number_format($group['subtotal'], 2)]) }}
                            </flux:text>
                        </div>
                        <div class="space-y-2">
                            @foreach ($group['items'] as $item)
                                <div class="flex items-center justify-between text-sm">
                                    <flux:text>{{ $item->product->name }} (x{{ $item->quantity }})</flux:text>
                                    <flux:text>${{ number_format((float) $item->unit_price * $item->quantity, 2) }}</flux:text>
                                </div>
                            @endforeach
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="rounded-xl border border-neutral-200 bg-white p-5 shadow-sm dark:border-neutral-700 dark:bg-zinc-900">
                <flux:heading size="md">{{ __('Payment') }}</flux:heading>
                <form wire:submit="placeOrder" class="mt-4 space-y-4">
                    <flux:select wire:model="payment_method" :label="__('Payment Method')">
                        <option value="credit_card">{{ __('Credit Card') }}</option>
                        <option value="wallet">{{ __('Wallet') }}</option>
                    </flux:select>

                    <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-800">
                        <flux:text>{{ __('Total Amount') }}</flux:text>
                        <flux:heading size="lg" class="text-indigo-600 dark:text-indigo-400">${{ number_format($this->totalAmount, 2) }}</flux:heading>
                    </div>

                    @error('checkout')
                        <flux:text class="text-sm text-red-600">{{ $message }}</flux:text>
                    @enderror

                    <flux:button variant="primary" type="submit" class="w-full" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="placeOrder">{{ __('Place Order') }}</span>
                        <span wire:loading wire:target="placeOrder">{{ __('Processing...') }}</span>
                    </flux:button>
                </form>
            </div>
        </div>
    @endif
</section>
