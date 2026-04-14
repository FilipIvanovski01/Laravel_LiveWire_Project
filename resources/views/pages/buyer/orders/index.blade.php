<?php

use App\Domain\OrderManagement\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('My Orders')] class extends Component {
    use WithPagination;

    #[Computed]
    public function orders(): LengthAwarePaginator
    {
        return Order::query()
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10);
    }
}; ?>

<section class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="lg">{{ __('My Orders') }}</flux:heading>
            <flux:text>{{ __('Track your recent purchases and payment status.') }}</flux:text>
        </div>
        <flux:button :href="route('cart.index')" wire:navigate icon="shopping-cart">{{ __('Back to Cart') }}</flux:button>
    </div>

    @if ($this->orders->isEmpty())
        <div class="rounded-xl border border-dashed border-neutral-300 p-8 text-center dark:border-neutral-700">
            <flux:text>{{ __('You have no orders yet.') }}</flux:text>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($this->orders as $order)
                <article wire:key="order-{{ $order->id }}" class="rounded-xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-700 dark:bg-zinc-900">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <flux:heading size="sm">{{ __('Order #:id', ['id' => $order->id]) }}</flux:heading>
                            <flux:text class="text-sm">{{ __('Status: :status', ['status' => ucfirst($order->status->value ?? $order->status)]) }}</flux:text>
                        </div>
                        <div class="flex items-center gap-3">
                            <flux:text class="font-semibold text-[#007BFF]">
                                ${{ number_format((float) $order->total_amount, 2) }}
                            </flux:text>
                            <flux:button size="sm" :href="route('buyer.orders.show', $order)" wire:navigate>
                                {{ __('Details') }}
                            </flux:button>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        {{ $this->orders->links() }}
    @endif
</section>
