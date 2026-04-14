<?php

namespace Database\Factories;

use App\Domain\ProductCatalog\Models\Vendor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Vendor>
 */
class VendorFactory extends Factory
{
    protected $model = Vendor::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $storePrefixes = ['Urban', 'North', 'Golden', 'Prime', 'Bright', 'Daily', 'Modern', 'Coastal', 'Summit', 'Craft'];
        $storeNouns = ['Market', 'Goods', 'Boutique', 'Supply', 'Studio', 'Outlet', 'Essentials', 'Corner', 'Hub', 'Collective'];
        $storeName = fake()->unique()->randomElement($storePrefixes).' '.fake()->randomElement($storeNouns);

        return [
            'user_id' => User::factory(),
            'store_name' => $storeName,
            'slug' => Str::slug($storeName).'-'.fake()->unique()->numberBetween(100, 999),
            'description' => fake()->randomElement([
                'Curated everyday products with fast shipping and dependable quality.',
                'Independent multi-category store focused on quality, value, and service.',
                'Carefully selected lifestyle items for home, work, and personal use.',
            ]),
            'is_active' => true,
        ];
    }
}
