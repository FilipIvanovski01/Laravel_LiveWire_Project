<?php

namespace App\Domain\OrderManagement\Policies;

use App\Domain\OrderManagement\Models\OrderItem;
use App\Models\User;

class OrderItemPolicy
{
    public function view(User $user, OrderItem $orderItem): bool
    {
        return $user->vendor !== null
            && $orderItem->vendor_id === $user->vendor->id;
    }

    public function update(User $user, OrderItem $orderItem): bool
    {
        return $this->view($user, $orderItem);
    }
}
