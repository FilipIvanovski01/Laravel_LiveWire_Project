<?php

use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('My Orders')] class extends Component {
}; ?>

<section class="space-y-2">
    <flux:heading size="lg">{{ __('My Orders') }}</flux:heading>
    <flux:text>{{ __('Buyer order history page is protected with authentication middleware.') }}</flux:text>
</section>
