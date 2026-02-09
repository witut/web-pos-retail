<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TransactionItem>
 */
class TransactionItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $qty = fake()->numberBetween(1, 5);
        $price = fake()->numberBetween(1000, 50000);

        return [
            'transaction_id' => Transaction::factory(),
            'product_id' => Product::factory(),
            'product_name' => fake()->word(),
            'unit_name' => 'Pcs',
            'qty' => $qty,
            'unit_price' => $price,
            'subtotal' => $qty * $price,
            'cost_price' => $price * 0.8, // 20% margin
        ];
    }
}
