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
            <flux:heading size="lg" class="text-[#212529]">{{ __('Order Details') }}</flux:heading>
            <flux:text class="text-[#6C757D]">{{ __('Order #:id', ['id' => $this->order->id]) }}</flux:text>
        </div>
        <flux:button class="border border-[#E5E7EB] bg-white text-[#212529] hover:bg-[#F8F9FA]" :href="route('buyer.orders.index')" wire:navigate>{{ __('Back to Orders') }}</flux:button>
    </div>

    <div class="rounded-xl border border-[#E5E7EB] bg-white p-5 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <flux:badge color="blue">{{ ucfirst($this->order->status->value ?? $this->order->status) }}</flux:badge>
            <flux:text class="font-semibold text-[#007BFF]">
                ${{ number_format((float) $this->order->total_amount, 2) }}
            </flux:text>
        </div>

        <div class="mt-4 space-y-3">
            @foreach ($this->order->items as $item)
                <div class="rounded-lg border border-[#E5E7EB] bg-[#F8F9FA] p-3">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <flux:text class="font-medium text-[#212529]">{{ $item->product_name }}</flux:text>
                        <flux:text>{{ __('Qty: :qty', ['qty' => $item->quantity]) }}</flux:text>
                    </div>
                    <flux:text class="text-sm text-[#6C757D]">{{ __('Vendor: :vendor', ['vendor' => $item->vendor->store_name]) }}</flux:text>
                    <flux:text class="text-sm text-[#6C757D]">{{ __('Line Status: :status', ['status' => ucfirst($item->status->value ?? $item->status)]) }}</flux:text>
                </div>
            @endforeach
        </div>
    </div>
</section>
