<?php

use App\Domain\ProductCatalog\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Vendor Products')] class extends Component {
    use WithPagination;

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
            <flux:heading size="lg">{{ __('Vendor Products') }}</flux:heading>
            <flux:text>{{ __('Manage all listings from your store in one place.') }}</flux:text>
        </div>
        <flux:button variant="primary" :href="route('vendor.products.create')" wire:navigate icon="plus">{{ __('Add Product') }}</flux:button>
    </div>

    @if ($this->products->isEmpty())
        <div class="rounded-xl border border-dashed border-neutral-300 p-8 text-center dark:border-neutral-700">
            <flux:text>{{ __('No products yet. Add your first product to start selling.') }}</flux:text>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($this->products as $product)
                <article wire:key="vendor-product-{{ $product->id }}" class="rounded-xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-700 dark:bg-zinc-900">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <flux:heading size="sm">{{ $product->name }}</flux:heading>
                            <flux:text class="text-sm">
                                {{ __('Status: :status | Stock: :stock', ['status' => ucfirst($product->status), 'stock' => $product->stock_quantity]) }}
                            </flux:text>
                            <flux:text class="text-xs text-zinc-500">{{ $product->created_at?->format('M d, Y') }}</flux:text>
                        </div>
                        <div class="flex items-center gap-3">
                            <flux:text class="font-semibold text-indigo-600 dark:text-indigo-400">
                                ${{ number_format((float) $product->price, 2) }}
                            </flux:text>
                            <flux:button size="sm" :href="route('vendor.products.edit', $product)" wire:navigate>
                                {{ __('Edit') }}
                            </flux:button>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        {{ $this->products->links() }}
    @endif
</section>
