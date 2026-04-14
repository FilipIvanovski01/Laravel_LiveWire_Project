<?php

namespace Database\Factories;

use App\Domain\OrderManagement\Enums\OrderStatus;
use App\Domain\OrderManagement\Models\Order;
use App\Domain\OrderManagement\Models\OrderItem;
use App\Domain\ProductCatalog\Models\Product;
use App\Domain\ProductCatalog\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $unitPrice = fake()->randomFloat(2, 5, 499);
        $quantity = fake()->numberBetween(1, 5);

        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'vendor_id' => Vendor::factory(),
            'product_name' => fake()->words(3, true),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'line_total' => round($unitPrice * $quantity, 2),
            'status' => fake()->randomElement([
                OrderStatus::Paid,
                OrderStatus::Shipped,
                OrderStatus::Delivered,
            ]),
        ];
    }
}
