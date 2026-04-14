<?php

namespace App\Domain\OrderManagement\Policies;

use App\Domain\OrderManagement\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function view(User $user, Order $order): bool
    {
        return $order->user_id === $user->id;
    }
}
