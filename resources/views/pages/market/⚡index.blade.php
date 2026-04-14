<?php

use App\Domain\Cart\Actions\AddToCartAction;
use App\Domain\ProductCatalog\Models\Vendor;
use App\Domain\ProductCatalog\Services\MarketplaceSearchService;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
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

    public function addToCart(string $productId, AddToCartAction $addToCartAction): void
    {
        if (! Auth::check()) {
            $this->redirect(route('login'), navigate: true);

            return;
        }

        $product = $this->products->getCollection()->firstWhere('id', $productId);

        if ($product === null) {
            return;
        }

        try {
            $addToCartAction->execute(Auth::user(), $product, 1);
            Flux::toast(variant: 'success', text: __('Product added to cart.'));
        } catch (ValidationException $exception) {
            Flux::toast(variant: 'danger', text: $exception->getMessage());
        }
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

<section class="w-full space-y-8">
    <div class="space-y-2">
        <flux:heading size="xl">{{ __('Discover Products') }}</flux:heading>
        <flux:text class="text-zinc-600 dark:text-zinc-300">{{ __('Browse products from trusted vendors and add them to your cart instantly.') }}</flux:text>
    </div>

    <div class="grid gap-4 rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-700 dark:bg-zinc-900 md:grid-cols-4">
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
        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($this->products as $product)
                <article wire:key="product-{{ $product->id }}" class="rounded-2xl border border-neutral-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-neutral-700 dark:bg-zinc-900">
                    <div class="mb-2">
                        <flux:heading size="md">{{ $product->name }}</flux:heading>
                        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">{{ $product->vendor->store_name }}</flux:text>
                    </div>
                    <flux:text class="line-clamp-2">{{ $product->description }}</flux:text>
                    <div class="mt-4 flex items-center justify-between">
                        <flux:text class="text-lg font-bold text-indigo-600 dark:text-indigo-400">${{ number_format((float) $product->price, 2) }}</flux:text>
                        <div class="flex gap-2">
                            <flux:button size="sm" :href="route('market.products.show', $product)" wire:navigate>
                                {{ __('View') }}
                            </flux:button>
                            @auth
                                <flux:button size="sm" variant="primary" wire:click="addToCart('{{ $product->id }}')">
                                    {{ __('Add') }}
                                </flux:button>
                            @endauth
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="pt-2">
            {{ $this->products->links() }}
        </div>
    @endif
</section>
