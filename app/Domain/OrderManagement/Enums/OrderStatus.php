<?php

namespace App\Domain\OrderManagement\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Shipped = 'shipped';
    case Delivered = 'delivered';

    public function canTransitionTo(self $target): bool
    {
        return match ($this) {
            self::Pending => in_array($target, [self::Paid], true),
            self::Paid => in_array($target, [self::Shipped], true),
            self::Shipped => in_array($target, [self::Delivered], true),
            self::Delivered => false,
        };
    }
}
