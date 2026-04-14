<?php

use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Vendor Products')] class extends Component {
}; ?>

<section class="space-y-2">
    <flux:heading size="lg">{{ __('Vendor Products') }}</flux:heading>
    <flux:text>{{ __('Vendor-only product management area is protected and ready for implementation.') }}</flux:text>
</section>
