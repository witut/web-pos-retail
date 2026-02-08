<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class StockReportExport extends BaseExport implements WithColumnFormatting
{
    public function headings(): array
    {
        return [
            'SKU',
            'Product Name',
            'Category',
            'Stock On Hand',
            'Unit',
            'Cost Price (HPP)',
            'Total Value',
        ];
    }

    public function map($row): array
    {
        return [
            $row->sku,
            $row->name,
            $row->category->name ?? '-',
            $row->stock_on_hand,
            $row->base_unit,
            $row->cost_price,
            $row->stock_on_hand * $row->cost_price,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'D' => NumberFormat::FORMAT_NUMBER,
            'F' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'G' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
        ];
    }
}
