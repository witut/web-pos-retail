<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Carbon\Carbon;

/**
 * DashboardController
 * 
 * Controller untuk halaman dashboard admin
 * Menampilkan KPI, grafik, dan statistik penjualan
 */
class DashboardController extends Controller
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Display admin dashboard
     */
    public function index()
    {
        // 1. KPI Cards
        $stats = $this->reportService->getDashboardStats();

        // 2. Sales Trend (Chart) - Last 30 Days
        $startDate = Carbon::now()->subDays(29)->format('Y-m-d');
        $endDate = Carbon::now()->format('Y-m-d');
        $salesTrend = $this->reportService->getDailySalesSummary($startDate, $endDate);

        // 3. Top Products (This Month)
        $monthStart = Carbon::now()->startOfMonth()->format('Y-m-d');
        $monthEnd = Carbon::now()->endOfMonth()->format('Y-m-d');
        $topProducts = $this->reportService->getTopSellingProducts($monthStart, $monthEnd, 5);

        // 4. Low Stock Products
        $lowStockProducts = $this->reportService->getLowStockReport()->take(5);

        return view('admin.dashboard.index', compact(
            'stats',
            'salesTrend',
            'topProducts',
            'lowStockProducts'
        ));
    }
}
