<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'invoice_number' => 'INV/' . now()->format('Y/m') . '/' . fake()->unique()->numerify('#####'),
            'transaction_date' => now(),
            'cashier_id' => User::factory(), // Will create a user if not provided
            'subtotal' => 100000,
            'tax_amount' => 10000,
            'discount_amount' => 0,
            'total' => 110000,
            'payment_method' => 'cash',
            'amount_paid' => 110000,
            'change_amount' => 0,
            'status' => 'completed',
        ];
    }
}
