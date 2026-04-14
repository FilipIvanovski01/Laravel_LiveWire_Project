<?php

namespace Tests\Feature\Marketplace;

use App\Domain\ProductCatalog\Models\Product;
use App\Domain\ProductCatalog\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_marketplace_lists_only_visible_products(): void
    {
        $activeVendor = Vendor::factory()->create(['is_active' => true]);
        $inactiveVendor = Vendor::factory()->create(['is_active' => false]);

        $visibleProduct = Product::factory()->for($activeVendor)->create([
            'name' => 'Visible Product',
            'status' => 'active',
        ]);

        Product::factory()->for($inactiveVendor)->create([
            'name' => 'Inactive Vendor Product',
            'status' => 'active',
        ]);

        Product::factory()->for($activeVendor)->create([
            'name' => 'Draft Product',
            'status' => 'draft',
        ]);

        Product::factory()->for($activeVendor)->create([
            'name' => 'Deleted Product',
            'status' => 'active',
        ])->delete();

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee($visibleProduct->name);
        $response->assertDontSee('Inactive Vendor Product');
        $response->assertDontSee('Draft Product');
        $response->assertDontSee('Deleted Product');
    }

    public function test_vendor_filter_works(): void
    {
        $vendorA = Vendor::factory()->create(['is_active' => true]);
        $vendorB = Vendor::factory()->create(['is_active' => true]);

        Product::factory()->for($vendorA)->create([
            'name' => 'Vendor A Product',
            'status' => 'active',
        ]);

        Product::factory()->for($vendorB)->create([
            'name' => 'Vendor B Product',
            'status' => 'active',
        ]);

        $response = $this->get(route('home', ['vendor_id' => $vendorA->id]));

        $response->assertOk();
        $response->assertSee('Vendor A Product');
        $response->assertDontSee('Vendor B Product');
    }

    public function test_price_filtering_works(): void
    {
        $vendor = Vendor::factory()->create(['is_active' => true]);

        Product::factory()->for($vendor)->create([
            'name' => 'Budget Item',
            'price' => 10.00,
            'status' => 'active',
        ]);

        Product::factory()->for($vendor)->create([
            'name' => 'Premium Item',
            'price' => 150.00,
            'status' => 'active',
        ]);

        $response = $this->get(route('home', ['min_price' => 50, 'max_price' => 200]));

        $response->assertOk();
        $response->assertSee('Premium Item');
        $response->assertDontSee('Budget Item');
    }

    public function test_keyword_search_works(): void
    {
        $vendor = Vendor::factory()->create(['is_active' => true]);

        Product::factory()->for($vendor)->create([
            'name' => 'Apple Watch',
            'status' => 'active',
        ]);

        Product::factory()->for($vendor)->create([
            'name' => 'Gaming Laptop',
            'status' => 'active',
        ]);

        $response = $this->get(route('home', ['q' => 'apple']));

        $response->assertOk();
        $response->assertSee('Apple Watch');
        $response->assertDontSee('Gaming Laptop');
    }

    public function test_marketplace_pagination_works(): void
    {
        $vendor = Vendor::factory()->create(['is_active' => true]);

        for ($index = 1; $index <= 13; $index++) {
            Product::factory()->for($vendor)->create([
                'name' => 'P-'.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
                'status' => 'active',
                'created_at' => now()->addSeconds($index),
            ]);
        }

        $firstPage = $this->get(route('home'));
        $firstPage->assertOk();
        $firstPage->assertSee('P-13');
        $firstPage->assertDontSee('P-01');

        $secondPage = $this->get(route('home', ['page' => 2]));
        $secondPage->assertOk();
        $secondPage->assertSee('P-01');
    }

    public function test_hidden_or_inactive_products_cannot_be_opened_from_public_detail_page(): void
    {
        $inactiveVendor = Vendor::factory()->create(['is_active' => false]);
        $hiddenProduct = Product::factory()->for($inactiveVendor)->create([
            'status' => 'active',
        ]);

        $response = $this->get(route('market.products.show', $hiddenProduct));

        $response->assertNotFound();
    }
}
