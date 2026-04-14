<?php

use App\Domain\ProductCatalog\Actions\UpdateProductAction;
use App\Domain\ProductCatalog\DTOs\UpdateProductDTO;
use App\Domain\ProductCatalog\Models\Product;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Edit Product')] class extends Component {
    #[Locked]
    public string $productId;

    public string $name = '';
    public string $description = '';
    public string $price = '';
    public string $stock_quantity = '';
    public string $image_url = '';
    public string $status = 'active';

    public function mount(Product $product): void
    {
        Gate::authorize('update', $product);

        $this->productId = $product->id;
        $this->name = $product->name;
        $this->description = $product->description;
        $this->price = (string) $product->price;
        $this->stock_quantity = (string) $product->stock_quantity;
        $this->image_url = $product->image_url;
        $this->status = $product->status;
    }

    public function save(UpdateProductAction $action): void
    {
        $product = Product::query()->findOrFail($this->productId);
        Gate::authorize('update', $product);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:2000'],
            'price' => ['required', 'numeric', 'min:0.01'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'image_url' => ['required', 'url', 'max:2048'],
            'status' => ['required', 'in:draft,active,archived'],
        ]);

        $action->execute(
            $product,
            new UpdateProductDTO(
                name: $validated['name'],
                description: $validated['description'],
                price: (float) $validated['price'],
                stockQuantity: (int) $validated['stock_quantity'],
                imageUrl: $validated['image_url'],
                status: $validated['status'],
            ),
        );

        Flux::toast(variant: 'success', text: __('Product updated.'));
        $this->redirect(route('vendor.products.index'), navigate: true);
    }
}; ?>

<section class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="lg">{{ __('Edit Product') }}</flux:heading>
            <flux:text>{{ __('Update listing details for your store.') }}</flux:text>
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
            <flux:button variant="primary" type="submit">{{ __('Save Changes') }}</flux:button>
        </div>
    </form>
</section>
