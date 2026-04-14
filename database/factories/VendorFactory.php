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
        $storeName = fake()->unique()->company();

        return [
            'user_id' => User::factory(),
            'store_name' => $storeName,
            'slug' => Str::slug($storeName).'-'.fake()->unique()->numberBetween(100, 999),
            'description' => fake()->paragraph(),
            'is_active' => true,
        ];
    }
}
