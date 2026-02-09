<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sku' => fake()->unique()->ean8(),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'category_id' => Category::factory(),
            'brand' => fake()->word(),
            'base_unit' => 'Pcs',
            'selling_price' => fake()->numberBetween(10000, 100000),
            'cost_price' => fake()->numberBetween(5000, 50000),
            'stock_on_hand' => fake()->numberBetween(0, 100),
            'min_stock_alert' => 10,
            'status' => 'active',
            'image_path' => null,
            'tax_rate' => 0,
            'created_by' => 1,
        ];
    }
}
