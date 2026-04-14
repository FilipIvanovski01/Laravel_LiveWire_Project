<?php

namespace Tests\Feature\Vendor;

use App\Domain\OrderManagement\Models\Order;
use App\Domain\OrderManagement\Models\OrderItem;
use App\Domain\ProductCatalog\Models\Product;
use App\Domain\ProductCatalog\Models\Vendor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class VendorCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_vendor_can_create_product_from_vendor_area(): void
    {
        $vendorUser = User::factory()->create();
        $vendor = Vendor::factory()->for($vendorUser)->create();

        Livewire::actingAs($vendorUser)
            ->test('pages::vendor.products.create')
            ->set('name', 'Vendor Product')
            ->set('description', 'A valid description for this product.')
            ->set('price', '19.99')
            ->set('stock_quantity', '12')
            ->set('image_url', 'https://example.com/product.jpg')
            ->set('status', 'active')
            ->call('save');

        $this->assertDatabaseHas('products', [
            'vendor_id' => $vendor->id,
            'name' => 'Vendor Product',
            'status' => 'active',
        ]);
    }

    public function test_vendor_products_page_lists_only_authenticated_vendor_products(): void
    {
        /** @var User $vendorAUser */
        $vendorAUser = User::factory()->create();
        $vendorA = Vendor::factory()->for($vendorAUser)->create(['store_name' => 'Vendor A']);

        /** @var User $vendorBUser */
        $vendorBUser = User::factory()->create();
        $vendorB = Vendor::factory()->for($vendorBUser)->create(['store_name' => 'Vendor B']);

        Product::factory()->for($vendorA)->create(['name' => 'A Product', 'status' => 'active']);
        Product::factory()->for($vendorB)->create(['name' => 'B Product', 'status' => 'active']);

        $response = $this->actingAs($vendorAUser)->get(route('vendor.products.index'));

        $response->assertOk();
        $response->assertSee('A Product');
        $response->assertDontSee('B Product');
    }

    public function test_vendor_orders_page_lists_only_order_items_for_current_vendor(): void
    {
        /** @var User $buyer */
        $buyer = User::factory()->create();
        $order = Order::factory()->for($buyer)->create();

        /** @var User $vendorAUser */
        $vendorAUser = User::factory()->create();
        $vendorA = Vendor::factory()->for($vendorAUser)->create();

        /** @var User $vendorBUser */
        $vendorBUser = User::factory()->create();
        $vendorB = Vendor::factory()->for($vendorBUser)->create();

        OrderItem::factory()->for($order)->for($vendorA)->create(['product_name' => 'Vendor A Item']);
        OrderItem::factory()->for($order)->for($vendorB)->create(['product_name' => 'Vendor B Item']);

        $response = $this->actingAs($vendorAUser)->get(route('vendor.orders.index'));

        $response->assertOk();
        $response->assertSee('Vendor A Item');
        $response->assertDontSee('Vendor B Item');
    }

    public function test_market_product_detail_links_to_vendor_storefront_and_no_back_link(): void
    {
        $vendor = Vendor::factory()->create(['store_name' => 'Public Store', 'slug' => 'public-store', 'is_active' => true]);
        $product = Product::factory()->for($vendor)->create(['status' => 'active']);

        $response = $this->get(route('market.products.show', $product));

        $response->assertOk();
        $response->assertSee(route('vendors.show', ['vendor' => $vendor->slug]), false);
        $response->assertDontSee('Back to marketplace');
    }

    public function test_vendor_public_profile_shows_active_listings(): void
    {
        $vendor = Vendor::factory()->create(['slug' => 'vendor-profile', 'is_active' => true]);

        Product::factory()->for($vendor)->create(['name' => 'Visible Listing', 'status' => 'active']);
        Product::factory()->for($vendor)->create(['name' => 'Hidden Listing', 'status' => 'draft']);

        $response = $this->get(route('vendors.show', ['vendor' => $vendor->slug]));

        $response->assertOk();
        $response->assertSee('Visible Listing');
        $response->assertDontSee('Hidden Listing');
    }
}
