<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Illuminate\Support\Collection;

class ProfitLossExport extends BaseExport implements WithColumnFormatting
{
    // Override __construct because data structure is different (array vs collection)
    public function __construct($data, $startDate = null, $endDate = null)
    {
        // Convert summary array to collection for export
        // We will export the Daily Breakdown
        parent::__construct(collect($data['daily_breakdown']), $startDate, $endDate);

        $this->summary = $data['summary'];
    }

    public function headings(): array
    {
        return [
            'Date',
            'Revenue (Omzet)',
            'COGS (HPP)',
            'Gross Profit (Laba Kotor)',
            'Margin %',
        ];
    }

    public function map($row): array
    {
        // $row is an array from daily_breakdown
        return [
            $row['date'],
            $row['revenue'],
            $row['cogs'],
            $row['profit'],
            $row['revenue'] > 0 ? ($row['profit'] / $row['revenue']) : 0,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'C' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'D' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'E' => NumberFormat::FORMAT_PERCENTAGE_00,
        ];
    }
}
