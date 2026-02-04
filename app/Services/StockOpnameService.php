<?php

namespace App\Services;

use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use App\Models\Product;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Exception;

/**
 * StockOpnameService
 * 
 * Service untuk handle stock taking / stock counting
 * 
 * FITUR:
 * 1. createOpname() - Create stock opname dengan variance detection
 * 2. processAdjustment() - Apply adjustment ke stock actual
 * 3. Generate opname number
 */
class StockOpnameService
{
    /**
     * Create stock opname (stock counting)
     * 
     * @param array $items Format: [['product_id' => 1, 'physical_stock' => 100], ...]
     * @param array $metadata Format: ['opname_date' => '2026-02-04', 'notes' => '...']
     * @param int $userId
     * @return StockOpname
     * @throws Exception
     */
    public function createOpname(array $items, array $metadata, int $userId): StockOpname
    {
        if (empty($items)) {
            throw new Exception('Items tidak boleh kosong');
        }

        return DB::transaction(function () use ($items, $metadata, $userId) {

            // 1. Generate opname number
            $opnameNumber = $this->generateOpnameNumber();

            // 2. Create opname header
            $opname = StockOpname::create([
                'opname_number' => $opnameNumber,
                'opname_date' => $metadata['opname_date'] ?? now()->toDateString(),
                'notes' => $metadata['notes'] ?? null,
                'created_by' => $userId,
            ]);

            // 3. Process each item
            foreach ($items as $item) {
                $product = Product::findOrFail($item['product_id']);

                $systemStock = $product->stock_on_hand;
                $physicalStock = $item['physical_stock'];
                $variance = $physicalStock - $systemStock;

                // Calculate variance value (dalam rupiah)
                $varianceValue = $variance * $product->cost_price;

                // Create opname item
                StockOpnameItem::create([
                    'opname_id' => $opname->id,
                    'product_id' => $product->id,
                    'system_stock' => $systemStock,
                    'physical_stock' => $physicalStock,
                    'variance' => $variance,
                    'variance_value' => $varianceValue,
                    'notes' => $item['notes'] ?? null,
                ]);

                // Log significant variance
                if (abs($variance) > 0) {
                    $opnameItem = StockOpnameItem::orderBy('id', 'desc')->first();
                    if ($opnameItem->isSignificantVariance()) {
                        AuditLog::logAction(
                            'STOCK_ADJUSTMENT',
                            'stock_opname_items',
                            (string) $opnameItem->id,
                            ['system_stock' => $systemStock],
                            ['physical_stock' => $physicalStock, 'variance' => $variance]
                        );
                    }
                }
            }

            return $opname->load('items.product');
        });
    }

    /**
     * Apply stock adjustment dari opname ke actual stock
     * 
     * CRITICAL: Ini akan update stock_on_hand produk
     * 
     * @param StockOpname $opname
     * @param int $userId
     * @return void
     * @throws Exception
     */
    public function processAdjustment(StockOpname $opname, int $userId): void
    {
        DB::transaction(function () use ($opname, $userId) {

            $stockService = new StockService();

            foreach ($opname->items as $item) {
                if ($item->variance == 0) {
                    continue; // No adjustment needed
                }

                $product = $item->product;

                if ($item->variance > 0) {
                    // Overstock: Tambah stock
                    $stockService->restoreStock(
                        $product->id,
                        abs($item->variance),
                        $product->base_unit,
                        'OPNAME',
                        $opname->opname_number,
                        $userId
                    );
                } else {
                    // Shortage: Kurangi stock
                    $stockService->deductStock(
                        $product->id,
                        abs($item->variance),
                        $product->base_unit,
                        'OPNAME',
                        $opname->opname_number,
                        $userId
                    );
                }
            }
        });
    }

    /**
     * Generate sequential opname number
     * Format: OPN/YYYY/MM/XXXXX
     * 
     * @return string
     */
    public function generateOpnameNumber(): string
    {
        $prefix = 'OPN/' . now()->format('Y/m') . '/';

        $lastOpname = StockOpname::where('opname_number', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderBy('id', 'desc')
            ->first();

        if ($lastOpname) {
            $lastNumber = (int) substr($lastOpname->opname_number, -5);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Get opname summary (untuk preview sebelum apply adjustment)
     * 
     * @param StockOpname $opname
     * @return array
     */
    public function getOpnameSummary(StockOpname $opname): array
    {
        $items = $opname->items()->with('product')->get();

        $totalVarianceValue = 0;
        $overstockCount = 0;
        $shortageCount = 0;
        $matchCount = 0;

        foreach ($items as $item) {
            $totalVarianceValue += $item->variance_value;

            if ($item->variance > 0) {
                $overstockCount++;
            } elseif ($item->variance < 0) {
                $shortageCount++;
            } else {
                $matchCount++;
            }
        }

        return [
            'total_products' => $items->count(),
            'total_variance_value' => $totalVarianceValue,
            'overstock_count' => $overstockCount,
            'shortage_count' => $shortageCount,
            'match_count' => $matchCount,
            'items' => $items,
        ];
    }
}
