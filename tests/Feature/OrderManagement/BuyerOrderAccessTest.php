<?php

namespace Tests\Feature\OrderManagement;

use App\Domain\OrderManagement\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuyerOrderAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_buyer_can_view_only_their_order_history(): void
    {
        $buyer = User::factory()->create();
        $otherBuyer = User::factory()->create();

        $ownOrder = Order::factory()->for($buyer)->create();
        $otherOrder = Order::factory()->for($otherBuyer)->create();

        $response = $this->actingAs($buyer)->get(route('buyer.orders.index'));

        $response->assertOk();
        $response->assertSee((string) $ownOrder->id);
        $response->assertDontSee((string) $otherOrder->id);
    }

    public function test_buyer_cannot_view_another_buyers_order(): void
    {
        $buyer = User::factory()->create();
        $otherBuyer = User::factory()->create();
        $otherOrder = Order::factory()->for($otherBuyer)->create();

        $this->actingAs($buyer)
            ->get(route('buyer.orders.show', $otherOrder))
            ->assertForbidden();
    }
}
