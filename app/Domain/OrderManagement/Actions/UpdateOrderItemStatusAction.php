<?php

namespace App\Domain\OrderManagement\Actions;

use App\Domain\OrderManagement\Enums\OrderStatus;
use App\Domain\OrderManagement\Models\OrderItem;
use App\Domain\OrderManagement\Services\OrderStatusSyncService;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class UpdateOrderItemStatusAction
{
    public function __construct(
        private readonly OrderStatusSyncService $orderStatusSyncService,
    ) {
    }

    public function execute(User $user, OrderItem $orderItem, OrderStatus $targetStatus): OrderItem
    {
        Gate::forUser($user)->authorize('update', $orderItem);

        $currentStatus = $orderItem->status instanceof OrderStatus
            ? $orderItem->status
            : OrderStatus::from($orderItem->status);

        if (! $currentStatus->canTransitionTo($targetStatus)) {
            throw ValidationException::withMessages([
                'status' => __('Invalid status transition from :from to :to.', [
                    'from' => $currentStatus->value,
                    'to' => $targetStatus->value,
                ]),
            ]);
        }

        return DB::transaction(function () use ($orderItem, $targetStatus): OrderItem {
            $orderItem->update([
                'status' => $targetStatus,
            ]);

            $this->orderStatusSyncService->syncFromItems($orderItem->order()->firstOrFail());

            return $orderItem->fresh();
        });
    }
}
