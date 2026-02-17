<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Product;
use App\Models\Setting;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * TransactionService
 * 
 * Service untuk handle business logic transaksi penjualan
 * 
 * FITUR UTAMA:
 * 1. createTransaction() - Process checkout dengan stock deduction (atomic)
 * 2. voidTransaction() - Void dengan Admin PIN + stock restoration
 * 3. generateInvoiceNumber() - Sequential invoice numbering
 * 4. calculateTotal() - Calculate subtotal, tax, total
 */
class TransactionService
{
    protected CustomerService $customerService;

    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }

    /**
     * Create new transaction (Checkout process)
     * 
     * CRITICAL: Atomic transaction
     * - Validate stock availability
     * - Generate invoice number (sequential, locked)
     * - Deduct stock via StockService
     * - Create transaction + items
     * - Create stock movements
     * - Handle customer points (if applicable)
     * 
     * @param array $cartItems Format: [['product_id' => 1, 'qty' => 2, 'unit_name' => 'pcs'], ...]
     * @param array $paymentData Format: ['method' => 'cash', 'amount_paid' => 100000]
     * @param int $cashierId
     * @param int|null $customerId
     * @param float $pointsDiscount
     * @return Transaction
     * @throws Exception
     */
    public function createTransaction(
        array $cartItems,
        array $paymentData,
        int $cashierId,
        ?int $customerId = null,
        float $pointsDiscount = 0,
        int $pointsToRedeem = 0
    ): Transaction {
        // Validate cart tidak kosong
        if (empty($cartItems)) {
            throw new Exception('Keranjang belanja kosong');
        }

        return DB::transaction(function () use ($cartItems, $paymentData, $cashierId, $customerId, $pointsDiscount, $pointsToRedeem) {

            // 1. Validate & prepare items dengan HPP
            $preparedItems = $this->validateAndPrepareItems($cartItems);

            // 2. Calculate totals
            $totals = $this->calculateTotal($preparedItems);
            // 2. Calculate totals
            $totals = $this->calculateTotal($preparedItems);
            $grandTotal = $totals['total'] - $pointsDiscount;

            // 3. Validate payment amount
            // Allow small float difference
            if ($paymentData['amount_paid'] < round($grandTotal) - 1) {
                throw new Exception('Jumlah pembayaran kurang dari total');
            }

            // 4. Generate invoice number (dengan locking untuk prevent collision)
            $invoiceNumber = $this->generateInvoiceNumber();

            // 5. Calculate change
            $changeAmount = $paymentData['amount_paid'] - $grandTotal;

            // 6. Calculate points earned
            $pointsEarned = 0;
            if ($customerId) {
                // Points calculated based on total after discount
                $finalTotal = $totals['total'] - $pointsDiscount;
                $pointsEarned = $this->customerService->calculatePointsEarned($finalTotal);
            }

            // 7. Create transaction record
            $transaction = Transaction::create([
                'invoice_number' => $invoiceNumber,
                'transaction_date' => now(),
                'cashier_id' => $cashierId,
                'customer_id' => $customerId,
                'subtotal' => $totals['subtotal'],
                'tax_amount' => $totals['tax_amount'],
                'discount_amount' => 0, // Future feature
                'points_discount_amount' => $pointsDiscount,
                'total' => $totals['total'] - $pointsDiscount,
                'points_earned' => $pointsEarned,
                'points_redeemed' => $pointsToRedeem,
                'payment_method' => $paymentData['method'],
                'amount_paid' => $paymentData['amount_paid'],
                'change_amount' => $changeAmount,
                'status' => 'completed',
            ]);

            // 8. Create transaction items & deduct stock
            $stockService = new StockService();

            foreach ($preparedItems as $item) {
                // Create transaction item
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'], // Snapshot
                    'unit_name' => $item['unit_name'],
                    'qty' => $item['qty'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['subtotal'],
                    'cost_price' => $item['cost_price'], // HPP untuk profit calc
                ]);

                // Deduct stock (create stock movement)
                $stockService->deductStock(
                    $item['product_id'],
                    $item['qty'],
                    $item['unit_name'],
                    'SALE',
                    $transaction->invoice_number,
                    $cashierId
                );
            }

            return $transaction->load('items');
        });
    }

    /**
     * Void transaction dengan Admin PIN
     * 
     * BUSINESS RULES:
     * - Check void time limit (dari settings)
     * - Verify Admin PIN
     * - Restore stock
     * - Update status ke 'void'
     * - Create audit log
     * 
     * @param Transaction $transaction
     * @param string $adminPin
     * @param string $voidReason
     * @param string|null $voidNotes
     * @param int $adminId
     * @return Transaction
     * @throws Exception
     */
    public function voidTransaction(
        Transaction $transaction,
        string $adminPin,
        string $voidReason,
        ?string $voidNotes,
        int $adminId
    ): Transaction {

        // 1. Validate transaction belum void
        if ($transaction->isVoided()) {
            throw new Exception('Transaksi sudah di-void sebelumnya');
        }

        // 2. Validate void time limit
        $voidTimeLimit = Setting::getVoidTimeLimit(); // Default 24 hours
        if (!$transaction->canBeVoided($voidTimeLimit)) {
            throw new Exception("Transaksi tidak bisa di-void (melewati batas waktu {$voidTimeLimit} jam)");
        }

        // 3. Verify Admin PIN
        $admin = \App\Models\User::find($adminId);
        if (!$admin || !$admin->isAdmin()) {
            throw new Exception('Hanya admin yang bisa void transaksi');
        }

        if (!$admin->verifyPin($adminPin)) {
            throw new Exception('PIN admin salah');
        }

        return DB::transaction(function () use ($transaction, $voidReason, $voidNotes, $adminId) {

            // 4. Restore stock untuk semua items
            $stockService = new StockService();

            foreach ($transaction->items as $item) {
                $stockService->restoreStock(
                    $item->product_id,
                    $item->qty,
                    $item->unit_name,
                    'VOID',
                    $transaction->invoice_number,
                    $adminId
                );
            }

            // 5. Update transaction status
            $transaction->update([
                'status' => 'void',
                'void_reason' => $voidReason,
                'void_notes' => $voidNotes,
                'voided_by' => $adminId,
                'voided_at' => now(),
            ]);

            // 6. Create audit log
            AuditLog::logAction(
                'VOID_TRANSACTION',
                'transactions',
                (string) $transaction->id,
                ['status' => 'completed'],
                [
                    'status' => 'void',
                    'void_reason' => $voidReason,
                    'void_notes' => $voidNotes,
                    'voided_by' => $adminId,
                ]
            );

            return $transaction->fresh();
        });
    }

    /**
     * Generate sequential invoice number
     * Format: INV/YYYY/MM/XXXXX (reset every month)
     * 
     * CRITICAL: Use database locking untuk prevent collision
     * 
     * @return string
     */
    public function generateInvoiceNumber(): string
    {
        $prefix = 'INV/' . now()->format('Y/m') . '/';

        // Get last invoice number bulan ini dengan locking
        $lastTransaction = Transaction::where('invoice_number', 'like', $prefix . '%')
            ->lockForUpdate() // CRITICAL: Lock untuk prevent race condition
            ->orderBy('id', 'desc')
            ->first();

        if ($lastTransaction) {
            // Extract number dari invoice terakhir
            $lastNumber = (int) substr($lastTransaction->invoice_number, -5);
            $newNumber = $lastNumber + 1;
        } else {
            // Bulan baru, mulai dari 1
            $newNumber = 1;
        }

        // Format: 00001, 00002, etc
        return $prefix . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate subtotal, tax, dan total
     * 
     * @param array $items Prepared items dengan price & qty
     * @return array ['subtotal' => float, 'tax_amount' => float, 'total' => float]
     */
    public function calculateTotal(array $items): array
    {
        $subtotal = 0;

        foreach ($items as $item) {
            $subtotal += $item['subtotal'];
        }

        // Get tax settings
        $taxRate = Setting::getTaxRate();
        $taxType = Setting::get('tax_type', 'exclusive');

        if ($taxType === 'inclusive') {
            // Formula: Tax = Total - (Total / (1 + Rate))
            // Subtotal (DPP) = Total - Tax
            $total = round($subtotal, 2); // In inclusive, SUM(prices) is the Total to pay
            $taxAmount = $total - ($total / (1 + ($taxRate / 100)));
            $subtotalNet = $total - $taxAmount;

            return [
                'subtotal' => round($subtotalNet, 2),
                'tax_amount' => round($taxAmount, 2),
                'total' => round($total, 2),
            ];
        }

        // Exclusive (Default)
        $taxAmount = $subtotal * ($taxRate / 100);
        $total = $subtotal + $taxAmount;

        return [
            'subtotal' => round($subtotal, 2),
            'tax_amount' => round($taxAmount, 2),
            'total' => round($total, 2),
        ];
    }

    /**
     * Validate cart items dan prepare untuk insert
     * 
     * - Check product exists & active
     * - Check stock availability
     * - Get HPP (cost_price) untuk profit calculation
     * - Calculate subtotal
     * 
     * @param array $cartItems
     * @return array
     * @throws Exception
     */
    protected function validateAndPrepareItems(array $cartItems): array
    {
        $preparedItems = [];

        foreach ($cartItems as $item) {
            $product = Product::active()->find($item['product_id']);

            if (!$product) {
                throw new Exception("Produk ID {$item['product_id']} tidak ditemukan atau tidak aktif");
            }

            // Get unit name and conversion rate
            $unitName = $item['unit_name'] ?? $product->base_unit;
            $conversionRate = $product->getConversionRate($unitName);
            $qtyInBaseUnit = $item['qty'] * $conversionRate;

            // Check stock availability (skip for service products)
            if ($product->product_type === 'inventory' && !$product->hasStock($qtyInBaseUnit)) {
                $availableInUnit = floor($product->stock_on_hand / $conversionRate);
                throw new Exception("Stok {$product->name} tidak mencukupi (tersedia: {$availableInUnit} {$unitName})");
            }

            // Get unit price (dari product atau product unit)
            $unitPrice = $item['unit_price'] ?? $product->selling_price;

            // Calculate Cost Price per Unit (HPP base * conversion)
            $costPricePerUnit = $product->cost_price * $conversionRate;

            $preparedItems[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'unit_name' => $unitName,
                'qty' => $item['qty'],
                'unit_price' => $unitPrice,
                'subtotal' => $unitPrice * $item['qty'],
                'cost_price' => $costPricePerUnit, // HPP per unit transaksi
            ];
        }

        return $preparedItems;
    }
}
