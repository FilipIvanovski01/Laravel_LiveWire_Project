<?php

namespace Tests\Unit\ProductCatalog;

use App\Domain\ProductCatalog\Models\Product;
use App\Domain\ProductCatalog\Models\Vendor;
use App\Domain\ProductCatalog\Policies\ProductPolicy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_vendor_cannot_delete_another_vendors_product(): void
    {
        $ownerUser = User::factory()->create();
        $ownerVendor = Vendor::factory()->for($ownerUser)->create();
        $product = Product::factory()->for($ownerVendor)->create();

        $otherVendorUser = User::factory()->create();
        Vendor::factory()->for($otherVendorUser)->create();

        $policy = new ProductPolicy();

        $this->assertFalse($policy->delete($otherVendorUser, $product));
    }

    public function test_non_vendor_cannot_delete_vendor_product(): void
    {
        $ownerUser = User::factory()->create();
        $ownerVendor = Vendor::factory()->for($ownerUser)->create();
        $product = Product::factory()->for($ownerVendor)->create();

        $buyer = User::factory()->create();
        $policy = new ProductPolicy();

        $this->assertFalse($policy->delete($buyer, $product));
    }
}
