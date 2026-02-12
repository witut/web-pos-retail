<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ProductExport;
use App\Http\Controllers\Controller;
use App\Imports\ProductImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ProductImportController extends Controller
{
    /**
     * Show import page
     */
    public function index()
    {
        return view('admin.products.import');
    }

    /**
     * Process product import
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120', // Max 5MB
        ]);

        try {
            $file = $request->file('file');
            $import = new ProductImport();

            Excel::import($import, $file);

            $summary = $import->getSummary();

            if ($summary['failed'] > 0) {
                return redirect()->back()
                    ->with('warning', "Import selesai dengan beberapa error. Berhasil: {$summary['success']}, Diupdate: {$summary['updated']}, Gagal: {$summary['failed']}")
                    ->with('import_errors', $summary['errors']);
            }

            return redirect()->route('admin.products.index')
                ->with('success', "Import berhasil! Produk baru: {$summary['success']}, Diupdate: {$summary['updated']}");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal import produk: ' . $e->getMessage());
        }
    }

    /**
     * Export products to Excel
     */
    public function export()
    {
        try {
            $filename = 'products_export_' . date('Y-m-d_His') . '.xlsx';

            return Excel::download(new ProductExport(), $filename);

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal export produk: ' . $e->getMessage());
        }
    }

    /**
     * Download import template
     */
    public function downloadTemplate()
    {
        try {
            // Template data: headers + 2 example rows
            $data = [
                // Headers
                ['SKU', 'Nama Produk', 'Product Type', 'Kategori', 'Brand', 'Harga Pokok (HPP)', 'Harga Jual', 'Stok Awal', 'Min Stock Alert', 'Unit Dasar', 'Barcode', 'Status', 'Deskripsi'],
                // Example 1: Inventory product
                ['PROD-001', 'Contoh Produk Inventory', 'inventory', 'Elektronik', 'Samsung', 100000, 150000, 10, 5, 'PCS', '1234567890', 'active', 'Contoh deskripsi produk inventory'],
                // Example 2: Service product
                ['SRV-001', 'Contoh Produk Jasa', 'service', 'Jasa', '', 50000, 100000, '', '', 'PCS', '', 'active', 'Contoh deskripsi produk jasa - stok tidak perlu diisi'],
            ];

            $templateExport = new class ($data) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithStyles {
                protected $data;

                public function __construct(array $data)
                {
                    $this->data = $data;
                }

                public function array(): array
                {
                    return $this->data;
                }

                public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
                {
                    return [
                    1 => [
                        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                        'fill' => [
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => ['rgb' => '4A5568'],
                            ],
                        ],
                    ];
                }
            };

            return Excel::download($templateExport, 'product_import_template.xlsx');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal download template: ' . $e->getMessage());
        }
    }
}
