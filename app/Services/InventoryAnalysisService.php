<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InventoryAnalysisService
{
    /**
     * Get products with NO sales in the last X days.
     * 
     * @param int $days Threshold days (default 90)
     * @return \Illuminate\Support\Collection
     */
    public function getDeadStock(int $days = 90)
    {
        $date = Carbon::now()->subDays($days);

        // Get IDs of products sold in the period
        $soldProductIds = DB::table('transaction_items')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->where('transactions.transaction_date', '>=', $date)
            ->where('transactions.status', 'completed')
            ->distinct()
            ->pluck('product_id');

        // Return active products NOT sold, with stock > 0
        return Product::active()
            ->whereNotIn('id', $soldProductIds)
            ->where('stock_on_hand', '>', 0)
            ->with('category')
            ->orderByDesc('stock_on_hand')
            ->get()
            ->map(function ($product) use ($days) {
                $product->days_since_last_sale = $this->getDaysSinceLastSale($product->id);
                $product->analysis_type = 'dead_stock';
                $product->analysis_reason = "No sales in last $days days";
                return $product;
            });
    }

    /**
     * Get products with LOW sales frequency in the last X days.
     * 
     * @param int $days Period to check (default 90)
     * @param int $maxSalesQty Maximum quantity sold to be considered slow moving (default 5)
     * @return \Illuminate\Support\Collection
     */
    public function getSlowMoving(int $days = 90, int $maxSalesQty = 5)
    {
        $date = Carbon::now()->subDays($days);

        // Get sales count per product
        $productSales = DB::table('transaction_items')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->where('transactions.transaction_date', '>=', $date)
            ->where('transactions.status', 'completed')
            ->select('product_id', DB::raw('SUM(qty) as total_qty'))
            ->groupBy('product_id')
            ->having('total_qty', '<=', $maxSalesQty)
            ->pluck('total_qty', 'product_id'); // ID => Qty

        $productIds = $productSales->keys();

        return Product::active()
            ->whereIn('id', $productIds)
            ->where('stock_on_hand', '>', 0) // Only care if we have stock
            ->with('category')
            ->get()
            ->map(function ($product) use ($productSales, $days) {
                $qtySold = $productSales[$product->id] ?? 0;
                $product->sales_in_period = $qtySold;
                $product->analysis_type = 'slow_moving';
                $product->analysis_reason = "Only $qtySold sold in last $days days";
                return $product;
            })
            ->sortByDesc('stock_on_hand'); // Show highest stock first
    }

    /**
     * Helper to find days since last sale.
     */
    private function getDaysSinceLastSale($productId)
    {
        $lastSale = DB::table('transaction_items')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->where('product_id', $productId)
            ->where('transactions.status', 'completed')
            ->orderByDesc('transactions.transaction_date')
            ->value('transactions.transaction_date');

        if (!$lastSale) {
            return -1; // Never sold
        }

        return Carbon::parse($lastSale)->diffInDays(Carbon::now());
    }
}
