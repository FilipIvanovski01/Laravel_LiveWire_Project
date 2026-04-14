<?php

namespace Tests\Feature\Cart;

use App\Domain\Cart\Models\Cart;
use App\Domain\Cart\Models\CartItem;
use App\Domain\ProductCatalog\Models\Product;
use App\Domain\ProductCatalog\Models\Vendor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CartInteractionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_add_product_to_cart_and_duplicate_add_increases_quantity(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()
            ->for(Vendor::factory()->create(['is_active' => true]))
            ->create(['status' => 'active', 'stock_quantity' => 5]);

        Livewire::actingAs($user)
            ->test('pages::cart.index')
            ->call('addProduct', $product->id)
            ->call('addProduct', $product->id);

        $cart = Cart::query()->where('user_id', $user->id)->firstOrFail();

        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
    }

    public function test_user_cannot_exceed_stock_when_adding_or_updating_quantities(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()
            ->for(Vendor::factory()->create(['is_active' => true]))
            ->create(['status' => 'active', 'stock_quantity' => 2]);

        Livewire::actingAs($user)
            ->test('pages::cart.index')
            ->call('addProduct', $product->id)
            ->call('addProduct', $product->id)
            ->call('addProduct', $product->id);

        $cart = Cart::query()->where('user_id', $user->id)->firstOrFail();
        $item = CartItem::query()->where('cart_id', $cart->id)->where('product_id', $product->id)->firstOrFail();

        $this->assertSame(2, $item->quantity);

        Livewire::actingAs($user)
            ->test('pages::cart.index')
            ->set("quantities.{$item->id}", 4)
            ->call('blurQuantity', $item->id)
            ->assertHasErrors(["quantities.{$item->id}"]);

        $this->assertSame(2, $item->fresh()->quantity);
    }

    public function test_user_can_remove_cart_items(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()
            ->for(Vendor::factory()->create(['is_active' => true]))
            ->create(['status' => 'active', 'stock_quantity' => 5]);

        $cart = Cart::factory()->for($user)->create();
        $item = CartItem::factory()->for($cart)->for($product)->create(['quantity' => 1, 'unit_price' => $product->price]);

        Livewire::actingAs($user)
            ->test('pages::cart.index')
            ->call('removeItem', $item->id);

        $this->assertDatabaseMissing('cart_items', [
            'id' => $item->id,
        ]);
    }

    public function test_user_cannot_add_product_with_zero_stock(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()
            ->for(Vendor::factory()->create(['is_active' => true]))
            ->create(['status' => 'active', 'stock_quantity' => 0]);

        Livewire::actingAs($user)
            ->test('pages::cart.index')
            ->call('addProduct', $product->id);

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_cart_page_shows_vendor_grouping_subtotals_and_total(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $vendorA = Vendor::factory()->create(['store_name' => 'Vendor A', 'is_active' => true]);
        $vendorB = Vendor::factory()->create(['store_name' => 'Vendor B', 'is_active' => true]);

        $productA = Product::factory()->for($vendorA)->create(['status' => 'active', 'price' => 10.00]);
        $productB = Product::factory()->for($vendorB)->create(['status' => 'active', 'price' => 20.00]);

        $cart = Cart::factory()->for($user)->create();
        CartItem::factory()->for($cart)->for($productA)->create(['quantity' => 2, 'unit_price' => 10.00]);
        CartItem::factory()->for($cart)->for($productB)->create(['quantity' => 1, 'unit_price' => 20.00]);

        $response = $this->actingAs($user)->get(route('cart.index'));

        $response->assertOk();
        $response->assertSee('Vendor A');
        $response->assertSee('Vendor B');
        $response->assertSee('Subtotal: $20.00');
        $response->assertSee('Total: $40.00');
    }
}
