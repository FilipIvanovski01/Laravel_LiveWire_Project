<?php

namespace Database\Factories;

use App\Domain\OrderManagement\Enums\OrderStatus;
use App\Domain\OrderManagement\Enums\PaymentMethod;
use App\Domain\OrderManagement\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement([
            OrderStatus::Pending,
            OrderStatus::Paid,
            OrderStatus::Shipped,
            OrderStatus::Delivered,
        ]);

        return [
            'user_id' => User::factory(),
            'status' => $status,
            'payment_method' => fake()->randomElement([PaymentMethod::CreditCard, PaymentMethod::Wallet]),
            'total_amount' => fake()->randomFloat(2, 20, 1200),
            'paid_at' => in_array($status, [OrderStatus::Paid, OrderStatus::Shipped, OrderStatus::Delivered], true) ? now()->subDays(fake()->numberBetween(1, 30)) : null,
        ];
    }
}
