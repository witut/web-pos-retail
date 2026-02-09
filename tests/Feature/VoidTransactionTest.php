<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Setting;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VoidTransactionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $cashier;
    private Transaction $transaction;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        // Create Admin with PIN
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'name' => 'Admin User',
            'pin' => '123456'
        ]);

        // Create Cashier
        $this->cashier = User::factory()->create([
            'role' => 'cashier',
            'name' => 'Cashier User'
        ]);

        // Create Product
        $this->product = Product::factory()->create([
            'stock_on_hand' => 90, // Stock after sale
            'selling_price' => 10000,
        ]);

        // Create Completed Transaction
        $this->transaction = Transaction::factory()->create([
            'cashier_id' => $this->cashier->id,
            'status' => 'completed',
            'transaction_date' => now(), // Just now
        ]);

        // Create Transaction Item
        TransactionItem::factory()->create([
            'transaction_id' => $this->transaction->id,
            'product_id' => $this->product->id,
            'qty' => 10,
            'unit_price' => 10000,
        ]);

        // Setup Settings
        Setting::factory()->create(['key' => 'void_time_limit', 'value' => '24']);
    }

    /** @test */
    public function it_can_void_transaction_with_valid_admin_pin()
    {
        $payload = [
            'admin_pin' => '123456',
            'void_reason' => 'Wrong Item',
            'void_notes' => 'Customer request'
        ];

        $response = $this->actingAs($this->cashier)
            ->postJson(route('pos.transaction.void', $this->transaction), $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Transaksi berhasil di-void'
            ]);

        // Verify Database
        $this->assertDatabaseHas('transactions', [
            'id' => $this->transaction->id,
            'status' => 'void',
            'void_reason' => 'Wrong Item',
            'voided_by' => $this->admin->id
        ]);

        // Verify Stock Returned
        $this->assertDatabaseHas('products', [
            'id' => $this->product->id,
            'stock_on_hand' => 100 // 90 + 10 returned
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'movement_type' => 'RETURN',
            'qty' => 10,
            'reference_type' => 'VOID'
        ]);
    }

    /** @test */
    public function it_fails_to_void_with_invalid_pin()
    {
        $payload = [
            'admin_pin' => '000000', // Wrong PIN
            'void_reason' => 'Wrong Item'
        ];

        $response = $this->actingAs($this->cashier)
            ->postJson(route('pos.transaction.void', $this->transaction), $payload);

        $response->assertStatus(422) // Or 403? Controller uses 422 for 'PIN admin tidak valid' logic
            ->assertJson([
                'success' => false
            ]);

        // Verify Database Unchanged
        $this->assertDatabaseHas('transactions', [
            'id' => $this->transaction->id,
            'status' => 'completed'
        ]);
    }
}
