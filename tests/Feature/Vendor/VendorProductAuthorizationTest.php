<?php

namespace Tests\Feature\Vendor;

use App\Domain\ProductCatalog\Models\Product;
use App\Domain\ProductCatalog\Models\Vendor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorProductAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_vendor_cannot_open_another_vendors_product_edit_page(): void
    {
        $ownerUser = User::factory()->create();
        $ownerVendor = Vendor::factory()->for($ownerUser)->create();
        $product = Product::factory()->for($ownerVendor)->create();

        /** @var User $otherVendorUser */
        $otherVendorUser = User::factory()->create();
        Vendor::factory()->for($otherVendorUser)->create();

        $this->actingAs($otherVendorUser)
            ->get(route('vendor.products.edit', $product))
            ->assertForbidden();
    }
}
