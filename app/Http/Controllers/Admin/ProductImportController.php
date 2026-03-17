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
            $file   = $request->file('file');
            $import = new ProductImport();

            Excel::import($import, $file);

            $summary = $import->getSummary();

            if ($summary['failed'] > 0) {
                // Simpan failed rows ke temp JSON file (menghindari session size limit)
                $errorFilePath = null;
                if (!empty($import->failedRows)) {
                    $dir = storage_path('app/imports/errors');
                    if (!is_dir($dir)) {
                        mkdir($dir, 0755, true);
                    }
                    $errorFilePath = $dir . '/errors_' . time() . '_' . auth()->id() . '.json';
                    file_put_contents($errorFilePath, json_encode($import->failedRows));
                }

                // Simpan path ke REGULAR session (bukan flash) agar tetap ada saat tombol download diklik
                if ($errorFilePath) {
                    session()->put('error_file_path', $errorFilePath);
                }

                return redirect()->back()
                    ->with('warning', "Import selesai. Produk baru: {$summary['success']}, Diupdate: {$summary['updated']}, Unit ditambahkan: {$summary['uom_added']}, Gagal: {$summary['failed']}")
                    ->with('import_errors', $summary['errors'])
                    ->with('has_error_file', !empty($errorFilePath)); // flag flash untuk tampilkan tombol di view
            }

            return redirect()->route('admin.products.index')
                ->with('success', "Import berhasil! Produk baru: {$summary['success']}, Diupdate: {$summary['updated']}, Unit ditambahkan: {$summary['uom_added']}");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal import produk: ' . $e->getMessage());
        }
    }

    /**
     * Download Excel berisi data-data yang gagal diimport beserta keterangannya.
     * File JSON temp dibersihkan setelah didownload.
     */
    public function downloadErrorFile(Request $request)
    {
        // Baca dari regular session (bukan flash) — tetap ada sampai diforget
        $filePath = session('error_file_path');
        session()->forget('error_file_path'); // hapus setelah dibaca

        if (!$filePath || !file_exists($filePath)) {
            return redirect()->back()->with('error', 'File error tidak ditemukan. Silakan lakukan import ulang terlebih dahulu.');
        }

        $failedRows = json_decode(file_get_contents($filePath), true);

        if (empty($failedRows)) {
            unlink($filePath);
            return redirect()->back()->with('error', 'Tidak ada data error untuk diunduh.');
        }

        // Build array for Excel: header + data rows
        $headers = [
            'No. Baris', 'SKU', 'Nama Produk', 'Product Type', 'Kategori', 'Brand',
            'Harga Pokok (HPP)', 'Harga Jual', 'Stok Awal', 'Min Stock Alert',
            'Konversi', 'Unit Dasar', 'Barcode', 'Status', 'Deskripsi',
            '⚠ Keterangan Error',
        ];

        $dataRows = array_map(fn($r) => [
            $r['baris'],
            $r['sku'],
            $r['nama_produk'],
            $r['product_type'],
            $r['kategori'],
            $r['brand'],
            $r['harga_pokok_hpp'],
            $r['harga_jual'],
            $r['stok_awal'],
            $r['min_stock_alert'],
            $r['konversi'],
            $r['unit_dasar'],
            $r['barcode'],
            $r['status'],
            $r['deskripsi'],
            $r['keterangan_error'],
        ], $failedRows);

        $allData    = array_merge([$headers], $dataRows);
        $totalRows  = count($failedRows);
        $errorPath  = $filePath; // capture for closure

        $export = new class ($allData, $totalRows, $errorPath) implements
            \Maatwebsite\Excel\Concerns\FromArray,
            \Maatwebsite\Excel\Concerns\WithStyles,
            \Maatwebsite\Excel\Concerns\WithColumnWidths
        {
            public function __construct(
                private array $data,
                private int $totalRows,
                private string $errorPath
            ) {}

            public function array(): array { return $this->data; }

            public function columnWidths(): array
            {
                return [
                    'A' => 10, 'B' => 22, 'C' => 30, 'D' => 14, 'E' => 14, 'F' => 14,
                    'G' => 18, 'H' => 14, 'I' => 12, 'J' => 18,
                    'K' => 12, 'L' => 12, 'M' => 20, 'N' => 10, 'O' => 28,
                    'P' => 55,
                ];
            }

            public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
            {
                // Header row: dark red background, white bold
                $sheet->getStyle('A1:P1')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => [
                        'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '7F1D1D'],
                    ],
                ]);

                // Data rows: light red/pink tint
                if ($this->totalRows > 0) {
                    $sheet->getStyle('A2:P' . ($this->totalRows + 1))->applyFromArray([
                        'fill' => [
                            'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'FFF1F2'],
                        ],
                    ]);
                    // Error column: slightly darker
                    $sheet->getStyle('P2:P' . ($this->totalRows + 1))->applyFromArray([
                        'font' => ['color' => ['rgb' => 'B91C1C'], 'bold' => true],
                        'fill' => [
                            'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'FEE2E2'],
                        ],
                        'alignment' => ['wrapText' => true],
                    ]);
                }

                // Freeze header row
                $sheet->freezePane('A2');

                return [];
            }
        };

        $filename = 'import_errors_' . date('Y-m-d_His') . '.xlsx';

        // Delete temp JSON after download
        @unlink($filePath);

        return Excel::download($export, $filename);
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
     * Download import template (14 kolom dengan contoh multi-UOM)
     */
    public function downloadTemplate()
    {
        try {
            $data = [
                // Headers (14 kolom)
                [
                    'SKU', 'Nama Produk', 'Product Type', 'Kategori', 'Brand',
                    'Harga Pokok (HPP)', 'Harga Jual', 'Stok Awal', 'Min Stock Alert',
                    'Konversi', 'Unit Dasar', 'Barcode', 'Status', 'Deskripsi',
                ],
                // Baris induk (Konversi=1, Unit=PCS)
                [
                    'PROD-001', 'Contoh Produk Snack', 'inventory', 'Snack', 'Brand A',
                    1700, 2000, 100, 10,
                    1, 'PCS', '8991234567890', 'active', 'Deskripsi produk'
                ],
                // Baris UOM tambahan (Konversi=10, Unit=RTG, SKU sama)
                [
                    'PROD-001', 'Contoh Produk Snack', 'inventory', 'Snack', 'Brand A',
                    17000, 19000, 0, 5,
                    10, 'RTG', '', 'active', ''
                ],
                // Produk baru - service (tidak perlu stok, konversi=1)
                [
                    'SRV-001', 'Contoh Produk Jasa', 'service', 'Jasa', '',
                    50000, 100000, 0, 0,
                    1, 'PCS', '', 'active', 'Produk jasa tidak perlu stok'
                ],
            ];

            $templateExport = new class ($data) implements
                \Maatwebsite\Excel\Concerns\FromArray,
                \Maatwebsite\Excel\Concerns\WithStyles,
                \Maatwebsite\Excel\Concerns\WithColumnWidths
            {
                protected array $data;

                public function __construct(array $data)
                {
                    $this->data = $data;
                }

                public function array(): array
                {
                    return $this->data;
                }

                public function columnWidths(): array
                {
                    return [
                        'A' => 20, 'B' => 30, 'C' => 14, 'D' => 14, 'E' => 14,
                        'F' => 18, 'G' => 14, 'H' => 12, 'I' => 18,
                        'J' => 12, 'K' => 12, 'L' => 20, 'M' => 10, 'N' => 30,
                    ];
                }

                public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
                {
                    return [
                        // Header row: dark background, white bold text
                        1 => [
                            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                            'fill' => [
                                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => ['rgb' => '1E3A5F'],
                            ],
                        ],
                        // Baris UOM tambahan (baris 3): light yellow background sebagai petunjuk visual
                        3 => [
                            'fill' => [
                                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'FFFBEB'],
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
