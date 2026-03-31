<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\ProductSerial;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Exception;

/**
 * PurchaseService
 * 
 * Service untuk menangani transaksi pengadaan barang (Pembelian).
 * Mencakup:
 * 1. Pembuatan batch otomatis (Farmasi/Bakery)
 * 2. Pembuatan nomor seri otomatis (Elektronik)
 * 3. Update stok & HPP (Weighted Average)
 * 4. Manajemen Hutang (Account Payable)
 */
class PurchaseService
{
    protected StockService $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * Memproses transaksi pembelian
     * 
     * @param array $data Input dari controller
     * @param int $userId ID user yang melakukan input
     * @return Purchase
     * @throws Exception
     */
    public function processPurchase(array $data, int $userId): Purchase
    {
        return DB::transaction(function () use ($data, $userId) {
            
            // 1. Hitung Total & Debt
            $totalAmount = 0;
            foreach ($data['items'] as $item) {
                $totalAmount += $item['qty'] * $item['cost_per_unit'];
            }

            $paidAmount = (float) ($data['paid_amount'] ?? 0);
            $debtAmount = $totalAmount - $paidAmount;
            $paymentStatus = $this->determinePaymentStatus($totalAmount, $paidAmount);

            // 2. Buat Header Pembelian
            $purchase = Purchase::create([
                'purchase_number' => $this->generatePurchaseNumber(),
                'supplier_id' => $data['supplier_id'],
                'purchase_date' => $data['purchase_date'] ?? now()->toDateString(),
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'debt_amount' => max(0, $debtAmount),
                'payment_status' => $paymentStatus,
                'status' => 'received',
                'due_date' => $data['due_date'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => $userId,
            ]);

            // 3. Proses Items
            foreach ($data['items'] as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                
                // Simpan detail item pembelian
                $purchaseItem = PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $product->id,
                    'qty' => $itemData['qty'],
                    'unit_name' => $itemData['unit_name'] ?? $product->base_unit,
                    'cost_per_unit' => $itemData['cost_per_unit'],
                    'subtotal' => $itemData['qty'] * $itemData['cost_per_unit'],
                ]);

                // A. Handle Batch & Expiry (Jika tipe = batch)
                $batchId = null;
                if ($product->tracking_type === 'batch') {
                    $batch = ProductBatch::create([
                        'product_id' => $product->id,
                        'batch_number' => $itemData['batch_number'] ?? ('B-' . now()->timestamp),
                        'expiry_date' => $itemData['expiry_date'] ?? now()->addYear()->toDateString(),
                        'initial_quantity' => $itemData['qty'],
                        'current_quantity' => $itemData['qty'],
                        'cost_price' => $itemData['cost_per_unit'],
                        'supplier_id' => $data['supplier_id'],
                        'status' => 'available',
                    ]);
                    $batchId = $batch->id;
                }

                // B. Handle Serial Numbers (Jika tipe = serial)
                if ($product->tracking_type === 'serial' && !empty($itemData['serials'])) {
                    $serials = is_array($itemData['serials']) 
                        ? $itemData['serials'] 
                        : preg_split('/[\n,]+/', $itemData['serials']);
                    
                    $serials = array_filter(array_map('trim', $serials));

                    foreach ($serials as $sn) {
                        ProductSerial::create([
                            'product_id' => $product->id,
                            'serial_number' => $sn,
                            'status' => 'available',
                            'purchase_id' => $purchase->id,
                        ]);
                    }
                }

                // C. Update Main Stock & HPP (Weighted Average)
                $oldStock = $product->stock_on_hand;
                $oldCost = $product->cost_price;
                
                $conversionRate = $product->getConversionRate($itemData['unit_name'] ?? $product->base_unit);
                $qtyInBaseUnit = (float) $itemData['qty'] * $conversionRate;
                $costPerBaseUnit = (float) $itemData['cost_per_unit'] / $conversionRate;

                $newCost = $this->stockService->calculateWeightedAverageCost(
                    $oldStock, (float) $oldCost, $qtyInBaseUnit, $costPerBaseUnit
                );

                $newStock = $oldStock + $qtyInBaseUnit;
                $product->update([
                    'stock_on_hand' => $newStock,
                    'cost_price' => $newCost,
                ]);

                // D. Record Stock Movement
                StockMovement::create([
                    'product_id' => $product->id,
                    'movement_type' => 'IN',
                    'reference_type' => 'PURCHASE',
                    'reference_id' => $purchase->purchase_number,
                    'qty' => $qtyInBaseUnit, // Selalu dicatat dalam satuan dasar pada StockMovement
                    'unit_name' => $product->base_unit,
                    'cost_price' => $costPerBaseUnit,
                    'stock_before' => $oldStock,
                    'stock_after' => $newStock,
                    'batch_id' => $batchId ?? null,
                    'product_serial_id' => null, // Serials are multiple, tracked via relations
                    'notes' => "Pembelian dari " . $purchase->supplier->name . " ({$itemData['qty']} {$itemData['unit_name']})",
                    'user_id' => $userId,
                ]);
            }

            return $purchase->load('items.product');
        });
    }

    /**
     * Tentukan status pembayaran
     */
    private function determinePaymentStatus($total, $paid): string
    {
        if ($paid >= $total) return 'paid';
        if ($paid <= 0) return 'unpaid';
        return 'partial';
    }

    /**
     * Generate Nomor Pembelian
     */
    public function generatePurchaseNumber(): string
    {
        $prefix = 'PUR/' . now()->format('Y/m') . '/';
        $lastPurchase = Purchase::where('purchase_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastPurchase) {
            $lastNumber = (int) substr($lastPurchase->purchase_number, -5);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }
}
