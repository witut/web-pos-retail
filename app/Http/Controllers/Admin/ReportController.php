<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Laporan Penjualan (Sales Report)
     */
    public function sales(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::today()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::today()->endOfMonth()->format('Y-m-d'));
        $cashierId = $request->input('cashier_id');
        $paymentMethod = $request->input('payment_method');

        $filters = array_filter([
            'cashier_id' => $cashierId,
            'payment_method' => $paymentMethod
        ]);

        $data = $this->reportService->getSalesReport($startDate, $endDate, $filters);
        $cashiers = User::where('role', 'cashier')->get();

        return view('admin.reports.sales', array_merge($data, [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'cashiers' => $cashiers,
            'selectedCashier' => $cashierId,
            'selectedPaymentMethod' => $paymentMethod
        ]));
    }

    /**
     * Laporan Stok (Stock Value Report)
     */
    public function stock(Request $request)
    {
        $categoryId = $request->input('category_id');

        $data = $this->reportService->getStockValueReport($categoryId);
        $categories = \App\Models\Category::all();

        return view('admin.reports.stock', array_merge($data, [
            'categories' => $categories,
            'selectedCategory' => $categoryId
        ]));
    }

    /**
     * Laporan Dead Stock (Barang Tidak Laku)
     */
    public function deadStock(Request $request)
    {
        $days = (int) $request->input('days', 60); // Default 60 hari

        $products = $this->reportService->getDeadStockReport($days);

        return view('admin.reports.dead_stock', [
            'products' => $products,
            'days' => $days
        ]);
    }
}
