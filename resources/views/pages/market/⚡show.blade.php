<?php

use App\Domain\ProductCatalog\Models\Product;
use Livewire\Attributes\Computed;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
    <flux:link :href="route('home')" wire:navigate>{{ __('Back to marketplace') }}</flux:link>

    <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
        <div class="space-y-2">
            <flux:heading size="xl">{{ $this->product->name }}</flux:heading>
            <flux:text>{{ __('Sold by :vendor', ['vendor' => $this->product->vendor->store_name]) }}</flux:text>
        </div>

        <flux:text class="mt-4">${{ number_format((float) $this->product->price, 2) }}</flux:text>
        <flux:text class="mt-4">{{ $this->product->description }}</flux:text>
        <flux:text class="mt-4 text-sm">{{ __('Stock: :count', ['count' => $this->product->stock_quantity]) }}</flux:text>
    </div>
</section>
