<?php

use App\Domain\ProductCatalog\Models\Product;
use App\Domain\ProductCatalog\Models\Vendor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Vendor Store')] class extends Component {
    use WithPagination;

    #[Locked]
    public string $vendorId;

    public function mount(Vendor $vendor): void
    {
        abort_unless($vendor->is_active, 404);
        $this->vendorId = $vendor->id;
    }

    #[Computed]
    public function vendor(): Vendor
    {
        return Vendor::query()->where('id', $this->vendorId)->firstOrFail();
    }

    #[Computed]
    public function products(): LengthAwarePaginator
    {
        return Product::query()
            ->where('vendor_id', $this->vendorId)
            ->marketplaceVisible()
            ->latest()
            ->paginate(12);
    }
}; ?>

<section class="space-y-6">
    <div class="rounded-2xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-700 dark:bg-zinc-900">
        <flux:heading size="xl">{{ $this->vendor->store_name }}</flux:heading>
        <flux:text class="mt-2">{{ $this->vendor->description ?: __('No store description available.') }}</flux:text>
    </div>

    <div>
        <flux:heading size="lg">{{ __('Active Listings') }}</flux:heading>
    </div>

    @if ($this->products->isEmpty())
        <div class="rounded-xl border border-dashed border-neutral-300 p-8 text-center dark:border-neutral-700">
            <flux:text>{{ __('This vendor has no active products right now.') }}</flux:text>
        </div>
    @else
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($this->products as $product)
                <article wire:key="vendor-public-product-{{ $product->id }}" class="rounded-xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-700 dark:bg-zinc-900">
                    <flux:heading size="sm">{{ $product->name }}</flux:heading>
                    <flux:text class="mt-2 line-clamp-2">{{ $product->description }}</flux:text>
                    <div class="mt-4 flex items-center justify-between">
                        <flux:text class="font-semibold text-indigo-600 dark:text-indigo-400">${{ number_format((float) $product->price, 2) }}</flux:text>
                        <flux:button size="sm" :href="route('market.products.show', $product)" wire:navigate>
                            {{ __('View Product') }}
                        </flux:button>
                    </div>
                </article>
            @endforeach
        </div>

        {{ $this->products->links() }}
    @endif
</section>
