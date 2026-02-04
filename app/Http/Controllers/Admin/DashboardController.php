<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

/**
 * DashboardController
 * 
 * Controller untuk halaman dashboard admin
 * Menampilkan KPI, grafik, dan statistik penjualan
 */
class DashboardController extends Controller
{
    /**
     * Display admin dashboard
     */
    public function index()
    {
        // KPI hari ini
        $todaySales = Transaction::today()
            ->completed()
            ->sum('total');

        $todayTransactionCount = Transaction::today()
            ->completed()
            ->count();

        // KPI bulan ini
        $monthSales = Transaction::whereBetween('transaction_date', [
            now()->startOfMonth(),
            now()->endOfMonth()
        ])
            ->completed()
            ->sum('total');

        $monthTransactionCount = Transaction::whereBetween('transaction_date', [
            now()->startOfMonth(),
            now()->endOfMonth()
        ])
            ->completed()
            ->count();

        // Low stock products
        $lowStockProducts = Product::lowStock()
            ->active()
            ->with('category')
            ->limit(10)
            ->get();

        // Top selling products (this month)
        $topProducts = DB::table('transaction_items')
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->whereBetween('transactions.transaction_date', [
                now()->startOfMonth(),
                now()->endOfMonth()
            ])
            ->where('transactions.status', 'completed')
            ->select(
                'products.name',
                DB::raw('SUM(transaction_items.qty) as total_qty'),
                DB::raw('SUM(transaction_items.subtotal) as total_sales')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_sales')
            ->limit(5)
            ->get();

        // Daily sales trend (last 30 days)
        $salesTrend = Transaction::whereBetween('transaction_date', [
            now()->subDays(30),
            now()
        ])
            ->completed()
            ->select(
                DB::raw('DATE(transaction_date) as date'),
                DB::raw('SUM(total) as daily_total'),
                DB::raw('COUNT(*) as daily_count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('admin.dashboard.index', compact(
            'todaySales',
            'todayTransactionCount',
            'monthSales',
            'monthTransactionCount',
            'lowStockProducts',
            'topProducts',
            'salesTrend'
        ));
    }
}
