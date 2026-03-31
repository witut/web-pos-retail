<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\StockReceiving;
use App\Models\StockReceivingItem;
use App\Models\AuditLog;
use App\Models\ProductSerial;
use App\Models\Setting;
use App\Models\Purchase;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;
use Exception;

/**
 * StockService
 * 
 * Service untuk handle business logic stock management
 * 
 * FITUR UTAMA:
 * 1. receiveStock() - Penerimaan stok dari supplier (update HPP dengan weighted average)
 * 2. deductStock() - Kurangi stok (dari penjualan)
 * 3. restoreStock() - Kembalikan stok (dari void transaction)
 * 4. adjustStock() - Manual adjustment (opname)
 * 5. calculateWeightedAverageHP() - Hitung HPP baru
 */
class StockService
{
    /**
     * Process stock receiving dari supplier
     * 
     * CRITICAL:
     * - Update HPP dengan weighted average
     * - Increase stock_on_hand
     * - Create stock movements
     * - Generate receiving number
     * 
     * @param int $supplierId
     * @param array $items Format: [['product_id' => 1, 'qty' => 10, 'cost_per_unit' => 5000], ...]
     * @param array $metadata Format: ['invoice_number' => 'DO-123', 'receiving_date' => '2026-02-04', 'notes' => '...']
     * @param int $userId
     * @return StockReceiving
     * @throws Exception
     */
    public function receiveStock(int $supplierId, array $items, array $metadata, int $userId): StockReceiving
    {
        if (empty($items)) {
            throw new Exception('Items tidak boleh kosong');
        }

        return DB::transaction(function () use ($supplierId, $items, $metadata, $userId) {

            // 1. Generate receiving number
            $receivingNumber = $this->generateReceivingNumber();

            // 2. Calculate total cost
            $totalCost = 0;
            foreach ($items as $item) {
                $totalCost += $item['qty'] * $item['cost_per_unit'];
            }

            // 3. Create stock receiving header
            $receiving = StockReceiving::create([
                'receiving_number' => $receivingNumber,
                'supplier_id' => $supplierId,
                'invoice_number' => $metadata['invoice_number'] ?? null,
                'receiving_date' => $metadata['receiving_date'] ?? now()->toDateString(),
                'total_cost' => $totalCost,
                'notes' => $metadata['notes'] ?? null,
                'created_by' => $userId,
            ]);

            // 4. Process each item
            foreach ($items as $item) {
                $product = Product::findOrFail($item['product_id']);

                // Save old values untuk stock movement
                $oldStock = $product->stock_on_hand;
                $oldCost = $product->cost_price;

                // Calculate new weighted average HPP
                $newCost = $this->calculateWeightedAverageCost(
                    $product->stock_on_hand,
                    $product->cost_price,
                    $item['qty'],
                    $item['cost_per_unit']
                );

                // Update product stock & HPP
                $newStock = $product->stock_on_hand + $item['qty'];
                $product->update([
                    'stock_on_hand' => $newStock,
                    'cost_price' => $newCost,
                ]);

                // Create receiving item
                StockReceivingItem::create([
                    'receiving_id' => $receiving->id,
                    'product_id' => $product->id,
                    'qty' => $item['qty'],
                    'unit_name' => $item['unit_name'] ?? $product->base_unit,
                    'cost_per_unit' => $item['cost_per_unit'],
                    'subtotal' => $item['qty'] * $item['cost_per_unit'],
                ]);

                // Create stock movement
                StockMovement::create([
                    'product_id' => $product->id,
                    'movement_type' => 'IN',
                    'reference_type' => 'RECEIVING',
                    'reference_id' => $receivingNumber,
                    'qty' => $item['qty'],
                    'unit_name' => $item['unit_name'] ?? $product->base_unit,
                    'cost_price' => $newCost,
                    'stock_before' => $oldStock,
                    'stock_after' => $newStock,
                    'batch_id' => $item['batch_id'] ?? null,
                    'product_serial_id' => $item['product_serial_id'] ?? null,
                    'notes' => "Penerimaan stok dari supplier",
                    'user_id' => $userId,
                ]);
            }

            return $receiving->load('items');
        });
    }

    /**
     * Process stock record from a Purchase
     * 
     * @param \App\Models\Purchase $purchase
     * @param array $items Data from controller
     * @return void
     */
    public function recordPurchase($purchase, array $items)
    {
        return DB::transaction(function () use ($purchase, $items) {
            foreach ($items as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                
                // 1. Update HPP (Weighted Average)
                $oldStock = $product->stock_on_hand;
                $oldCost = $product->cost_price;
                $newCost = $this->calculateWeightedAverageCost(
                    $oldStock, $oldCost, $itemData['qty'], $itemData['cost_per_unit']
                );

                // 2. Update Main Stock
                $newStock = $oldStock + $itemData['qty'];
                $product->update([
                    'stock_on_hand' => $newStock,
                    'cost_price' => $newCost
                ]);

                // 3. Stock Movement
                StockMovement::create([
                    'product_id' => $product->id,
                    'movement_type' => 'IN',
                    'reference_type' => 'PURCHASE',
                    'reference_id' => $purchase->purchase_number,
                    'qty' => $itemData['qty'],
                    'unit_name' => $itemData['unit_name'],
                    'cost_price' => $newCost,
                    'stock_before' => $oldStock,
                    'stock_after' => $newStock,
                    'batch_id' => $itemData['batch_id'] ?? null,
                    'product_serial_id' => $itemData['product_serial_id'] ?? null,
                    'notes' => "Pembelian dari " . $purchase->supplier->name,
                    'user_id' => auth()->id()
                ]);
            }
        });
    }

    /**
     * Deduct stock (untuk penjualan)
     * 
     * @param int $productId
     * @param float $qty
     * @param string $unitName
     * @param string $referenceType (SALE, ADJUSTMENT, dll)
     * @param string $referenceId (Invoice number, dll)
     * @param int $userId
     * @param array $options ['serial_id' => 1, 'batch_id' => 1] (optional)
     * @return void
     * @throws Exception
     */
    public function deductStock(
        int $productId,
        float $qty,
        string $unitName,
        string $referenceType,
        string $referenceId,
        int $userId,
        ?string $notes = null,
        array $options = []
    ): void {
        $product = Product::findOrFail($productId);

        // Skip stock deduction for service products
        if ($product->product_type === 'service') {
            return;
        }

        // Get conversion rate
        $conversionRate = $product->getConversionRate($unitName);
        $qtyInBaseUnit = $qty * $conversionRate;

        // Check system setting for negative stock
        $allowNegative = \App\Models\Setting::get('allow_negative_stock', 'no') === 'yes';

        // Validate stock (skip if negative allowed)
        if (!$allowNegative && !$product->hasStock($qtyInBaseUnit)) {
            throw new Exception("Stok {$product->name} tidak mencukupi");
        }

        $oldStock = $product->stock_on_hand;
        $newStock = $oldStock - $qtyInBaseUnit;

        // --- Logic Tracking Tipe ---
        
        // 1. Serial Tracking (Elektronik)
        if ($product->tracking_type === 'serial') {
            $serialId = $options['serial_id'] ?? null;
            if ($serialId) {
                $serial = ProductSerial::find($serialId);
                if ($serial && $serial->status === 'available') {
                    $serial->update([
                        'status' => 'sold',
                        'sale_id' => $referenceId // Using invoice number as sale_id for now or link to Table ID
                    ]);
                    
                    // Specific movement for this serial
                    $this->createMovement($product, -$qty, $unitName, $newStock, $oldStock, $referenceType, $referenceId, $userId, $notes, null, $serial->id);
                }
            } else {
                // If serial not provided but tracking is on, we might just deduct general stock 
                // but ideally SN should be mandatory in POS UI.
                $this->createMovement($product, -$qty, $unitName, $newStock, $oldStock, $referenceType, $referenceId, $userId, $notes);
            }
        } 
        
        // 2. Batch Tracking (Pharmacy/Bakery - FEFO)
        else if ($product->tracking_type === 'batch') {
            $remainingToDeduct = $qtyInBaseUnit;
            
            // Get batches ordered by expiry date (FEFO)
            $batches = $product->batches()
                ->where('current_quantity', '>', 0)
                ->orderBy('expiry_date', 'asc')
                ->get();

            foreach ($batches as $batch) {
                if ($remainingToDeduct <= 0) break;

                $deductFromBatch = min($batch->current_quantity, $remainingToDeduct);
                $batch->decrement('current_quantity', $deductFromBatch);
                
                // Record movement for this batch
                // We use a fraction of the total qty for the movement if it's split, 
                // but for simplicity we record it once or multiple times.
                // Better record multiple if it spans batches to be accurate.
                $this->createMovement($product, -($deductFromBatch / $conversionRate), $unitName, $newStock, $oldStock, $referenceType, $referenceId, $userId, $notes, $batch->id);
                
                $remainingToDeduct -= $deductFromBatch;
            }

            // If still remaining (negative stock case), deduct from general or dummy batch
            if ($remainingToDeduct > 0 && $allowNegative) {
                $this->createMovement($product, -($remainingToDeduct / $conversionRate), $unitName, $newStock, $oldStock, $referenceType, $referenceId, $userId, $notes . " (Negative Stock)");
            }
        }
        
        // 3. Default Tracking
        else {
            $this->createMovement($product, -$qty, $unitName, $newStock, $oldStock, $referenceType, $referenceId, $userId, $notes);
        }

        // Update main product stock
        $product->update(['stock_on_hand' => $newStock]);
    }

    /**
     * Helper to create stock movement
     */
    private function createMovement($product, $qty, $unitName, $newStock, $oldStock, $type, $refId, $userId, $notes, $batchId = null, $serialId = null)
    {
        $conversionRate = $product->getConversionRate($unitName);
        
        StockMovement::create([
            'product_id' => $product->id,
            'movement_type' => 'OUT',
            'reference_type' => $type,
            'reference_id' => $refId,
            'qty' => $qty,
            'unit_name' => $unitName,
            'cost_price' => $product->cost_price * $conversionRate,
            'stock_before' => $oldStock,
            'stock_after' => $newStock,
            'batch_id' => $batchId,
            'product_serial_id' => $serialId,
            'notes' => $notes,
            'user_id' => $userId,
        ]);
    }

    /**
     * Restore stock (untuk void transaction)
     * 
     * @param int $productId
     * @param float $qty
     * @param string $unitName
     * @param string $referenceType
     * @param string $referenceId
     * @param int $userId
     * @return void
     */
    public function restoreStock(
        int $productId,
        float $qty,
        string $unitName,
        string $referenceType,
        string $referenceId,
        int $userId,
        ?string $notes = null
    ): void {
        $product = Product::findOrFail($productId);

        // Get conversion rate
        $conversionRate = $product->getConversionRate($unitName);
        $qtyInBaseUnit = $qty * $conversionRate;

        $oldStock = $product->stock_on_hand;
        $newStock = $oldStock + $qtyInBaseUnit;

        // Update product stock
        $product->update(['stock_on_hand' => $newStock]);

        // Create stock movement
        StockMovement::create([
            'product_id' => $product->id,
            'movement_type' => 'RETURN',
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'qty' => $qty, // Qty in transaction unit
            'unit_name' => $unitName,
            'cost_price' => $product->cost_price * $conversionRate,
            'stock_before' => $oldStock,
            'stock_after' => $newStock,
            'notes' => $notes ?? "Pengembalian stok dari void transaksi",
            'user_id' => $userId,
        ]);
    }

    /**
     * Adjust stock (untuk stock opname)
     * 
     * @param int $productId
     * @param float $qty (Difference/Variance, can be +/-)
     * @param string $unitName
     * @param string $referenceId
     * @param string $notes
     * @param int $userId
     * @return void
     */
    public function adjustStock(
        int $productId,
        float $qty,
        string $unitName,
        string $referenceId,
        string $notes,
        int $userId
    ): void {
        $product = Product::findOrFail($productId);

        // Get conversion rate
        $conversionRate = $product->getConversionRate($unitName);
        $qtyInBaseUnit = $qty * $conversionRate;

        $oldStock = $product->stock_on_hand;
        $newStock = $oldStock + $qtyInBaseUnit; // qty here is the variance (e.g. +5 or -2)

        // Update product stock
        $product->update(['stock_on_hand' => $newStock]);

        // Create stock movement based on ENUM constraints
        // movement_type: ADJUSTMENT
        // reference_type: OPNAME

        StockMovement::create([
            'product_id' => $product->id,
            'movement_type' => 'ADJUSTMENT',
            'reference_type' => 'OPNAME', // Must match ENUM
            'reference_id' => $referenceId,
            'qty' => $qty, // Qty in transaction unit
            'unit_name' => $unitName,
            'cost_price' => $product->cost_price * $conversionRate,
            'stock_before' => $oldStock,
            'stock_after' => $newStock,
            'notes' => $notes,
            'user_id' => $userId,
        ]);
    }

    /**
     * Calculate weighted average cost (HPP)
     * 
     * Formula: ((Old Stock × Old Cost) + (New Qty × New Cost)) ÷ Total Stock
     * 
     * Contoh:
     * - Stock lama: 10 pcs @ Rp 5.000 = Rp 50.000
     * - Pembelian baru: 5 pcs @ Rp 6.000 = Rp 30.000
     * - Total: 15 pcs @ Rp 5.333 = Rp 80.000
     * 
     * @param float $oldStock
     * @param float $oldCost
     * @param float $newQty
     * @param float $newCost
     * @return float
     */
    public function calculateWeightedAverageCost(
        float $oldStock,
        float $oldCost,
        float $newQty,
        float $newCost
    ): float {
        // Jika stok lama 0, HPP baru = cost pembelian baru
        if ($oldStock == 0) {
            return $newCost;
        }

        $oldValue = $oldStock * $oldCost;
        $newValue = $newQty * $newCost;
        $totalStock = $oldStock + $newQty;

        return round(($oldValue + $newValue) / $totalStock, 2);
    }

    /**
     * Generate sequential receiving number
     * Format: RCV/YYYY/MM/XXXXX
     * 
     * @return string
     */
    public function generateReceivingNumber(): string
    {
        $prefix = 'RCV/' . now()->format('Y/m') . '/';

        $lastReceiving = StockReceiving::where('receiving_number', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderBy('id', 'desc')
            ->first();

        if ($lastReceiving) {
            $lastNumber = (int) substr($lastReceiving->receiving_number, -5);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Get stock card (kartu stok) untuk produk tertentu
     * 
     * @param int $productId
     * @param string|null $startDate
     * @param string|null $endDate
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getStockCard(int $productId, ?string $startDate = null, ?string $endDate = null)
    {
        $query = StockMovement::where('product_id', $productId)
            ->orderBy('created_at');

        if ($startDate && $endDate) {
            $query->betweenDates($startDate, $endDate);
        }

        return $query->with('user')->get();
    }
}
