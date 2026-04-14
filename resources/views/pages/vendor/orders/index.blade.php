<?php

use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Vendor Orders')] class extends Component {
}; ?>

<section class="space-y-2">
    <flux:heading size="lg">{{ __('Vendor Orders') }}</flux:heading>
    <flux:text>{{ __('Vendor-only order page is protected and ready for implementation.') }}</flux:text>
</section>
