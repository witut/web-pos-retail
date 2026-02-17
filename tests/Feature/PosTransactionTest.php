<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PosTransactionTest extends TestCase
{
    use RefreshDatabase;

    protected $cashier;
    protected $product;
    protected $customer;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup Cashier
        $this->cashier = User::factory()->create([
            'role' => 'cashier',
            'name' => 'Cashier Test'
        ]);

        // Setup Product
        $this->product = Product::factory()->create([
            'name' => 'Test Product',
            'selling_price' => 100000,
            'cost_price' => 50000,
            'stock_on_hand' => 100,
            'product_type' => 'inventory',
            'base_unit' => 'pcs'
        ]);

        // Setup Customer
        $this->customer = Customer::create([
            'name' => 'Test Customer',
            'phone' => '081234567890',
            'points_balance' => 0
        ]);

        // Seed Settings
        Setting::set('customer.loyalty_enabled', true);
        Setting::set('customer.points_earn_rate', '10000:1'); // Rp 10k = 1 point
        Setting::set('customer.points_redeem_rate', '100:10000'); // 100 points = Rp 10k
        Setting::set('tax_rate', 0); // Force no tax
    }

    public function test_checkout_with_customer_earns_points()
    {
        $this->actingAs($this->cashier);

        // Buy 2 items @ 100k = 200k total
        // Earn Rate 10k:1 => Should earn 20 points

        $response = $this->postJson(route('pos.checkout'), [
            'payment' => [
                'method' => 'cash',
                'amount_paid' => 200000
            ],
            'customer_id' => $this->customer->id,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'qty' => 2,
                    'price' => 100000,
                    'unit_name' => 'pcs'
                ]
            ]
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        // Verify Database
        $this->assertDatabaseHas('transactions', [
            'total' => 200000,
            'customer_id' => $this->customer->id,
            'points_earned' => 20
        ]);

        // Verify Customer Points Balance
        $this->assertEquals(20, $this->customer->fresh()->points_balance);

        // Verify Customer Stats
        $this->assertEquals(200000, $this->customer->fresh()->total_spent);
    }

    public function test_checkout_redeem_points()
    {
        $this->actingAs($this->cashier);

        // Give customer 200 points (worth Rp 20k)
        $this->customer->update(['points_balance' => 200]);

        // Buy 1 item @ 100k
        // Redeem 200 points (-20k)
        // Total to pay: 80k

        $response = $this->postJson(route('pos.checkout'), [
            'payment' => [
                'method' => 'cash',
                'amount_paid' => 100000 // Paying full amount to be safe
            ],
            'customer_id' => $this->customer->id,
            'points_to_redeem' => 200,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'qty' => 1,
                    'price' => 100000,
                    'unit_name' => 'pcs'
                ]
            ]
        ]);

        $response->assertStatus(200);

        // Verify Transaction
        $this->assertDatabaseHas('transactions', [
            'subtotal' => 100000,
            'points_discount_amount' => 20000,
            'total' => 80000,
            'points_redeemed' => 200,
            'change_amount' => 20000 // 100000 paid - 80000 total = 20000 change
        ]);

        // Verify Customer Balance (200 - 200 + 8 (earned from 80k)) = 8 points
        // Earn on 80k => 8 points
        // Earn on 80k => 8 points
        $this->assertEquals(8, $this->customer->fresh()->points_balance);
    }

}
