<?php

namespace Tests\Feature\Vendor;

use App\Domain\ProductCatalog\Models\Product;
use App\Domain\ProductCatalog\Models\Vendor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorProductSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_vendor_cannot_access_vendor_product_create_page(): void
    {
        /** @var User $buyer */
        $buyer = User::factory()->create();

        $this->actingAs($buyer)
            ->get(route('vendor.products.create'))
            ->assertRedirect(route('vendor.onboarding'));
    }

    public function test_non_vendor_cannot_create_product_record(): void
    {
        /** @var User $buyer */
        $buyer = User::factory()->create();
        $initialCount = Product::query()->count();

        $this->actingAs($buyer)
            ->get(route('vendor.products.create'))
            ->assertRedirect(route('vendor.onboarding'));

        $this->assertSame($initialCount, Product::query()->count());
    }

    public function test_vendor_cannot_access_another_vendors_product_edit_page(): void
    {
        $owner = User::factory()->create();
        $ownerVendor = Vendor::factory()->for($owner)->create();
        $product = Product::factory()->for($ownerVendor)->create();

        /** @var User $otherVendorUser */
        $otherVendorUser = User::factory()->create();
        Vendor::factory()->for($otherVendorUser)->create();

        $this->actingAs($otherVendorUser)
            ->get(route('vendor.products.edit', $product))
            ->assertForbidden();
    }
}
