<?php

namespace App\Domain\OrderManagement\Enums;

enum PaymentMethod: string
{
    case CreditCard = 'credit_card';
    case Wallet = 'wallet';
}
