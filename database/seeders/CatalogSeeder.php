<?php

namespace Database\Seeders;

use App\Domain\ProductCatalog\Enums\ProductStatus;
use App\Domain\ProductCatalog\Models\Product;
use App\Domain\ProductCatalog\Models\Vendor;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vendors = Vendor::query()->get();

        foreach ($vendors as $vendor) {
            Product::factory()
                ->count(8)
                ->for($vendor)
                ->state([
                    'status' => ProductStatus::Active,
                ])
                ->create();

            Product::factory()
                ->count(4)
                ->for($vendor)
                ->create();
        }
    }
}
