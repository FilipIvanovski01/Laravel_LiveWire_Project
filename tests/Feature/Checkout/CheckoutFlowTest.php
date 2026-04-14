<?php

namespace Tests\Feature\Checkout;

use App\Domain\Cart\Models\Cart;
use App\Domain\Cart\Models\CartItem;
use App\Domain\OrderManagement\Models\Order;
use App\Domain\ProductCatalog\Models\Product;
use App\Domain\ProductCatalog\Models\Vendor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CheckoutFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_succeeds_when_total_is_less_or_equal_to_999(): void
    {
        $buyer = User::factory()->create();
        $vendor = Vendor::factory()->create(['is_active' => true]);
        $product = Product::factory()->for($vendor)->create([
            'status' => 'active',
            'price' => 100.00,
            'stock_quantity' => 10,
        ]);

        $cart = Cart::factory()->for($buyer)->create();
        CartItem::factory()->for($cart)->for($product)->create([
            'quantity' => 2,
            'unit_price' => 100.00,
        ]);

        Livewire::actingAs($buyer)
            ->test('pages::checkout.index')
            ->set('payment_method', 'wallet')
            ->call('placeOrder')
            ->assertHasNoErrors();

        $order = Order::query()->where('user_id', $buyer->id)->latest()->first();

        $this->assertNotNull($order);
        $this->assertSame('paid', $order->status->value);
        $this->assertEquals(200.00, (float) $order->total_amount);
        $this->assertDatabaseCount('cart_items', 0);
        $this->assertSame(8, $product->fresh()->stock_quantity);
    }

    public function test_checkout_fails_when_total_is_greater_than_999_and_cart_remains(): void
    {
        $buyer = User::factory()->create();
        $vendor = Vendor::factory()->create(['is_active' => true]);
        $product = Product::factory()->for($vendor)->create([
            'status' => 'active',
            'price' => 600.00,
            'stock_quantity' => 10,
        ]);

        $cart = Cart::factory()->for($buyer)->create();
        CartItem::factory()->for($cart)->for($product)->create([
            'quantity' => 2,
            'unit_price' => 600.00,
        ]);

        Livewire::actingAs($buyer)
            ->test('pages::checkout.index')
            ->set('payment_method', 'credit_card')
            ->call('placeOrder')
            ->assertHasErrors(['checkout']);

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseHas('cart_items', ['cart_id' => $cart->id, 'product_id' => $product->id]);
        $this->assertSame(10, $product->fresh()->stock_quantity);
    }

    public function test_checkout_rejects_insufficient_stock_and_keeps_cart_intact(): void
    {
        $buyer = User::factory()->create();
        $vendor = Vendor::factory()->create(['is_active' => true]);
        $product = Product::factory()->for($vendor)->create([
            'status' => 'active',
            'price' => 50.00,
            'stock_quantity' => 1,
        ]);

        $cart = Cart::factory()->for($buyer)->create();
        CartItem::factory()->for($cart)->for($product)->create([
            'quantity' => 2,
            'unit_price' => 50.00,
        ]);

        Livewire::actingAs($buyer)
            ->test('pages::checkout.index')
            ->set('payment_method', 'wallet')
            ->call('placeOrder')
            ->assertHasErrors(['checkout']);

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseHas('cart_items', ['cart_id' => $cart->id, 'product_id' => $product->id]);
    }

    public function test_checkout_rejects_empty_cart(): void
    {
        $buyer = User::factory()->create();
        Cart::factory()->for($buyer)->create();

        Livewire::actingAs($buyer)
            ->test('pages::checkout.index')
            ->set('payment_method', 'wallet')
            ->call('placeOrder')
            ->assertHasErrors(['checkout']);

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_checkout_rejects_when_product_was_removed_after_adding_to_cart(): void
    {
        $buyer = User::factory()->create();
        $vendor = Vendor::factory()->create(['is_active' => true]);
        $product = Product::factory()->for($vendor)->create([
            'status' => 'active',
            'price' => 49.00,
            'stock_quantity' => 9,
        ]);

        $cart = Cart::factory()->for($buyer)->create();
        CartItem::factory()->for($cart)->for($product)->create([
            'quantity' => 1,
            'unit_price' => 49.00,
        ]);

        $product->delete();

        Livewire::actingAs($buyer)
            ->test('pages::checkout.index')
            ->set('payment_method', 'wallet')
            ->call('placeOrder')
            ->assertHasErrors(['checkout']);

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('cart_items', 1);
    }

    public function test_non_vendor_cannot_access_vendor_management_routes(): void
    {
        /** @var User $buyer */
        $buyer = User::factory()->create();

        $this->actingAs($buyer)
            ->get(route('vendor.products.index'))
            ->assertRedirect(route('vendor.onboarding'));
    }
}
