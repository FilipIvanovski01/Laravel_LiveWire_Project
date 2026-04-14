<?php

namespace Database\Factories;

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
        return [
            'vendor_id' => Vendor::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->paragraphs(2, true),
            'price' => fake()->randomFloat(2, 5, 499),
            'stock_quantity' => fake()->numberBetween(0, 120),
            'image_url' => fake()->imageUrl(640, 480, 'technics', true, 'product'),
            'status' => fake()->randomElement(['active', 'active', 'active', 'draft', 'archived']),
        ];
    }
}
