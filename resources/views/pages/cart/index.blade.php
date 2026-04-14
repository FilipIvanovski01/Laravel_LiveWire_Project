<x-layouts::app :title="__('Cart')">
    <div class="space-y-2">
        <flux:heading size="lg">{{ __('Cart') }}</flux:heading>
        <flux:text>{{ __('Buyer cart page is protected with authentication middleware.') }}</flux:text>
    </div>
</x-layouts::app>
