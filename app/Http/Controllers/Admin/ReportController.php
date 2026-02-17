<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ReportService;
use App\Services\InventoryAnalysisService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    protected $reportService;
    protected $inventoryAnalysisService;

    public function __construct(
        ReportService $reportService,
        InventoryAnalysisService $inventoryAnalysisService
    ) {
        $this->reportService = $reportService;
        $this->inventoryAnalysisService = $inventoryAnalysisService;
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

        $products = $this->inventoryAnalysisService->getDeadStock($days);

        return view('admin.reports.dead_stock', [
            'products' => $products,
            'days' => $days
        ]);
    }

    /**
     * Laporan Laba Rugi (Profit & Loss)
     */
    public function profitLoss(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::today()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::today()->endOfMonth()->format('Y-m-d'));

        $data = $this->reportService->getProfitLossReport($startDate, $endDate);

        return view('admin.reports.profit_loss', array_merge($data, [
            'startDate' => $startDate,
            'endDate' => $endDate
        ]));
    }

    /**
     * Laporan Pelanggan (Customer Report)
     */
    public function customers(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::today()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::today()->endOfMonth()->format('Y-m-d'));

        $data = $this->reportService->getCustomerReport($startDate, $endDate);
        $topCustomers = $this->reportService->getTopCustomers($startDate, $endDate, 5);

        return view('admin.reports.customers', array_merge($data, [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'topCustomers' => $topCustomers
        ]));
    }

    /**
     * Laporan Poin Loyalty (Points Report)
     */
    public function points(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::today()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::today()->endOfMonth()->format('Y-m-d'));

        $data = $this->reportService->getPointsReport($startDate, $endDate);

        return view('admin.reports.points', array_merge($data, [
            'startDate' => $startDate,
            'endDate' => $endDate
        ]));
    }

    /**
     * Export Reports (Excel/PDF)
     */
    public function export(Request $request)
    {
        $type = $request->input('type', 'sales'); // sales, stock, profit_loss
        $format = $request->input('format', 'excel'); // excel, pdf

        $startDate = $request->input('start_date', Carbon::today()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::today()->endOfMonth()->format('Y-m-d'));

        $fileName = "{$type}_report_{$startDate}_{$endDate}";

        if ($type === 'sales') {
            $data = $this->reportService->getSalesReport($startDate, $endDate, $request->all());
            $export = new \App\Exports\SalesReportExport($data['transactions']);
            $fileName = "sales_report_{$startDate}_to_{$endDate}";
        } elseif ($type === 'stock') {
            $categoryId = $request->input('category_id');
            $data = $this->reportService->getStockValueReport($categoryId);
            $export = new \App\Exports\StockReportExport($data['products']);
            $fileName = "stock_report_" . date('Y-m-d');
        } elseif ($type === 'profit_loss') {
            $data = $this->reportService->getProfitLossReport($startDate, $endDate);
            $export = new \App\Exports\ProfitLossExport($data);
            $fileName = "profit_loss_{$startDate}_to_{$endDate}";
        } else {
            return back()->with('error', 'Invalid report type');
        }

        if ($format === 'pdf') {
            // Use DOMPDF
            // For now, Maatwebsite Excel supports PDF via Dompdf if installed
            return \Maatwebsite\Excel\Facades\Excel::download($export, "{$fileName}.pdf", \Maatwebsite\Excel\Excel::DOMPDF);
        }

        return \Maatwebsite\Excel\Facades\Excel::download($export, "{$fileName}.xlsx");
    }
}
