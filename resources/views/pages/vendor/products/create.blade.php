<?php

use App\Domain\ProductCatalog\Actions\CreateProductAction;
use App\Domain\ProductCatalog\DTOs\CreateProductDTO;
use App\Domain\ProductCatalog\Enums\ProductStatus;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Add Product')] class extends Component {
    use WithFileUploads;

    public string $name = '';
    public string $description = '';
    public string $price = '';
    public string $stock_quantity = '';
    public $image = null;
    public string $status = 'active';

    public function save(CreateProductAction $action): void
    {
        Gate::authorize('create', \App\Domain\ProductCatalog\Models\Product::class);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:2000'],
            'price' => ['required', 'numeric', 'min:0.01'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'image' => ['required', 'image', 'max:3072', 'mimes:jpg,jpeg,png,webp'],
            'status' => ['required', Rule::enum(ProductStatus::class)],
        ]);

        $vendor = Auth::user()->vendor;
        $filename = Str::uuid()->toString().'.'.$this->image->getClientOriginalExtension();
        $imagePath = $this->image->storePubliclyAs('products', $filename, 'public');
        $action->execute(
            $vendor,
            new CreateProductDTO(
                name: $validated['name'],
                description: $validated['description'],
                price: (float) $validated['price'],
                stockQuantity: (int) $validated['stock_quantity'],
                imageUrl: $imagePath,
                status: ProductStatus::from($validated['status']),
            ),
        );

        Flux::toast(variant: 'success', text: __('Product created successfully.'));
        $this->redirect(route('vendor.products.index'), navigate: true);
    }
}; ?>

<section class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="lg">{{ __('Add Product') }}</flux:heading>
            <flux:text>{{ __('Create a new listing for your store.') }}</flux:text>
        </div>
        <flux:button :href="route('vendor.products.index')" wire:navigate>{{ __('Back to Products') }}</flux:button>
    </div>

    <form wire:submit="save" class="space-y-4 rounded-2xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-700 dark:bg-zinc-900">
        <flux:input wire:model="name" :label="__('Product name')" required />
        <flux:textarea wire:model="description" :label="__('Description')" rows="4" required />

        <div class="grid gap-4 md:grid-cols-2">
            <flux:input wire:model="price" :label="__('Price')" type="number" step="0.01" min="0.01" required />
            <flux:input wire:model="stock_quantity" :label="__('Stock quantity')" type="number" min="0" required />
        </div>

        <div class="space-y-2">
            <flux:input wire:model="image" :label="__('Product image')" type="file" accept="image/png,image/jpeg,image/webp" required />
            <div class="rounded-xl border-2 border-dashed border-[#E5E7EB] bg-[#F8F9FA] p-4">
                @if ($image)
                    <img
                        src="{{ $image->temporaryUrl() }}"
                        onerror="this.onerror=null;this.src='https://placehold.co/640x480/F8F9FA/6C757D?text=No+Image';"
                        class="h-44 w-full rounded-lg object-cover"
                    />
                @else
                    <div class="flex h-44 flex-col items-center justify-center text-center">
                        <flux:icon name="photo" class="h-8 w-8 text-[#6C757D]" />
                        <p class="mt-2 text-sm font-medium text-[#212529]">{{ __('Upload product image') }}</p>
                        <p class="text-xs text-[#6C757D]">{{ __('Select a JPG, PNG, or WEBP image up to 3MB.') }}</p>
                    </div>
                @endif
            </div>
            @error('image') <flux:text class="text-xs text-[#DC3545]">{{ $message }}</flux:text> @enderror
        </div>

        <flux:select wire:model="status" :label="__('Status')">
            <option value="draft">{{ __('Draft') }}</option>
            <option value="active">{{ __('Active') }}</option>
            <option value="archived">{{ __('Archived') }}</option>
        </flux:select>
        <flux:text class="text-xs text-[#6C757D]">
            {{ __('Only products with Active status are visible in the marketplace.') }}
        </flux:text>

        <div class="flex justify-end">
            <flux:button variant="primary" type="submit">{{ __('Create Product') }}</flux:button>
        </div>
    </form>
</section>
