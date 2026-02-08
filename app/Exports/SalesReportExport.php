<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesReportExport extends BaseExport implements WithColumnFormatting
{
    public function headings(): array
    {
        return [
            'Invoice Number',
            'Date',
            'Cashier',
            'Payment Method',
            'Total Items',
            'Subtotal',
            'Tax',
            'Total',
            'Status'
        ];
    }

    public function map($row): array
    {
        return [
            $row->invoice_number,
            $row->transaction_date->format('Y-m-d H:i'),
            $row->cashier->name ?? 'Unknown',
            ucfirst($row->payment_method),
            $row->items->sum('qty'),
            $row->subtotal,
            $row->tax_amount,
            $row->total,
            ucfirst($row->status),
        ];
    }

    public function columnFormats(): array
    {
        return [
            'F' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Subtotal
            'G' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Tax
            'H' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Total
        ];
    }
}
