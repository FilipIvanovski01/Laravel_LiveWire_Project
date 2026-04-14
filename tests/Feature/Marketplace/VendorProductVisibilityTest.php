<?php

namespace Tests\Feature\Marketplace;

use App\Domain\ProductCatalog\Models\Product;
use App\Domain\ProductCatalog\Models\Vendor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorProductVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_active_vendor_product_is_visible_on_marketplace(): void
    {
        $vendorUser = User::factory()->create();
        $vendor = Vendor::factory()->for($vendorUser)->create(['is_active' => true]);

        $product = Product::factory()->for($vendor)->create([
            'name' => 'Fresh Marketplace Listing',
            'status' => 'active',
            'stock_quantity' => 8,
        ]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee($product->name);
    }

    public function test_draft_product_is_not_visible_on_marketplace_until_published(): void
    {
        $vendorUser = User::factory()->create();
        $vendor = Vendor::factory()->for($vendorUser)->create(['is_active' => true]);

        $product = Product::factory()->for($vendor)->create([
            'name' => 'Draft Listing',
            'status' => 'draft',
        ]);

        $response = $this->get(route('home'));
        $response->assertOk();
        $response->assertDontSee($product->name);

        $product->update(['status' => 'active']);

        $publishedResponse = $this->get(route('home'));
        $publishedResponse->assertOk();
        $publishedResponse->assertSee($product->name);
    }
}
