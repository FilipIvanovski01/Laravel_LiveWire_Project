<?php

use App\Domain\ProductCatalog\Actions\CreateVendorAction;
use App\Domain\ProductCatalog\DTOs\CreateVendorDTO;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Become a vendor')] class extends Component {
    public string $store_name = '';
    public ?string $description = null;

    public function mount(): void
    {
        if (Auth::user()?->vendor !== null) {
            $this->redirectIntended(default: route('vendor.products.index', absolute: false));
        }
    }

    public function createVendorProfile(CreateVendorAction $createVendorAction): void
    {
        $validated = $this->validate([
            'store_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $createVendorAction->execute(
            Auth::user(),
            new CreateVendorDTO(
                storeName: $validated['store_name'],
                description: $validated['description'] ?? null,
            ),
        );

        Flux::toast(variant: 'success', text: __('Your vendor profile is ready.'));
        $this->redirectIntended(default: route('vendor.products.index', absolute: false), navigate: true);
    }
}; 
?>
<section class="mx-auto w-full max-w-2xl">
    <div class="mb-6">
        <flux:heading size="xl">{{ __('Create your vendor profile') }}</flux:heading>
        <flux:text class="mt-2">
            {{ __('Once created, you can access product management and vendor order pages.') }}
        </flux:text>
    </div>

    <form wire:submit="createVendorProfile" class="space-y-6 rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
        <flux:input wire:model="store_name" :label="__('Store name')" required />

        <flux:textarea wire:model="description" :label="__('Store description')" rows="4" />

        <div class="flex justify-end">
            <flux:button variant="primary" type="submit">
                {{ __('Create vendor profile') }}
            </flux:button>
        </div>
    </form>
</section>