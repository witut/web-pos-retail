<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;

class POSTransactionTest extends TestCase
{
    use RefreshDatabase;

    private User $cashier;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        // Create Cashier
        $this->cashier = User::factory()->create([
            'role' => 'cashier',
            'name' => 'Test Cashier'
        ]);

        // Create Product
        $this->product = Product::factory()->create([
            'stock_on_hand' => 100,
            'selling_price' => 10000,
            'cost_price' => 5000,
            'base_unit' => 'Pcs',
            'status' => 'active'
        ]);

        // Setup Tax Setting (10%)
        Setting::factory()->create([
            'key' => 'tax_rate',
            'value' => '10'
        ]);

        // Setup Tax Type (Exclusive)
        Setting::factory()->create([
            'key' => 'tax_type',
            'value' => 'exclusive'
        ]);
    }

    /** @test */
    public function it_can_load_pos_page()
    {
        $response = $this->actingAs($this->cashier)
            ->get(route('pos.index'));

        $response->assertStatus(200);
        $response->assertViewIs('cashier.pos.index');
        $response->assertSee('Test Cashier'); // Ensure user name is visible
    }

    /** @test */
    public function it_can_checkout_successfully()
    {
        $buyQty = 2;
        $price = $this->product->selling_price;
        $subtotal = $buyQty * $price;
        $tax = $subtotal * 0.10; // 10%
        $total = $subtotal + $tax;

        $payload = [
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'qty' => $buyQty,
                    'price' => $price,
                    'unit_name' => 'Pcs'
                ]
            ],
            'payment' => [
                'method' => 'cash',
                'amount_paid' => 50000, // Sufficient payment
            ]
        ];

        $response = $this->actingAs($this->cashier)
            ->postJson(route('pos.checkout'), $payload);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'invoice_number',
                'transaction_id',
                'total'
            ])
            ->assertJson([
                'success' => true,
                'total' => $total
            ]);

        // Verify Database
        $this->assertDatabaseHas('transactions', [
            'cashier_id' => $this->cashier->id,
            'total' => $total,
            'payment_method' => 'cash',
            'status' => 'completed'
        ]);

        $this->assertDatabaseHas('transaction_items', [
            'product_id' => $this->product->id,
            'qty' => $buyQty,
            'unit_price' => $price,
            'subtotal' => $subtotal
        ]);

        // Verify Stock Deduction via Database check on Product
        $this->assertDatabaseHas('products', [
            'id' => $this->product->id,
            'stock_on_hand' => 100 - $buyQty
        ]);

        // Verify Stock Movement
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'movement_type' => 'OUT',
            'qty' => -$buyQty,
            'reference_type' => 'SALE'
        ]);
    }
}
