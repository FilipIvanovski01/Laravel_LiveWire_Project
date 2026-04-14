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

<section class="mx-auto w-full max-w-3xl space-y-6">
    <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
        <div class="space-y-2">
            <flux:heading size="xl">{{ $this->product->name }}</flux:heading>
            <flux:text>{{ __('Sold by :vendor', ['vendor' => $this->product->vendor->store_name]) }}</flux:text>
            <flux:link :href="route('vendors.show', ['vendor' => $this->product->vendor->slug])" wire:navigate>
                {{ __('View Storefront') }}
            </flux:link>
        </div>

        <flux:text class="mt-4">${{ number_format((float) $this->product->price, 2) }}</flux:text>
        <flux:text class="mt-4">{{ $this->product->description }}</flux:text>
        <flux:text class="mt-4 text-sm">{{ __('Stock: :count', ['count' => $this->product->stock_quantity]) }}</flux:text>
        @auth
            <div class="mt-6">
                <flux:button variant="primary" wire:click="addToCart">
                    {{ __('Add to cart') }}
                </flux:button>
            </div>
        @endauth
    </div>
</section>
