<?php

namespace Tests\Feature\Checkout;

use App\Domain\Cart\Models\Cart;
use App\Domain\Cart\Models\CartItem;
use App\Domain\ProductCatalog\Models\Product;
use App\Domain\ProductCatalog\Models\Vendor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CheckoutEdgeCasesTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_fails_if_one_item_in_multi_vendor_cart_becomes_invalid(): void
    {
        $buyer = User::factory()->create();
        $vendorA = Vendor::factory()->create(['is_active' => true]);
        $vendorB = Vendor::factory()->create(['is_active' => true]);

        $validProduct = Product::factory()->for($vendorA)->create([
            'status' => 'active',
            'price' => 120.00,
            'stock_quantity' => 10,
        ]);

        $invalidProduct = Product::factory()->for($vendorB)->create([
            'status' => 'active',
            'price' => 80.00,
            'stock_quantity' => 5,
        ]);

        $cart = Cart::factory()->for($buyer)->create();
        CartItem::factory()->for($cart)->for($validProduct)->create(['quantity' => 2, 'unit_price' => 120.00]);
        CartItem::factory()->for($cart)->for($invalidProduct)->create(['quantity' => 1, 'unit_price' => 80.00]);

        $invalidProduct->update(['status' => 'archived']);

        Livewire::actingAs($buyer)
            ->test('pages::checkout.index')
            ->set('payment_method', 'wallet')
            ->call('placeOrder')
            ->assertHasErrors(['checkout']);

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
        $this->assertDatabaseCount('cart_items', 2);
        $this->assertSame(10, $validProduct->fresh()->stock_quantity);
    }

    public function test_checkout_rejects_when_stock_drops_to_zero_before_payment(): void
    {
        $buyer = User::factory()->create();
        $vendor = Vendor::factory()->create(['is_active' => true]);
        $product = Product::factory()->for($vendor)->create([
            'status' => 'active',
            'price' => 90.00,
            'stock_quantity' => 2,
        ]);

        $cart = Cart::factory()->for($buyer)->create();
        CartItem::factory()->for($cart)->for($product)->create(['quantity' => 1, 'unit_price' => 90.00]);

        $product->update(['stock_quantity' => 0]);

        Livewire::actingAs($buyer)
            ->test('pages::checkout.index')
            ->set('payment_method', 'credit_card')
            ->call('placeOrder')
            ->assertHasErrors(['checkout']);

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);
        $this->assertSame(0, $product->fresh()->stock_quantity);
    }
}
