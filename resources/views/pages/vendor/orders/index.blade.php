<?php

use App\Domain\OrderManagement\Actions\UpdateOrderItemStatusAction;
use App\Domain\OrderManagement\Enums\OrderStatus;
use App\Domain\OrderManagement\Models\OrderItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Vendor Orders')] class extends Component {
    use WithPagination;

    public function markAsShipped(string $orderItemId, UpdateOrderItemStatusAction $updateOrderItemStatusAction): void
    {
        $orderItem = OrderItem::query()
            ->where('vendor_id', Auth::user()->vendor->id)
            ->findOrFail($orderItemId);

        try {
            $updateOrderItemStatusAction->execute(
                Auth::user(),
                $orderItem,
                OrderStatus::Shipped,
            );
        } catch (ValidationException $exception) {
            $this->addError('status', $exception->getMessage());

            return;
        }

        $this->resetErrorBag('status');
        session()->flash('status', __('Order line marked as shipped.'));
    }

    #[Computed]
    public function orderItems(): LengthAwarePaginator
    {
        return OrderItem::query()
            ->with(['order.user', 'product'])
            ->where('vendor_id', Auth::user()->vendor->id)
            ->latest()
            ->paginate(15);
    }
}; ?>

<section class="space-y-6">
    <div>
        <flux:heading size="lg">{{ __('Vendor Orders') }}</flux:heading>
        <flux:text>{{ __('Track all order lines for products sold by your store.') }}</flux:text>
    </div>

    @if (session()->has('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    @error('status')
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-red-700">
            {{ $message }}
        </div>
    @enderror

    @if ($this->orderItems->isEmpty())
        <div class="rounded-xl border border-dashed border-neutral-300 p-8 text-center dark:border-neutral-700">
            <flux:text>{{ __('No vendor orders found yet.') }}</flux:text>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($this->orderItems as $orderItem)
                <article wire:key="vendor-order-item-{{ $orderItem->id }}" class="rounded-xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-700 dark:bg-zinc-900">
                    <div class="grid gap-3 md:grid-cols-5 md:items-center">
                        <div class="md:col-span-2">
                            <flux:heading size="sm">{{ $orderItem->product_name }}</flux:heading>
                            <flux:text class="text-sm">{{ __('Order #:id', ['id' => $orderItem->order->id]) }}</flux:text>
                            <flux:text class="text-sm">{{ __('Status: :status', ['status' => ucfirst($orderItem->order->status->value ?? $orderItem->order->status)]) }}</flux:text>
                            <flux:text class="text-sm">{{ __('Line: :status', ['status' => ucfirst($orderItem->status->value ?? $orderItem->status)]) }}</flux:text>
                            <flux:text class="text-xs text-zinc-500">{{ $orderItem->created_at?->format('M d, Y H:i') }}</flux:text>
                        </div>
                        <flux:text>{{ __('Buyer: :buyer', ['buyer' => $orderItem->order->user->name]) }}</flux:text>
                        <flux:text>{{ __('Qty: :qty', ['qty' => $orderItem->quantity]) }}</flux:text>
                        <div class="flex items-center justify-between gap-2 md:block">
                            <flux:text class="font-semibold text-[#007BFF]">
                            ${{ number_format((float) $orderItem->line_total, 2) }}
                            </flux:text>
                            @if (($orderItem->status->value ?? $orderItem->status) === 'paid')
                                <flux:button
                                    size="sm"
                                    variant="primary"
                                    wire:click="markAsShipped('{{ $orderItem->id }}')"
                                >
                                    {{ __('Mark shipped') }}
                                </flux:button>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        {{ $this->orderItems->links() }}
    @endif
</section>
