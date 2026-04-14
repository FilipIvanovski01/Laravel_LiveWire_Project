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
            $this->dispatch('cart-updated');
            Flux::toast(variant: 'success', text: __('Product added to cart.'));
        } catch (ValidationException $exception) {
            Flux::toast(variant: 'danger', text: $exception->getMessage());
        }
    }

    public function increaseQuantity(string $cartItemId, UpdateCartItemQuantityAction $action): void
    {
        $current = isset($this->quantities[$cartItemId]) ? (int) $this->quantities[$cartItemId] : 1;
        $this->applyQuantityUpdate($cartItemId, $current + 1, $action);
    }

    public function decreaseQuantity(string $cartItemId, UpdateCartItemQuantityAction $action): void
    {
        $current = isset($this->quantities[$cartItemId]) ? (int) $this->quantities[$cartItemId] : 1;
        $next = max(1, $current - 1);
        $this->applyQuantityUpdate($cartItemId, $next, $action);
    }

    public function updatedQuantities(string $value, string $key): void
    {
        $cartItemId = (string) str($key)->afterLast('.');
        $quantity = max(1, (int) $value);
        $this->quantities[$cartItemId] = $quantity;
    }

    public function blurQuantity(string $cartItemId, UpdateCartItemQuantityAction $action): void
    {
        $quantity = isset($this->quantities[$cartItemId]) ? (int) $this->quantities[$cartItemId] : 1;
        $this->applyQuantityUpdate($cartItemId, max(1, $quantity), $action);
    }

    private function applyQuantityUpdate(string $cartItemId, int $quantity, UpdateCartItemQuantityAction $action): void
    {
        $cartItem = CartItem::query()->with(['cart', 'product'])->findOrFail($cartItemId);

        try {
            $action->execute(Auth::user(), $cartItem, $quantity);
            $this->quantities[$cartItemId] = $quantity;
            $this->syncQuantities();
            $this->dispatch('cart-updated');
            $this->resetErrorBag("quantities.{$cartItemId}");
        } catch (ValidationException $exception) {
            $this->addError("quantities.{$cartItemId}", $exception->getMessage());
            $this->quantities[$cartItemId] = $cartItem->quantity;
        }
    }

    public function removeItem(string $cartItemId, RemoveFromCartAction $action): void
    {
        $cartItem = CartItem::query()->with('cart')->findOrFail($cartItemId);
        $action->execute(Auth::user(), $cartItem);
        unset($this->quantities[$cartItemId]);
        $this->dispatch('cart-updated');

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
            ->filter(fn (CartItem $item): bool => $item->product !== null && $item->product->vendor !== null)
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
        <flux:heading size="xl" class="text-[#212529]">{{ __('Your Cart') }}</flux:heading>
        <flux:text class="text-[#6C757D]">{{ __('Review items grouped by vendor and adjust quantities in real time.') }}</flux:text>
        <div class="pt-2">
            <flux:button class="border border-[#E5E7EB] bg-white text-[#212529] hover:bg-[#F8F9FA]" :href="route('buyer.orders.index')" wire:navigate icon="receipt-percent">
                {{ __('Order History') }}
            </flux:button>
        </div>
    </div>

    @if (empty($this->groupedItems))
        <div class="rounded-2xl border border-dashed border-[#E5E7EB] bg-white p-10 text-center">
            <flux:text class="text-[#6C757D]">{{ __('Your cart is currently empty.') }}</flux:text>
            <div class="mt-4">
                <flux:button class="bg-[#007BFF] hover:bg-[#0069d9]" variant="primary" :href="route('home')" wire:navigate>
                    {{ __('Browse marketplace') }}
                </flux:button>
            </div>
        </div>
    @else
        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
            @foreach ($this->groupedItems as $group)
                <article class="rounded-2xl border border-[#E5E7EB] bg-white p-5 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <flux:heading size="md" class="text-[#212529]">{{ $group['vendor_name'] }}</flux:heading>
                        <flux:text class="font-semibold text-[#212529]">
                            {{ __('Subtotal: $:amount', ['amount' => number_format($group['subtotal'], 2)]) }}
                        </flux:text>
                    </div>

                    <div class="space-y-3">
                        @foreach ($group['items'] as $item)
                            <div wire:key="cart-item-{{ $item->id }}" class="grid gap-3 rounded-xl border border-[#E5E7EB] bg-[#F8F9FA] p-3 md:grid-cols-12 md:items-end">
                                <div class="md:col-span-4">
                                    <flux:text class="font-medium text-[#212529]">{{ $item->product->name }}</flux:text>
                                </div>

                                <div class="md:col-span-2">
                                    <flux:text>{{ __('$ :price', ['price' => number_format((float) $item->unit_price, 2)]) }}</flux:text>
                                </div>

                                <div class="md:col-span-3">
                                    <label class="mb-1 block text-xs font-medium text-[#6C757D]">{{ __('Qty') }}</label>
                                    <div class="flex items-center rounded-lg border border-[#E5E7EB] bg-white">
                                        <button
                                            type="button"
                                            class="px-3 py-2 text-[#212529] hover:bg-[#F8F9FA] focus:outline-none focus:ring-2 focus:ring-[#007BFF]"
                                            wire:click="decreaseQuantity('{{ $item->id }}')"
                                            aria-label="{{ __('Decrease quantity') }}"
                                        >
                                            -
                                        </button>
                                        <input
                                            type="number"
                                            min="1"
                                            class="w-14 border-x border-[#E5E7EB] bg-transparent px-2 py-2 text-center text-[#212529] focus:outline-none"
                                            wire:model.live="quantities.{{ $item->id }}"
                                            wire:blur="blurQuantity('{{ $item->id }}')"
                                        />
                                        <button
                                            type="button"
                                            class="px-3 py-2 text-[#212529] hover:bg-[#F8F9FA] focus:outline-none focus:ring-2 focus:ring-[#007BFF]"
                                            wire:click="increaseQuantity('{{ $item->id }}')"
                                            aria-label="{{ __('Increase quantity') }}"
                                        >
                                            +
                                        </button>
                                    </div>
                                    @error("quantities.{$item->id}") <flux:text class="text-xs text-[#DC3545]">{{ $message }}</flux:text> @enderror
                                </div>

                                <div class="md:col-span-2">
                                    <flux:text class="font-semibold text-[#007BFF]">
                                        {{ __('$ :line', ['line' => number_format((float) $item->unit_price * $item->quantity, 2)]) }}
                                    </flux:text>
                                </div>

                                <div class="flex gap-2 md:col-span-1 md:justify-end">
                                    <flux:button size="sm" variant="danger" wire:click="removeItem('{{ $item->id }}')">
                                        {{ __('Remove') }}
                                    </flux:button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </article>
            @endforeach
            </div>

            <div class="lg:sticky lg:top-6 lg:h-fit">
                <div class="rounded-2xl border border-[#E5E7EB] bg-white p-5 shadow-sm">
                    <flux:heading size="md" class="text-[#212529]">{{ __('Order Summary') }}</flux:heading>
                    <flux:heading size="lg" class="mt-2 text-[#007BFF]">
                        {{ __('Total: $:amount', ['amount' => number_format($this->cartTotal, 2)]) }}
                    </flux:heading>
                    <div class="mt-3 flex justify-end">
                        <flux:button variant="primary" class="w-full bg-[#007BFF] hover:bg-[#0069d9]" :href="route('checkout.index')" wire:navigate>
                            {{ __('Proceed to Checkout') }}
                        </flux:button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</section>
