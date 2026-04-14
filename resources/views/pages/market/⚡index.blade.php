<?php

use App\Domain\ProductCatalog\Models\Vendor;
use App\Domain\ProductCatalog\Services\MarketplaceSearchService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Marketplace')] class extends Component {
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $vendor_id = '';

    #[Url(except: '')]
    public string $min_price = '';

    #[Url(except: '')]
    public string $max_price = '';

    public int $perPage = 12;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingVendorId(): void
    {
        $this->resetPage();
    }

    public function updatingMinPrice(): void
    {
        $this->resetPage();
    }

    public function updatingMaxPrice(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function vendors(): Collection
    {
        return Vendor::query()
            ->where('is_active', true)
            ->orderBy('store_name')
            ->get(['id', 'store_name']);
    }

    #[Computed]
    public function products(): LengthAwarePaginator
    {
        return app(MarketplaceSearchService::class)->search([
            'search' => $this->search,
            'vendor_id' => $this->vendor_id,
            'min_price' => $this->min_price,
            'max_price' => $this->max_price,
        ], $this->perPage);
    }
}; ?>

<section class="w-full space-y-6">
    <div class="space-y-2">
        <flux:heading size="xl">{{ __('Marketplace') }}</flux:heading>
        <flux:text>{{ __('Browse products from all active vendors.') }}</flux:text>
    </div>

    <div class="grid gap-4 rounded-xl border border-neutral-200 p-4 dark:border-neutral-700 md:grid-cols-4">
        <flux:input wire:model.live.debounce.400ms="search" :label="__('Search')" :placeholder="__('Search by product name')" />

        <flux:select wire:model.live="vendor_id" :label="__('Vendor')">
            <option value="">{{ __('All vendors') }}</option>
            @foreach ($this->vendors as $vendor)
                <option value="{{ $vendor->id }}">{{ $vendor->store_name }}</option>
            @endforeach
        </flux:select>

        <flux:input wire:model.live.debounce.400ms="min_price" :label="__('Min Price')" type="number" min="0" step="0.01" />

        <flux:input wire:model.live.debounce.400ms="max_price" :label="__('Max Price')" type="number" min="0" step="0.01" />
    </div>

    @if ($this->products->isEmpty())
        <div class="rounded-xl border border-dashed border-neutral-300 p-8 text-center dark:border-neutral-700">
            <flux:text>{{ __('No products match your filters.') }}</flux:text>
        </div>
    @else
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($this->products as $product)
                <article wire:key="product-{{ $product->id }}" class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
                    <div class="mb-2">
                        <flux:heading size="md">{{ $product->name }}</flux:heading>
                        <flux:text class="text-sm">{{ $product->vendor->store_name }}</flux:text>
                    </div>
                    <flux:text class="line-clamp-2">{{ $product->description }}</flux:text>
                    <div class="mt-4 flex items-center justify-between">
                        <flux:text class="font-semibold">${{ number_format((float) $product->price, 2) }}</flux:text>
                        <flux:button size="sm" :href="route('market.products.show', $product)" wire:navigate>
                            {{ __('View') }}
                        </flux:button>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="pt-2">
            {{ $this->products->links() }}
        </div>
    @endif
</section>
