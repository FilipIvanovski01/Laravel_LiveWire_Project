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
            $this->dispatch('cart-updated');
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
        <flux:heading size="xl" class="text-[#212529]">{{ __('Discover Products') }}</flux:heading>
        <flux:text class="text-[#6C757D]">{{ __('Browse curated products from trusted vendors and add them instantly.') }}</flux:text>
    </div>

    <div class="grid gap-4 rounded-2xl border border-[#E5E7EB] bg-white p-5 shadow-sm md:grid-cols-4">
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
        <div class="rounded-2xl border border-dashed border-[#E5E7EB] bg-white p-10 text-center">
            <flux:text class="text-[#6C757D]">{{ __('No products match your filters.') }}</flux:text>
        </div>
    @else
        <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($this->products as $product)
                <article wire:key="product-{{ $product->id }}" class="overflow-hidden rounded-2xl border border-[#E5E7EB] bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="aspect-4/3 w-full bg-[#F8F9FA]">
                        <img
                            src="{{ $product->image_url_for_display }}"
                            onerror="this.onerror=null;this.src='https://placehold.co/640x480/F8F9FA/6C757D?text=No+Image';"
                            class="h-full w-full object-cover"
                            loading="lazy"
                        />
                    </div>
                    <div class="space-y-3 p-4">
                        <div>
                            <flux:heading size="sm" class="text-[#212529]">{{ $product->name }}</flux:heading>
                            <flux:text class="text-sm text-[#6C757D]">{{ $product->vendor->store_name }}</flux:text>
                        </div>
                        <flux:text class="line-clamp-2 text-sm text-[#6C757D]">{{ $product->description }}</flux:text>
                        <div class="flex items-center justify-between">
                            <flux:text class="text-base font-semibold text-[#007BFF]">${{ number_format((float) $product->price, 2) }}</flux:text>
                            <flux:badge color="{{ $product->stock_quantity > 0 ? 'emerald' : 'red' }}">
                                {{ $product->stock_quantity > 0 ? __('In stock') : __('Out of stock') }}
                            </flux:badge>
                        </div>
                        <div class="grid w-full grid-cols-2 gap-2">
                            <flux:button
                                size="sm"
                                class="justify-center border border-[#E5E7EB] bg-white text-[#212529] hover:bg-[#F8F9FA] focus:ring-2 focus:ring-[#007BFF]"
                                :href="route('market.products.show', $product)"
                                wire:navigate
                            >
                                {{ __('View') }}
                            </flux:button>
                            @auth
                                <flux:button
                                    size="sm"
                                    variant="primary"
                                    class="justify-center bg-[#007BFF] hover:bg-[#0069d9] focus:ring-2 focus:ring-[#007BFF]"
                                    wire:click="addToCart('{{ $product->id }}')"
                                    wire:loading.attr="disabled"
                                >
                                    {{ __('Add to cart') }}
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
