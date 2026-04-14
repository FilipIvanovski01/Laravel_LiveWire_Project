<?php

use App\Domain\Cart\Actions\AddToCartAction;
use App\Domain\Cart\Actions\RemoveFromCartAction;
use App\Domain\Cart\Actions\UpdateCartItemQuantityAction;
use App\Domain\Cart\Models\Cart;
use App\Domain\Cart\Models\CartItem;
use App\Domain\ProductCatalog\Models\Product;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Cart')] class extends Component {
    /** @var array<string, int|string> */
    public array $quantities = [];

    public function mount(): void
    {
        $this->syncQuantities();
    }

    public function addProduct(string $productId, AddToCartAction $addToCartAction): void
    {
        $product = Product::query()
            ->marketplaceVisible()
            ->findOrFail($productId);

        try {
            $addToCartAction->execute(Auth::user(), $product, 1);
            $this->syncQuantities();
            Flux::toast(variant: 'success', text: __('Product added to cart.'));
        } catch (ValidationException $exception) {
            Flux::toast(variant: 'danger', text: $exception->getMessage());
        }
    }

    public function updateItemQuantity(string $cartItemId, UpdateCartItemQuantityAction $action): void
    {
        $quantity = isset($this->quantities[$cartItemId]) ? (int) $this->quantities[$cartItemId] : 1;
        $cartItem = CartItem::query()->with(['cart', 'product'])->findOrFail($cartItemId);

        try {
            $action->execute(Auth::user(), $cartItem, $quantity);
            $this->syncQuantities();
            Flux::toast(variant: 'success', text: __('Cart updated.'));
        } catch (ValidationException $exception) {
            $this->addError("quantities.{$cartItemId}", $exception->getMessage());
        }
    }

    public function removeItem(string $cartItemId, RemoveFromCartAction $action): void
    {
        $cartItem = CartItem::query()->with('cart')->findOrFail($cartItemId);
        $action->execute(Auth::user(), $cartItem);
        unset($this->quantities[$cartItemId]);

        Flux::toast(text: __('Item removed from cart.'));
    }

    #[Computed]
    public function cart(): ?Cart
    {
        return Cart::query()
            ->where('user_id', Auth::id())
            ->with([
                'items.product.vendor',
            ])
            ->first();
    }

    #[Computed]
    public function groupedItems(): array
    {
        if ($this->cart === null) {
            return [];
        }

        /** @var Collection<int, CartItem> $items */
        $items = $this->cart->items;

        return $items
            ->groupBy(fn (CartItem $item): string => $item->product->vendor->id)
            ->map(function (Collection $vendorItems): array {
                /** @var CartItem $firstItem */
                $firstItem = $vendorItems->first();
                $subtotal = $vendorItems->sum(fn (CartItem $item): float => (float) $item->unit_price * $item->quantity);

                return [
                    'vendor_name' => $firstItem->product->vendor->store_name,
                    'items' => $vendorItems,
                    'subtotal' => $subtotal,
                ];
            })
            ->values()
            ->all();
    }

    #[Computed]
    public function cartTotal(): float
    {
        return collect($this->groupedItems)->sum('subtotal');
    }

    private function syncQuantities(): void
    {
        $cart = Cart::query()
            ->where('user_id', Auth::id())
            ->with('items')
            ->first();

        if ($cart === null) {
            $this->quantities = [];

            return;
        }

        $this->quantities = $cart->items
            ->mapWithKeys(fn (CartItem $item): array => [$item->id => $item->quantity])
            ->all();
    }
}; ?>

<section class="space-y-8">
    <div class="space-y-2">
        <flux:heading size="xl">{{ __('Your Cart') }}</flux:heading>
        <flux:text class="text-zinc-600 dark:text-zinc-300">{{ __('Review items grouped by vendor and adjust quantities in real time.') }}</flux:text>
        <div class="pt-2">
            <flux:button :href="route('buyer.orders.index')" wire:navigate icon="receipt-percent">
                {{ __('Order History') }}
            </flux:button>
        </div>
    </div>

    @if (empty($this->groupedItems))
        <div class="rounded-xl border border-dashed border-neutral-300 p-8 text-center dark:border-neutral-700">
            <flux:text>{{ __('Your cart is currently empty.') }}</flux:text>
            <div class="mt-4">
                <flux:button :href="route('home')" wire:navigate>
                    {{ __('Browse marketplace') }}
                </flux:button>
            </div>
        </div>
    @else
        <div class="space-y-6">
            @foreach ($this->groupedItems as $group)
                <article class="rounded-2xl border border-neutral-200 bg-white p-5 shadow-sm dark:border-neutral-700 dark:bg-zinc-900">
                    <div class="mb-4 flex items-center justify-between">
                        <flux:heading size="md">{{ $group['vendor_name'] }}</flux:heading>
                        <flux:text class="font-semibold">
                            {{ __('Subtotal: $:amount', ['amount' => number_format($group['subtotal'], 2)]) }}
                        </flux:text>
                    </div>

                    <div class="space-y-3">
                        @foreach ($group['items'] as $item)
                            <div wire:key="cart-item-{{ $item->id }}" class="grid gap-3 rounded-xl border border-neutral-200 p-3 dark:border-neutral-700 md:grid-cols-12 md:items-end">
                                <div class="md:col-span-4">
                                    <flux:text class="font-medium">{{ $item->product->name }}</flux:text>
                                </div>

                                <div class="md:col-span-2">
                                    <flux:text>{{ __('$ :price', ['price' => number_format((float) $item->unit_price, 2)]) }}</flux:text>
                                </div>

                                <div class="md:col-span-2">
                                    <flux:input
                                        wire:model.live="quantities.{{ $item->id }}"
                                        type="number"
                                        min="1"
                                        :label="__('Qty')"
                                    />
                                    @error("quantities.{$item->id}") <flux:text class="text-red-600 text-xs">{{ $message }}</flux:text> @enderror
                                </div>

                                <div class="md:col-span-2">
                                    <flux:text class="text-indigo-600 dark:text-indigo-400 font-semibold">
                                        {{ __('$ :line', ['line' => number_format((float) $item->unit_price * $item->quantity, 2)]) }}
                                    </flux:text>
                                </div>

                                <div class="flex gap-2 md:col-span-2 md:justify-end">
                                    <flux:button size="sm" wire:click="updateItemQuantity('{{ $item->id }}')">
                                        {{ __('Update') }}
                                    </flux:button>
                                    <flux:button size="sm" variant="danger" wire:click="removeItem('{{ $item->id }}')">
                                        {{ __('Remove') }}
                                    </flux:button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </article>
            @endforeach

            <div class="flex justify-end">
                <div class="rounded-xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-700 dark:bg-zinc-900">
                    <flux:heading size="lg">
                        {{ __('Total: $:amount', ['amount' => number_format($this->cartTotal, 2)]) }}
                    </flux:heading>
                    <div class="mt-3 flex justify-end">
                        <flux:button variant="primary" :href="route('checkout.index')" wire:navigate>
                            {{ __('Proceed to Checkout') }}
                        </flux:button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</section>
