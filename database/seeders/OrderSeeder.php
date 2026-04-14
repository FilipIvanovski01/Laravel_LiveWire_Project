<?php

namespace Database\Seeders;

use App\Domain\OrderManagement\Models\Order;
use App\Domain\OrderManagement\Models\OrderItem;
use App\Domain\ProductCatalog\Enums\ProductStatus;
use App\Domain\ProductCatalog\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::query()
            ->where('status', ProductStatus::Active)
            ->where('stock_quantity', '>', 0)
            ->get();

        if ($products->isEmpty()) {
            return;
        }

        $users = User::query()
            ->inRandomOrder()
            ->limit(10)
            ->get();

        foreach ($users as $user) {
            $order = Order::factory()->for($user)->create();
            $selectedProducts = $products->random(min(fake()->numberBetween(1, 3), $products->count()));
            $orderTotal = 0;

            foreach ($selectedProducts as $product) {
                $quantity = fake()->numberBetween(1, min(3, $product->stock_quantity));
                $lineTotal = round((float) $product->price * $quantity, 2);
                $orderTotal += $lineTotal;

                OrderItem::factory()->for($order)->for($product)->for($product->vendor)->create([
                    'product_name' => $product->name,
                    'quantity' => $quantity,
                    'unit_price' => $product->price,
                    'line_total' => $lineTotal,
                ]);
            }

            $order->update([
                'total_amount' => round($orderTotal, 2),
            ]);
        }
    }
}
