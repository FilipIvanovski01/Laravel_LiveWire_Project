<?php

use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Checkout')] class extends Component {
}; ?>

<section class="space-y-2">
    <flux:heading size="lg">{{ __('Checkout') }}</flux:heading>
    <flux:text>{{ __('Checkout page is protected with authentication middleware.') }}</flux:text>
</section>
