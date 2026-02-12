<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;

class GenerateProductTemplate extends Command
{
    protected $signature = 'product:generate-template';
    protected $description = 'Generate product import template Excel file';

    public function handle()
    {
        $templatePath = storage_path('app/templates');

        // Ensure directory exists
        if (!file_exists($templatePath)) {
            mkdir($templatePath, 0755, true);
        }

        // Template data: headers + 2 example rows
        $data = [
            // Headers
            ['SKU', 'Nama Produk', 'Product Type', 'Kategori', 'Brand', 'Harga Pokok (HPP)', 'Harga Jual', 'Stok Awal', 'Min Stock Alert', 'Unit Dasar', 'Barcode', 'Status', 'Deskripsi'],
            // Example 1: Inventory product
            ['PROD-001', 'Contoh Produk Inventory', 'inventory', 'Elektronik', 'Samsung', '100000', '150000', '10', '5', 'PCS', '1234567890', 'active', 'Contoh deskripsi produk inventory'],
            // Example 2: Service product
            ['SRV-001', 'Contoh Produk Jasa', 'service', 'Jasa', '', '50000', '100000', '', '', 'PCS', '', 'active', 'Contoh deskripsi produk jasa - stok tidak perlu diisi'],
        ];

        try {
            Excel::store(
                new class ($data) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithStyles {
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
                },
                'templates/product_import_template.xlsx'
            );

            $this->info('Template berhasil dibuat di: ' . $templatePath . '/product_import_template.xlsx');
            return 0;
        } catch (\Exception $e) {
            $this->error('Gagal membuat template: ' . $e->getMessage());
            return 1;
        }
    }
}
