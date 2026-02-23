<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * ReportService
 * 
 * Service untuk generate laporan dan analytics
 * 
 * FITUR:
 * 1. Sales reports (daily, monthly, period)
 * 2. Stock reports (kartu stok, stock value)
 * 3. Profit reports
 * 4. Top selling products
 * 5. Cashier performance
 */
class ReportService
{
    /**
     * Get dashboard summary statistics (Today's KPI)
     * 
     * @return array
     */
    public function getDashboardStats(): array
    {
        $today = Carbon::today();

        // 1. Transaction Stats Today
        $todayTrx = Transaction::whereDate('transaction_date', $today)
            ->completed()
            ->get();

        $todaySales = $todayTrx->sum('total');
        $todayCount = $todayTrx->count();

        // Calculate Profit (Total - Cost of Goods Sold)
        // Note: For now assuming total profit is stored/calc via model method or simple subtraction
        $todayProfit = $todayTrx->sum(function ($trx) {
            return $trx->getTotalProfit(); // Assuming this method exists on model based on getSalesReport usage
        });

        // 2. Low Stock Count
        $lowStockCount = Product::active()
            ->whereColumn('stock_on_hand', '<=', 'min_stock_alert')
            ->count();

        // 3. Compare with yesterday for trend (optional, simple +/-)
        $yesterday = Carbon::yesterday();
        $yesterdaySales = Transaction::whereDate('transaction_date', $yesterday)
            ->completed()
            ->sum('total');

        $growth = $yesterdaySales > 0 ? (($todaySales - $yesterdaySales) / $yesterdaySales) * 100 : 100;

        // 4. Customer Stats
        $totalCustomers = DB::table('customers')->whereNull('deleted_at')->count();
        $newCustomersThisMonth = DB::table('customers')
            ->whereNull('deleted_at')
            ->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
            ->count();

        return [
            'today_sales' => $todaySales,
            'today_transactions' => $todayCount,
            'today_profit' => $todayProfit,
            'low_stock_count' => $lowStockCount,
            'growth_percentage' => round($growth, 1),
            'total_customers' => $totalCustomers,
            'new_customers_this_month' => $newCustomersThisMonth,
        ];
    }
    /**
     * Get sales report untuk periode tertentu
     * 
     * @param string $startDate
     * @param string $endDate
     * @param array $filters ['cashier_id' => int, 'payment_method' => string]
     * @return array
     */
    public function getSalesReport(string $startDate, string $endDate, array $filters = []): array
    {
        $query = Transaction::betweenDates($startDate, $endDate)
            ->completed()
            ->with('items.product', 'cashier');

        // Apply filters
        if (isset($filters['cashier_id'])) {
            $query->where('cashier_id', $filters['cashier_id']);
        }

        if (isset($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        $transactions = $query->get();

        // Calculate summary
        $summary = [
            'total_transactions' => $transactions->count(),
            'total_sales' => $transactions->sum('total'),
            'total_tax' => $transactions->sum('tax_amount'),
            'total_items_sold' => $transactions->sum(function ($t) {
                return $t->getTotalItems();
            }),
            'total_profit' => $transactions->sum(function ($t) {
                return $t->getTotalProfit();
            }),
            'average_transaction' => $transactions->avg('total'),
        ];

        // Group by payment method
        $byPaymentMethod = $transactions->groupBy('payment_method')->map(function ($group) {
            return [
                'count' => $group->count(),
                'total' => $group->sum('total'),
            ];
        });

        return [
            'summary' => $summary,
            'by_payment_method' => $byPaymentMethod,
            'transactions' => $transactions,
        ];
    }

    /**
     * Get top selling products
     * 
     * @param string $startDate
     * @param string $endDate
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function getTopSellingProducts(string $startDate, string $endDate, int $limit = 10)
    {
        return DB::table('transaction_items')
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->whereBetween('transactions.transaction_date', [$startDate, $endDate])
            ->where('transactions.status', 'completed')
            ->select(
                'products.id',
                'products.name',
                'products.sku',
                DB::raw('SUM(transaction_items.qty) as total_qty'),
                DB::raw('SUM(transaction_items.subtotal) as total_sales'),
                DB::raw('COUNT(DISTINCT transactions.id) as transaction_count'),
                DB::raw('SUM(transaction_items.subtotal - (transaction_items.qty * transaction_items.cost_price)) as total_profit')
            )
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderByDesc('total_sales')
            ->limit($limit)
            ->get();
    }

    /**
     * Get stock value report
     * Total nilai stok di gudang (stock_on_hand × cost_price)
     * 
     * @param int|null $categoryId Filter by category
     * @return array
     */
    public function getStockValueReport(?int $categoryId = null): array
    {
        $query = Product::active()->with('category');

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $products = $query->get();

        $totalValue = $products->sum(function ($product) {
            return $product->stock_on_hand * $product->cost_price;
        });

        $totalQty = $products->sum('stock_on_hand');

        // Group by category
        $byCategory = $products->groupBy('category.name')->map(function ($group) {
            return [
                'total_products' => $group->count(),
                'total_qty' => $group->sum('stock_on_hand'),
                'total_value' => $group->sum(function ($p) {
                    return $p->stock_on_hand * $p->cost_price;
                }),
            ];
        });

        return [
            'summary' => [
                'total_products' => $products->count(),
                'total_qty' => $totalQty,
                'total_value' => $totalValue,
            ],
            'by_category' => $byCategory,
            'products' => $products,
        ];
    }

    /**
     * Get cashier performance report
     * 
     * @param string $startDate
     * @param string $endDate
     * @return \Illuminate\Support\Collection
     */
    public function getCashierPerformance(string $startDate, string $endDate)
    {
        return DB::table('transactions')
            ->join('users', 'transactions.cashier_id', '=', 'users.id')
            ->whereBetween('transactions.transaction_date', [$startDate, $endDate])
            ->where('transactions.status', 'completed')
            ->select(
                'users.id',
                'users.name',
                DB::raw('COUNT(*) as total_transactions'),
                DB::raw('SUM(transactions.total) as total_sales'),
                DB::raw('AVG(transactions.total) as avg_transaction'),
                DB::raw('MIN(transactions.total) as min_transaction'),
                DB::raw('MAX(transactions.total) as max_transaction')
            )
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_sales')
            ->get();
    }

    /**
     * Get profit report (detail per transaksi)
     * 
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getProfitReport(string $startDate, string $endDate): array
    {
        $transactions = Transaction::betweenDates($startDate, $endDate)
            ->completed()
            ->with('items')
            ->get();

        $totalProfit = 0;
        $totalSales = 0;

        $details = $transactions->map(function ($transaction) use (&$totalProfit, &$totalSales) {
            $profit = $transaction->getTotalProfit();
            $totalProfit += $profit;
            $totalSales += $transaction->total;

            return [
                'invoice_number' => $transaction->invoice_number,
                'date' => $transaction->transaction_date,
                'total' => $transaction->total,
                'profit' => $profit,
                'profit_margin' => $transaction->total > 0 ? ($profit / $transaction->total) * 100 : 0,
            ];
        });

        return [
            'summary' => [
                'total_transactions' => $transactions->count(),
                'total_sales' => $totalSales,
                'total_profit' => $totalProfit,
                'avg_profit_margin' => $totalSales > 0 ? ($totalProfit / $totalSales) * 100 : 0,
            ],
            'details' => $details,
        ];
    }

    /**
     * Get daily sales summary (untuk grafik)
     * 
     * @param string $startDate
     * @param string $endDate
     * @return \Illuminate\Support\Collection
     */
    /**
     * Get daily sales summary (untuk grafik)
     * 
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getDailySalesSummary(string $startDate, string $endDate): array
    {
        $salesData = Transaction::whereDate('transaction_date', '>=', $startDate)
            ->whereDate('transaction_date', '<=', $endDate)
            ->completed()
            ->select(
                DB::raw('DATE(transaction_date) as date'),
                DB::raw('SUM(total) as total_sales')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total_sales', 'date')
            ->toArray();

        // Fill missing dates with 0
        $chartData = [
            'labels' => [],
            'data' => []
        ];

        $period = \Carbon\CarbonPeriod::create($startDate, $endDate);

        foreach ($period as $date) {
            $dateString = $date->format('Y-m-d');
            $chartData['labels'][] = $date->format('d M');
            $chartData['data'][] = $salesData[$dateString] ?? 0;
        }

        return $chartData;
    }

    /**
     * Get low stock alert report
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    /**
     * Get low stock alert report
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLowStockReport()
    {
        return Product::lowStock()
            ->active()
            ->with('category')
            ->orderBy('stock_on_hand')
            ->get();
    }

    /**
     * Get dead stock report (Barang tidak laku dalam X hari)
     * 
     * @param int $days
     * @return \Illuminate\Support\Collection
     */
    /**
     * Get dead stock report (Barang tidak laku dalam X hari)
     * 
     * @param int $days
     * @return \Illuminate\Support\Collection
     */
    public function getDeadStockReport(int $days = 30)
    {
        $date = Carbon::now()->subDays($days);

        // Subquery untuk mendapatkan ID produk yang terjual dalam X hari terakhir
        $soldProductIds = DB::table('transaction_items')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->where('transactions.transaction_date', '>=', $date)
            ->where('transactions.status', 'completed')
            ->pluck('product_id');

        // Ambil produk yang TIDAK ada di list terjual & stok > 0
        return Product::active()
            ->whereNotIn('id', $soldProductIds)
            ->where('stock_on_hand', '>', 0)
            ->with('category')
            ->orderByDesc('stock_on_hand') // Prioritaskan yang numpuk banyak
            ->get();
    }

    /**
     * Get profit & loss report (Laba Rugi)
     * 
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getProfitLossReport(string $startDate, string $endDate): array
    {
        // 1. Get completed transactions in period
        $transactions = Transaction::whereBetween('transaction_date', [$startDate, $endDate])
            ->completed()
            ->with(['items'])
            ->get();

        $summary = [
            'gross_revenue' => 0, // Total Sales (Omzet Kotor)
            'net_revenue' => 0,   // Omzet Bersih (After Discount/Tax) - For now same as Gross
            'cogs' => 0,          // Cost of Goods Sold (HPP)
            'gross_profit' => 0,  // Laba Kotor (Revenue - HPP)
            'expenses' => 0,      // Biaya Operasional (Future feature)
            'net_profit' => 0,    // Laba Bersih
            'transaction_count' => $transactions->count(),
            'items_sold' => 0
        ];

        $dailyBreakdown = [];

        foreach ($transactions as $trx) {
            $trxRevenue = $trx->total;
            $trxCOGS = 0;
            $trxItems = 0;

            foreach ($trx->items as $item) {
                // Calculate HPP for this item based on historical cost recorded at transaction time
                // If historical cost is 0 (migration data), fallback to current product cost? 
                // Better to trust recorded cost.
                $itemCOGS = $item->cost_price * $item->qty;
                $trxCOGS += $itemCOGS;
                $trxItems += $item->qty;
            }

            $trxProfit = $trx->getTotalProfit();

            $summary['gross_revenue'] += $trxRevenue;
            $summary['cogs'] += $trxCOGS;
            $summary['items_sold'] += $trxItems;

            // Group by date for chart
            $date = $trx->transaction_date->format('Y-m-d');
            if (!isset($dailyBreakdown[$date])) {
                $dailyBreakdown[$date] = [
                    'date' => $date,
                    'revenue' => 0,
                    'cogs' => 0,
                    'profit' => 0
                ];
            }
            $dailyBreakdown[$date]['revenue'] += $trxRevenue;
            $dailyBreakdown[$date]['cogs'] += $trxCOGS;
            $dailyBreakdown[$date]['profit'] += $trxProfit;
        }

        $summary['net_revenue'] = $summary['gross_revenue']; // Adjust if discounts exist
        $summary['gross_profit'] = $summary['net_revenue'] - $summary['cogs'];
        $summary['net_profit'] = $summary['gross_profit'] - $summary['expenses'];

        // Margin Calculation
        $summary['gross_margin_percent'] = $summary['net_revenue'] > 0
            ? ($summary['gross_profit'] / $summary['net_revenue']) * 100
            : 0;

        return [
            'summary' => $summary,
            'daily_breakdown' => array_values($dailyBreakdown), // Reset keys for JSON
            'transactions' => $transactions // Optional: if we want drilldown
        ];
    }
    /**
     * Get customer report (List of customers with stats)
     * 
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getCustomerReport(string $startDate, string $endDate): array
    {
        // Get customers who have transactions in the period
        $customers = DB::table('customers')
            ->join('transactions', 'customers.id', '=', 'transactions.customer_id')
            ->whereBetween('transactions.transaction_date', [$startDate, $endDate])
            ->where('transactions.status', 'completed')
            ->select(
                'customers.id',
                'customers.name',
                'customers.phone',
                'customers.points_balance',
                DB::raw('COUNT(transactions.id) as total_transactions'),
                DB::raw('SUM(transactions.total) as total_spent'),
                DB::raw('MAX(transactions.transaction_date) as last_transaction'),
                DB::raw('SUM(transactions.points_earned) as points_earned'),
                DB::raw('SUM(transactions.points_redeemed) as points_redeemed')
            )
            ->groupBy('customers.id', 'customers.name', 'customers.phone', 'customers.points_balance')
            ->orderByDesc('total_spent')
            ->get();

        $summary = [
            'total_customers' => $customers->unique('id')->count(),
            'total_transactions' => $customers->sum('total_transactions'),
            'total_spent' => $customers->sum('total_spent'),
            'total_points_earned' => $customers->sum('points_earned'),
            'total_points_redeemed' => $customers->sum('points_redeemed'),
        ];

        return [
            'summary' => $summary,
            'customers' => $customers
        ];
    }

    /**
     * Get top spending customers
     * 
     * @param string $startDate
     * @param string $endDate
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function getTopCustomers(string $startDate, string $endDate, int $limit = 5)
    {
        return DB::table('customers')
            ->join('transactions', 'customers.id', '=', 'transactions.customer_id')
            ->whereBetween('transactions.transaction_date', [$startDate, $endDate])
            ->where('transactions.status', 'completed')
            ->select(
                'customers.id',
                'customers.name',
                DB::raw('SUM(transactions.total) as total_spent'),
                DB::raw('COUNT(transactions.id) as transaction_count')
            )
            ->groupBy('customers.id', 'customers.name')
            ->orderByDesc('total_spent')
            ->limit($limit)
            ->get();
    }

    /**
     * Get points redemption report
     * 
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getPointsReport(string $startDate, string $endDate): array
    {
        $transactions = Transaction::whereBetween('transaction_date', [$startDate, $endDate])
            ->completed()
            ->whereNotNull('customer_id')
            ->where(function ($q) {
                $q->where('points_earned', '>', 0)
                    ->orWhere('points_redeemed', '>', 0);
            })
            ->with('customer')
            ->get();

        $summary = [
            'total_earned' => $transactions->sum('points_earned'),
            'total_redeemed' => $transactions->sum('points_redeemed'),
            'redemption_value' => $transactions->sum('points_discount_amount'),
            'customers_participating' => $transactions->unique('customer_id')->count()
        ];

        return [
            'summary' => $summary,
            'transactions' => $transactions
        ];
    }
}
