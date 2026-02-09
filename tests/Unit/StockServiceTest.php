<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\StockService;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StockServiceTest extends TestCase
{
    use RefreshDatabase;

    private StockService $stockService;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->stockService = new StockService();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_calculates_weighted_average_cost_correctly()
    {
        // Scenario 1: Initial Stock 0
        // Old: 0 @ 0
        // New: 10 @ 1000
        // Result: 1000
        $result = $this->stockService->calculateWeightedAverageCost(0, 0, 10, 1000);
        $this->assertEquals(1000, $result);

        // Scenario 2: Adding stock with different price
        // Old: 10 @ 1000 = 10.000
        // New: 10 @ 2000 = 20.000
        // Total: 20 pcs, Value 30.000 -> Avg 1.500
        $result = $this->stockService->calculateWeightedAverageCost(10, 1000, 10, 2000);
        $this->assertEquals(1500, $result);

        // Scenario 3: Complex decimal
        // Old: 5 @ 10,500 = 52,500
        // New: 3 @ 12,000 = 36,000
        // Total: 8 pcs, Value 88,500 -> Avg 11,062.5
        $result = $this->stockService->calculateWeightedAverageCost(5, 10500, 3, 12000);
        $this->assertEquals(11062.5, $result);
    }

    /** @test */
    public function it_can_deduct_stock_successfully()
    {
        $product = Product::factory()->create([
            'stock_on_hand' => 10,
            'cost_price' => 5000
        ]);

        $this->stockService->deductStock(
            $product->id,
            3,
            'Pcs',
            'SALE',
            'INV-001',
            $this->user->id
        );

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock_on_hand' => 7
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'movement_type' => 'OUT',
            'qty' => -3,
            'reference_id' => 'INV-001'
        ]);
    }

    /** @test */
    public function it_throws_exception_when_deducting_insufficient_stock()
    {
        $product = Product::factory()->create([
            'stock_on_hand' => 5,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Stok {$product->name} tidak mencukupi");

        $this->stockService->deductStock(
            $product->id,
            10, // Requesting more than available
            'Pcs',
            'SALE',
            'INV-002',
            $this->user->id
        );
    }

    /** @test */
    public function it_can_restore_stock_successfully()
    {
        $product = Product::factory()->create([
            'stock_on_hand' => 10,
        ]);

        $this->stockService->restoreStock(
            $product->id,
            5,
            'Pcs',
            'VOID',
            'INV-003',
            $this->user->id
        );

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock_on_hand' => 15
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'movement_type' => 'RETURN',
            'qty' => 5,
            'reference_id' => 'INV-003'
        ]);
    }
    /** @test */
    public function it_can_deduct_stock_with_uom_conversion()
    {
        $product = Product::factory()->create([
            'stock_on_hand' => 100,
            'base_unit' => 'Pcs',
            'cost_price' => 1000
        ]);

        // Create Unit: Dozen = 12 Pcs
        $product->units()->create([
            'unit_name' => 'Dozen',
            'conversion_rate' => 12,
            'is_base_unit' => false,
            'is_active' => true,
            'selling_price' => 12000
        ]);

        // Deduct 2 Dozen (2 * 12 = 24 Pcs)
        $this->stockService->deductStock(
            $product->id,
            2,
            'Dozen',
            'SALE',
            'INV-UOM-001',
            $this->user->id
        );

        // Expect: 100 - 24 = 76
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock_on_hand' => 76
        ]);

        // Check Stock Movement
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'movement_type' => 'OUT',
            'qty' => -2, // Display Qty
            'unit_name' => 'Dozen',
            'cost_price' => 12000, // Cost proportional to unit (1000 * 12)
            'reference_id' => 'INV-UOM-001'
        ]);
    }
}
