<?php

namespace App\Domain\OrderManagement\Services;

use App\Domain\OrderManagement\Enums\OrderStatus;
use App\Domain\OrderManagement\Models\Order;

class OrderStatusSyncService
{
    public function syncFromItems(Order $order): Order
    {
        $statuses = $order->items()
            ->get(['status'])
            ->map(static fn ($item): string => ($item->status instanceof OrderStatus) ? $item->status->value : (string) $item->status)
            ->all();

        if ($statuses === []) {
            return $order;
        }

        $uniqueStatuses = array_unique($statuses);

        if (count($uniqueStatuses) === 1) {
            $singleStatus = OrderStatus::from($uniqueStatuses[0]);

            if ($order->status !== $singleStatus) {
                $order->update(['status' => $singleStatus]);
            }

            return $order->fresh();
        }

        if (! in_array(OrderStatus::Paid->value, $uniqueStatuses, true) && in_array(OrderStatus::Shipped->value, $uniqueStatuses, true)) {
            if ($order->status !== OrderStatus::Shipped) {
                $order->update(['status' => OrderStatus::Shipped]);
            }

            return $order->fresh();
        }

        if ($order->status !== OrderStatus::Paid) {
            $order->update(['status' => OrderStatus::Paid]);
        }

        return $order->fresh();
    }
}
