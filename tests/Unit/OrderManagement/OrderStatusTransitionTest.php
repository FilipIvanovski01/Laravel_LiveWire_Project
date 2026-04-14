<?php

namespace Tests\Unit\OrderManagement;

use App\Domain\OrderManagement\Enums\OrderStatus;
use Tests\TestCase;

class OrderStatusTransitionTest extends TestCase
{
    public function test_it_allows_only_defined_forward_transitions(): void
    {
        $this->assertTrue(OrderStatus::Pending->canTransitionTo(OrderStatus::Paid));
        $this->assertTrue(OrderStatus::Paid->canTransitionTo(OrderStatus::Shipped));
        $this->assertTrue(OrderStatus::Shipped->canTransitionTo(OrderStatus::Delivered));
    }

    public function test_it_rejects_backward_or_skipped_transitions(): void
    {
        $this->assertFalse(OrderStatus::Paid->canTransitionTo(OrderStatus::Pending));
        $this->assertFalse(OrderStatus::Pending->canTransitionTo(OrderStatus::Shipped));
        $this->assertFalse(OrderStatus::Delivered->canTransitionTo(OrderStatus::Shipped));
    }
}
