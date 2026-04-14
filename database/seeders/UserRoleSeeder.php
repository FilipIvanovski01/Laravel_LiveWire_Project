<?php

namespace Database\Seeders;

use App\Domain\ProductCatalog\Models\Vendor;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $storeNames = collect([
            'Harbor Home',
            'Northline Outfitters',
            'Daily Utility',
            'Blue Peak Market',
            'Urban Nest Goods',
            'Summit Tech Shop',
            'Green Basket Co',
            'Luna Living Store',
            'Craft & Carry',
            'Core Essentials Hub',
            'Modern Pantry',
            'Everfield Supply',
        ]);

        $primaryUser = User::factory()->create([
            'name' => 'Primary User',
            'email' => 'primary@example.com',
        ]);

        User::factory()->count(19)->create();

        $vendorUsers = User::query()
            ->inRandomOrder()
            ->limit(10)
            ->get();

        foreach ($vendorUsers as $user) {
            $storeName = $storeNames->pop() ?? fake()->unique()->company();

            Vendor::factory()->for($user)->create([
                'store_name' => $storeName,
                'slug' => Str::slug($storeName).'-'.Str::lower(Str::random(6)),
            ]);
        }

        if ($primaryUser->vendor === null) {
            $storeName = 'Primary Store';

            Vendor::factory()->for($primaryUser)->create([
                'store_name' => $storeName,
                'slug' => Str::slug($storeName).'-'.Str::lower(Str::random(6)),
            ]);
        }
    }
}
