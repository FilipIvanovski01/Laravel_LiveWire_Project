<?php

namespace Tests\Feature\Auth;

use App\Domain\ProductCatalog\Models\Vendor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProtectedRouteAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_protected_commerce_routes(): void
    {
        $protectedRoutes = [
            route('profile.edit'),
            route('cart.index'),
            route('checkout.index'),
            route('buyer.orders.index'),
            route('vendor.orders.index'),
            route('vendor.products.index'),
        ];

        foreach ($protectedRoutes as $uri) {
            $response = $this->get($uri);
            $response->assertRedirect(route('login'));
        }
    }

    public function test_authenticated_vendor_can_access_buyer_cart_and_checkout_routes(): void
    {
        /** @var User $vendorUser */
        $vendorUser = User::factory()->create();
        Vendor::factory()->for($vendorUser)->create();

        $this->actingAs($vendorUser)
            ->get(route('cart.index'))
            ->assertOk();

        $this->actingAs($vendorUser)
            ->get(route('checkout.index'))
            ->assertOk();
    }
}
