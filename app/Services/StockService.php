<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\StockReceiving;
use App\Models\StockReceivingItem;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
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
                    'notes' => "Penerimaan stok dari supplier",
                    'user_id' => $userId,
                ]);
            }

            return $receiving->load('items');
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
     * @return void
     * @throws Exception
     */
    public function deductStock(
        int $productId,
        float $qty,
        string $unitName,
        string $referenceType,
        string $referenceId,
        int $userId
    ): void {
        $product = Product::findOrFail($productId);

        // Validate stock
        if (!$product->hasStock($qty)) {
            throw new Exception("Stok {$product->name} tidak mencukupi");
        }

        $oldStock = $product->stock_on_hand;
        $newStock = $oldStock - $qty;

        // Update product stock
        $product->update(['stock_on_hand' => $newStock]);

        // Create stock movement
        StockMovement::create([
            'product_id' => $product->id,
            'movement_type' => 'OUT',
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'qty' => -$qty, // Negative untuk OUT
            'unit_name' => $unitName,
            'cost_price' => $product->cost_price,
            'stock_before' => $oldStock,
            'stock_after' => $newStock,
            'notes' => null,
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
        int $userId
    ): void {
        $product = Product::findOrFail($productId);

        $oldStock = $product->stock_on_hand;
        $newStock = $oldStock + $qty;

        // Update product stock
        $product->update(['stock_on_hand' => $newStock]);

        // Create stock movement
        StockMovement::create([
            'product_id' => $product->id,
            'movement_type' => 'RETURN',
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'qty' => $qty, // Positive untuk RETURN
            'unit_name' => $unitName,
            'cost_price' => $product->cost_price,
            'stock_before' => $oldStock,
            'stock_after' => $newStock,
            'notes' => "Pengembalian stok dari void transaksi",
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
