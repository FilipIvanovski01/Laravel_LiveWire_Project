<?php

use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Profile')] class extends Component {
    public string $store_name = '';
    public ?string $description = null;

    public function mount(): void
    {
        $vendor = Auth::user()?->vendor;

        if ($vendor !== null) {
            $this->store_name = $vendor->store_name;
            $this->description = $vendor->description;
        }
    }

    public function updateVendorProfile(): void
    {
        $user = Auth::user();
        $vendor = $user?->vendor;

        if ($vendor === null) {
            return;
        }

        $validated = $this->validate([
            'store_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $vendor->update([
            'store_name' => $validated['store_name'],
            'description' => $validated['description'] ?? null,
        ]);

        Flux::toast(variant: 'success', text: __('Vendor profile updated.'));
    }
}; ?>

<section class="mx-auto w-full max-w-3xl space-y-6">
    <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
        <flux:heading size="lg">{{ __('Profile') }}</flux:heading>
        <flux:text class="mt-2">{{ auth()->user()->name }}</flux:text>
        <flux:text>{{ auth()->user()->email }}</flux:text>
    </div>

    @if (auth()->user()->vendor === null)
        <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
            <flux:heading size="md">{{ __('Become a Vendor') }}</flux:heading>
            <flux:text class="mt-2">{{ __('Create your store profile to start managing products and vendor orders.') }}</flux:text>
            <div class="mt-4">
                <flux:button variant="primary" :href="route('vendor.onboarding')" wire:navigate>
                    {{ __('Become a Vendor') }}
                </flux:button>
            </div>
        </div>
    @else
        <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
            <flux:heading size="md">{{ __('Vendor Profile') }}</flux:heading>
            <form wire:submit="updateVendorProfile" class="mt-4 space-y-4">
                <flux:input wire:model="store_name" :label="__('Store name')" required />
                <flux:textarea wire:model="description" :label="__('Store description')" rows="4" />
                <flux:button variant="primary" type="submit">{{ __('Save Vendor Profile') }}</flux:button>
            </form>
            <div class="mt-6 flex flex-wrap gap-2">
                <flux:button :href="route('vendor.products.index')" wire:navigate>{{ __('Manage Products') }}</flux:button>
                <flux:button :href="route('vendor.orders.index')" wire:navigate>{{ __('View Vendor Orders') }}</flux:button>
            </div>
        </div>
    @endif
</section>
