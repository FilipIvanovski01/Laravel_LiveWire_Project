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
    <div class="rounded-2xl border border-[#E5E7EB] bg-white p-6 shadow-sm">
        <flux:heading size="xl" class="text-[#212529]">{{ $this->vendor->store_name }}</flux:heading>
        <flux:text class="mt-2 text-[#6C757D]">{{ $this->vendor->description ?: __('No store description available.') }}</flux:text>
    </div>

    <div>
        <flux:heading size="lg" class="text-[#212529]">{{ __('Active Listings') }}</flux:heading>
    </div>

    @if ($this->products->isEmpty())
        <div class="rounded-xl border border-dashed border-[#E5E7EB] bg-white p-8 text-center">
            <flux:text class="text-[#6C757D]">{{ __('This vendor has no active products right now.') }}</flux:text>
        </div>
    @else
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($this->products as $product)
                <article wire:key="vendor-public-product-{{ $product->id }}" class="rounded-xl border border-[#E5E7EB] bg-white p-4 shadow-sm">
                    <img
                        src="{{ $product->image_url_for_display }}"
                        onerror="this.onerror=null;this.src='https://placehold.co/640x480/F8F9FA/6C757D?text=No+Image';"
                        class="mb-3 h-36 w-full rounded-lg object-cover"
                    />
                    <flux:heading size="sm" class="text-[#212529]">{{ $product->name }}</flux:heading>
                    <flux:text class="mt-2 line-clamp-2 text-[#6C757D]">{{ $product->description }}</flux:text>
                    <div class="mt-4 flex items-center justify-between">
                        <flux:text class="font-semibold text-[#007BFF]">${{ number_format((float) $product->price, 2) }}</flux:text>
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
