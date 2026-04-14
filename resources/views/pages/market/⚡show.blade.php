<?php

use App\Domain\Cart\Actions\AddToCartAction;
use App\Domain\ProductCatalog\Models\Product;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Product Details')] class extends Component {
    #[Locked]
    public string $productId;

    public function mount(Product $product): void
    {
        $this->productId = $product->id;
    }

    public function addToCart(AddToCartAction $addToCartAction): void
    {
        if (! Auth::check()) {
            $this->redirect(route('login'), navigate: true);

            return;
        }

        try {
            $addToCartAction->execute(Auth::user(), $this->product, 1);
            $this->dispatch('cart-updated');
            Flux::toast(variant: 'success', text: __('Product added to cart.'));
        } catch (ValidationException $exception) {
            Flux::toast(variant: 'danger', text: $exception->getMessage());
        }
    }

    #[Computed]
    public function product(): Product
    {
        $product = Product::query()
            ->with('vendor')
            ->marketplaceVisible()
            ->find($this->productId);

        if ($product === null) {
            throw new ModelNotFoundException();
        }

        return $product;
    }
}; ?>

<section class="mx-auto w-full max-w-5xl space-y-6">
    <div class="grid gap-6 rounded-2xl border border-[#E5E7EB] bg-white p-6 shadow-sm lg:grid-cols-2">
        <div class="overflow-hidden rounded-xl bg-[#F8F9FA]">
            <img
                src="{{ $this->product->image_url_for_display }}"
                onerror="this.onerror=null;this.src='https://placehold.co/640x480/F8F9FA/6C757D?text=No+Image';"
                class="h-full w-full object-cover"
            />
        </div>

        <div class="space-y-4">
            <div class="space-y-2">
                <flux:heading size="xl" class="text-[#212529]">{{ $this->product->name }}</flux:heading>
                <flux:text class="text-[#6C757D]">{{ __('Sold by :vendor', ['vendor' => $this->product->vendor->store_name]) }}</flux:text>
                <flux:link :href="route('vendors.show', ['vendor' => $this->product->vendor->slug])" wire:navigate>
                    {{ __('View Storefront') }}
                </flux:link>
            </div>

            <flux:text class="text-2xl font-semibold text-[#007BFF]">${{ number_format((float) $this->product->price, 2) }}</flux:text>
            <flux:text class="text-[#6C757D]">{{ $this->product->description }}</flux:text>
            <flux:text class="text-sm {{ $this->product->stock_quantity > 0 ? 'text-[#28A745]' : 'text-[#DC3545]' }}">
                {{ __('Stock: :count', ['count' => $this->product->stock_quantity]) }}
            </flux:text>
            @auth
                <div class="pt-2">
                    <flux:button
                        variant="primary"
                        class="min-w-40 bg-[#007BFF] hover:bg-[#0069d9] focus:ring-2 focus:ring-[#007BFF]"
                        wire:click="addToCart"
                        wire:loading.attr="disabled"
                    >
                        {{ __('Add to cart') }}
                    </flux:button>
                </div>
            @endauth
        </div>
    </div>
</section>
