<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    /**
     * Get products to export
     */
    public function collection()
    {
        return Product::with(['category', 'barcodes'])
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    /**
     * Define headers
     */
    public function headings(): array
    {
        return [
            'SKU',
            'Nama Produk',
            'Product Type',
            'Kategori',
            'Brand',
            'Harga Pokok (HPP)',
            'Harga Jual',
            'Stok Awal',
            'Min Stock Alert',
            'Unit Dasar',
            'Barcode',
            'Status',
            'Deskripsi',
        ];
    }

    /**
     * Map each product to row data
     */
    public function map($product): array
    {
        return [
            $product->sku,
            $product->name,
            $product->product_type ?? 'inventory',
            $product->category->name ?? '',
            $product->brand ?? '',
            $product->cost_price ?? 0,
            $product->selling_price,
            $product->product_type === 'service' ? 0 : $product->stock_on_hand,
            $product->product_type === 'service' ? '' : $product->min_stock_alert,
            $product->base_unit,
            $product->barcodes->first()->barcode ?? '',
            $product->status,
            $product->description ?? '',
        ];
    }

    /**
     * Style the worksheet
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4A5568'],
                ],
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
            ],
        ];
    }
}
