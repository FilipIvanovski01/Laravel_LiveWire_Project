<?php

namespace Database\Factories;

use App\Domain\ProductCatalog\Enums\ProductStatus;
use App\Domain\ProductCatalog\Models\Product;
use App\Domain\ProductCatalog\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $products = [
            ['name' => 'Wireless Earbuds Pro', 'desc' => 'Noise-isolating earbuds with charging case and all-day battery life.'],
            ['name' => 'Minimal Desk Lamp', 'desc' => 'Adjustable warm/cool lighting designed for focused work sessions.'],
            ['name' => 'Ergonomic Office Chair', 'desc' => 'Lumbar-support chair built for long workdays and comfort.'],
            ['name' => 'Stainless Water Bottle', 'desc' => 'Insulated bottle that keeps drinks cold for 24 hours.'],
            ['name' => 'Portable Blender', 'desc' => 'Compact rechargeable blender perfect for smoothies on the go.'],
            ['name' => 'Organic Cotton T-Shirt', 'desc' => 'Soft breathable tee made with premium organic cotton.'],
            ['name' => 'Running Shoes Lite', 'desc' => 'Lightweight trainers with cushioned soles for daily runs.'],
            ['name' => 'Smart Fitness Watch', 'desc' => 'Track heart rate, sleep, and workouts with weekly insights.'],
            ['name' => 'Ceramic Dinner Set', 'desc' => 'Durable 12-piece ceramic set for modern dining tables.'],
            ['name' => 'Air Purifier Compact', 'desc' => 'HEPA filtration for cleaner air in bedrooms and offices.'],
        ];
        $selected = fake()->randomElement($products);

        return [
            'vendor_id' => Vendor::factory(),
            'name' => $selected['name'],
            'description' => $selected['desc'].' '.fake()->sentence(),
            'price' => fake()->randomFloat(2, 12, 349),
            'stock_quantity' => fake()->numberBetween(3, 90),
            'image_url' => 'https://picsum.photos/seed/'.fake()->unique()->slug().'/640/480',
            'status' => fake()->randomElement([ProductStatus::Active, ProductStatus::Active, ProductStatus::Active, ProductStatus::Draft, ProductStatus::Archived]),
        ];
    }
}
