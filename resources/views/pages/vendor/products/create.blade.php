<?php

use App\Domain\ProductCatalog\Actions\CreateProductAction;
use App\Domain\ProductCatalog\DTOs\CreateProductDTO;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Add Product')] class extends Component {
    public string $name = '';
    public string $description = '';
    public string $price = '';
    public string $stock_quantity = '';
    public string $image_url = '';
    public string $status = 'active';

    public function save(CreateProductAction $action): void
    {
        Gate::authorize('create', \App\Domain\ProductCatalog\Models\Product::class);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:2000'],
            'price' => ['required', 'numeric', 'min:0.01'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'image_url' => ['required', 'url', 'max:2048'],
            'status' => ['required', 'in:draft,active,archived'],
        ]);

        $vendor = Auth::user()->vendor;
        $action->execute(
            $vendor,
            new CreateProductDTO(
                name: $validated['name'],
                description: $validated['description'],
                price: (float) $validated['price'],
                stockQuantity: (int) $validated['stock_quantity'],
                imageUrl: $validated['image_url'],
                status: $validated['status'],
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

        <flux:input wire:model="image_url" :label="__('Image URL')" type="url" required />

        <flux:select wire:model="status" :label="__('Status')">
            <option value="draft">{{ __('Draft') }}</option>
            <option value="active">{{ __('Active') }}</option>
            <option value="archived">{{ __('Archived') }}</option>
        </flux:select>

        <div class="flex justify-end">
            <flux:button variant="primary" type="submit">{{ __('Create Product') }}</flux:button>
        </div>
    </form>
</section>
