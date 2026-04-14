<?php

namespace Database\Seeders;

use App\Domain\Cart\Models\Cart;
use App\Domain\Cart\Models\CartItem;
use App\Domain\ProductCatalog\Enums\ProductStatus;
use App\Domain\ProductCatalog\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class CartSeeder extends Seeder
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

        $buyers = User::query()->get();
        
        foreach ($buyers as $buyer) {
            $cart = Cart::factory()->for($buyer)->create();

            $prefillCount = min(fake()->numberBetween(1, 3), $products->count());

            $selectedProducts = $products->random($prefillCount);

            foreach ($selectedProducts as $product) {
                CartItem::factory()->for($cart)->for($product)->create([
                    'quantity' => fake()->numberBetween(1, min(3, $product->stock_quantity)),
                    'unit_price' => $product->price,
                ]);
            }
        }
    }
}
