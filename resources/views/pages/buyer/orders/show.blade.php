<?php

use App\Domain\OrderManagement\Models\Order;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Order Details')] class extends Component {
    #[Locked]
    public string $orderId;

    public function mount(Order $order): void
    {
        Gate::authorize('view', $order);
        $this->orderId = $order->id;
    }

    #[Computed]
    public function order(): Order
    {
        $order = Order::query()
            ->with(['items.vendor'])
            ->find($this->orderId);

        if ($order === null) {
            throw new ModelNotFoundException();
        }

        return $order;
    }
}; ?>

<section class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="lg">{{ __('Order Details') }}</flux:heading>
            <flux:text>{{ __('Order #:id', ['id' => $this->order->id]) }}</flux:text>
        </div>
        <flux:button :href="route('buyer.orders.index')" wire:navigate>{{ __('Back to Orders') }}</flux:button>
    </div>

    <div class="rounded-xl border border-neutral-200 bg-white p-5 shadow-sm dark:border-neutral-700 dark:bg-zinc-900">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <flux:text>{{ __('Status: :status', ['status' => ucfirst($this->order->status->value ?? $this->order->status)]) }}</flux:text>
            <flux:text class="font-semibold text-[#007BFF]">
                ${{ number_format((float) $this->order->total_amount, 2) }}
            </flux:text>
        </div>

        <div class="mt-4 space-y-3">
            @foreach ($this->order->items as $item)
                <div class="rounded-lg border border-neutral-200 p-3 dark:border-neutral-700">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <flux:text class="font-medium">{{ $item->product_name }}</flux:text>
                        <flux:text>{{ __('Qty: :qty', ['qty' => $item->quantity]) }}</flux:text>
                    </div>
                    <flux:text class="text-sm">{{ __('Vendor: :vendor', ['vendor' => $item->vendor->store_name]) }}</flux:text>
                    <flux:text class="text-sm text-zinc-500">{{ __('Line Status: :status', ['status' => ucfirst($item->status->value ?? $item->status)]) }}</flux:text>
                </div>
            @endforeach
        </div>
    </div>
</section>
