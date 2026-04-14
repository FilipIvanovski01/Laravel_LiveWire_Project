<?php

namespace App\Domain\OrderManagement\Actions;

use App\Domain\OrderManagement\DTOs\CreateOrderDTO;
use App\Domain\OrderManagement\Enums\OrderStatus;
use App\Domain\OrderManagement\Models\Order;

class CreateOrderAction
{
    public function execute(CreateOrderDTO $data): Order
    {
        $order = Order::query()->create([
            'user_id' => $data->userId,
            'status' => OrderStatus::Paid,
            'payment_method' => $data->paymentMethod,
            'total_amount' => $data->totalAmount,
            'paid_at' => now(),
        ]);

        $order->items()->createMany(
            array_map(
                static fn ($item): array => [
                    'product_id' => $item->productId,
                    'vendor_id' => $item->vendorId,
                    'product_name' => $item->productName,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unitPrice,
                    'line_total' => $item->lineTotal,
                    'status' => OrderStatus::Paid,
                ],
                $data->items,
            ),
        );

        return $order->fresh();
    }
}
