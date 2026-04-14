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
    <div class="rounded-2xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-700 dark:bg-zinc-900">
        <flux:heading size="lg">{{ __('Account Dashboard') }}</flux:heading>
        <flux:text class="mt-2 font-medium">{{ auth()->user()->name }}</flux:text>
        <flux:text class="text-zinc-500 dark:text-zinc-400">{{ auth()->user()->email }}</flux:text>

        <div class="mt-6 flex flex-wrap gap-2">
            <flux:button :href="route('cart.index')" wire:navigate icon="shopping-cart">{{ __('Open Cart') }}</flux:button>
            <flux:button :href="route('buyer.orders.index')" wire:navigate icon="receipt-percent">{{ __('My Orders') }}</flux:button>
        </div>
    </div>

    @if (auth()->user()->vendor === null)
        <div class="rounded-2xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-700 dark:bg-zinc-900">
            <flux:heading size="md">{{ __('Become a Vendor') }}</flux:heading>
            <flux:text class="mt-2">{{ __('Create your store profile to start managing products and vendor orders.') }}</flux:text>
            <div class="mt-4">
                <flux:button variant="primary" :href="route('vendor.onboarding')" wire:navigate>
                    {{ __('Become a Vendor') }}
                </flux:button>
            </div>
        </div>
    @else
        <div class="rounded-2xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-700 dark:bg-zinc-900">
            <flux:heading size="md">{{ __('Vendor Profile') }}</flux:heading>
            <form wire:submit="updateVendorProfile" class="mt-4 space-y-4">
                <flux:input wire:model="store_name" :label="__('Store name')" required />
                <flux:textarea wire:model="description" :label="__('Store description')" rows="4" />
                <flux:button variant="primary" type="submit">{{ __('Save Vendor Profile') }}</flux:button>
            </form>

            <div class="mt-6 rounded-xl border border-dashed border-neutral-300 p-4 dark:border-neutral-700">
                <flux:heading size="sm">{{ __('Vendor Tools') }}</flux:heading>
                <div class="mt-3 flex flex-wrap gap-2">
                    <flux:button variant="primary" :href="route('vendor.products.create')" wire:navigate icon="plus">{{ __('Add Product') }}</flux:button>
                    <flux:button :href="route('vendor.products.index')" wire:navigate icon="squares-2x2">{{ __('Manage Products') }}</flux:button>
                    <flux:button :href="route('vendor.orders.index')" wire:navigate icon="truck">{{ __('Vendor Orders') }}</flux:button>
                </div>
            </div>
        </div>
    @endif
</section>
