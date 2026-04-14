<?php

namespace Tests\Feature\OrderManagement;

use App\Domain\OrderManagement\Actions\UpdateOrderItemStatusAction;
use App\Domain\OrderManagement\Enums\OrderStatus;
use App\Domain\OrderManagement\Models\Order;
use App\Domain\OrderManagement\Models\OrderItem;
use App\Domain\ProductCatalog\Models\Vendor;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class VendorOrderManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_vendor_orders_page_shows_only_current_vendor_items(): void
    {
        $buyer = User::factory()->create();
        $order = Order::factory()->for($buyer)->create(['status' => OrderStatus::Paid]);

        $vendorAUser = User::factory()->create();
        $vendorA = Vendor::factory()->for($vendorAUser)->create();

        $vendorBUser = User::factory()->create();
        $vendorB = Vendor::factory()->for($vendorBUser)->create();

        OrderItem::factory()->for($order)->for($vendorA)->create(['product_name' => 'Visible Item']);
        OrderItem::factory()->for($order)->for($vendorB)->create(['product_name' => 'Hidden Item']);

        $response = $this->actingAs($vendorAUser)->get(route('vendor.orders.index'));

        $response->assertOk();
        $response->assertSee('Visible Item');
        $response->assertDontSee('Hidden Item');
    }

    public function test_vendor_can_mark_their_paid_line_item_as_shipped(): void
    {
        $buyer = User::factory()->create();
        $order = Order::factory()->for($buyer)->create(['status' => OrderStatus::Paid]);

        $vendorUser = User::factory()->create();
        $vendor = Vendor::factory()->for($vendorUser)->create();

        $item = OrderItem::factory()->for($order)->for($vendor)->create([
            'status' => OrderStatus::Paid,
        ]);

        Livewire::actingAs($vendorUser)
            ->test('pages::vendor.orders.index')
            ->call('markAsShipped', $item->id)
            ->assertHasNoErrors();

        $this->assertSame(OrderStatus::Shipped, $item->fresh()->status);
        $this->assertSame(OrderStatus::Shipped, $order->fresh()->status);
    }

    public function test_vendor_cannot_modify_another_vendors_line_item(): void
    {
        $buyer = User::factory()->create();
        $order = Order::factory()->for($buyer)->create(['status' => OrderStatus::Paid]);

        $ownerVendorUser = User::factory()->create();
        $ownerVendor = Vendor::factory()->for($ownerVendorUser)->create();

        $otherVendorUser = User::factory()->create();
        Vendor::factory()->for($otherVendorUser)->create();

        $item = OrderItem::factory()->for($order)->for($ownerVendor)->create([
            'status' => OrderStatus::Paid,
        ]);

        $action = app(UpdateOrderItemStatusAction::class);

        $this->expectException(AuthorizationException::class);
        $action->execute($otherVendorUser, $item, OrderStatus::Shipped);
    }

    public function test_backward_status_transition_is_rejected(): void
    {
        $buyer = User::factory()->create();
        $order = Order::factory()->for($buyer)->create(['status' => OrderStatus::Shipped]);

        $vendorUser = User::factory()->create();
        $vendor = Vendor::factory()->for($vendorUser)->create();

        $item = OrderItem::factory()->for($order)->for($vendor)->create([
            'status' => OrderStatus::Shipped,
        ]);

        $action = app(UpdateOrderItemStatusAction::class);

        $this->expectException(ValidationException::class);
        $action->execute($vendorUser, $item, OrderStatus::Paid);
    }

    public function test_non_vendor_is_redirected_from_vendor_orders_page(): void
    {
        $buyer = User::factory()->create();

        $this->actingAs($buyer)
            ->get(route('vendor.orders.index'))
            ->assertRedirect(route('vendor.onboarding'));
    }
}
