<?php

use App\Domain\ProductCatalog\Actions\PublishProductAction;
use App\Domain\ProductCatalog\Enums\ProductStatus;
use App\Domain\ProductCatalog\Models\Product;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Vendor Products')] class extends Component {
    use WithPagination;

    public function publish(string $productId, PublishProductAction $publishProductAction): void
    {
        $product = Product::query()
            ->where('vendor_id', Auth::user()->vendor->id)
            ->findOrFail($productId);

        $publishProductAction->execute(Auth::user(), $product);
        Flux::toast(variant: 'success', text: __('Product is now visible in marketplace.'));
    }

    #[Computed]
    public function products(): LengthAwarePaginator
    {
        return Product::query()
            ->where('vendor_id', Auth::user()->vendor->id)
            ->latest()
            ->paginate(12);
    }
}; ?>

<section class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="lg" class="text-[#212529]">{{ __('Vendor Products') }}</flux:heading>
            <flux:text class="text-[#6C757D]">{{ __('Manage all listings from your store in one place.') }}</flux:text>
        </div>
        <flux:button variant="primary" class="bg-[#007BFF] hover:bg-[#0069d9]" :href="route('vendor.products.create')" wire:navigate icon="plus">{{ __('Add Product') }}</flux:button>
    </div>

    @if ($this->products->isEmpty())
        <div class="rounded-xl border border-dashed border-[#E5E7EB] bg-white p-8 text-center">
            <flux:text class="text-[#6C757D]">{{ __('No products yet. Add your first product to start selling.') }}</flux:text>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($this->products as $product)
                <article wire:key="vendor-product-{{ $product->id }}" class="rounded-xl border border-[#E5E7EB] bg-white p-4 shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <flux:heading size="sm">{{ $product->name }}</flux:heading>
                            <flux:text class="text-sm">
                                {{ __('Status: :status | Stock: :stock', ['status' => ucfirst($product->status->value), 'stock' => $product->stock_quantity]) }}
                            </flux:text>
                            <flux:text class="text-xs {{ $product->status === ProductStatus::Active ? 'text-[#28A745]' : 'text-[#6C757D]' }}">
                                {{ $product->status === ProductStatus::Active ? __('Visible in marketplace') : __('Hidden from marketplace') }}
                            </flux:text>
                            <flux:text class="text-xs text-[#6C757D]">{{ $product->created_at?->format('M d, Y') }}</flux:text>
                        </div>
                        <div class="flex items-center gap-3">
                            <flux:text class="font-semibold text-[#007BFF]">
                                ${{ number_format((float) $product->price, 2) }}
                            </flux:text>
                            <flux:button size="sm" :href="route('vendor.products.edit', $product)" wire:navigate>
                                {{ __('Edit') }}
                            </flux:button>
                            @if ($product->status !== ProductStatus::Active)
                                <flux:button size="sm" variant="primary" class="bg-[#007BFF] hover:bg-[#0069d9]" wire:click="publish('{{ $product->id }}')">
                                    {{ __('Publish') }}
                                </flux:button>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        {{ $this->products->links() }}
    @endif
</section>
