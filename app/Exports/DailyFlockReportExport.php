<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DailyFlockReportExport implements FromArray, WithColumnWidths, WithEvents, WithHeadings, WithStyles
{
    protected $data;

    protected $filters;

    public function __construct($data, $filters = [])
    {
        $this->data = $data;
        $this->filters = $filters;
    }

    public function array(): array
    {
        return $this->data['batches'] ?? [];
    }

    public function headings(): array
    {
        return [
            'Delivery Date',
            'Breed Type',
            'Batch No',
            'Quantity',
            'Age (Days)',
            'Body Weight (g)',
            'Starter Feed (kg)',
            'Grower Feed (kg)',
            'Layer Feed (kg)',
            'Water Consumption (L)',
            'Water Quality',
            'Eggs Produced',
            'Grade A',
            'Grade B',
            'Mortality Count',
            'Mortality %',
            'Remarks',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15, // Delivery Date
            'B' => 15, // Breed Type
            'C' => 12, // Batch No
            'D' => 10, // Quantity
            'E' => 12, // Age
            'F' => 15, // Body Weight
            'G' => 15, // Starter Feed
            'H' => 15, // Grower Feed
            'I' => 15, // Layer Feed
            'J' => 18, // Water Consumption
            'K' => 15, // Water Quality
            'L' => 15, // Eggs Produced
            'M' => 12, // Grade A
            'N' => 12, // Grade B
            'O' => 15, // Mortality Count
            'P' => 12, // Mortality %
            'Q' => 20, // Remarks
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Header row styling
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E0E0E0'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Get the data range
                $lastRow = $sheet->getHighestRow();
                $lastColumn = $sheet->getHighestColumn();

                // Add borders to all data cells
                $sheet->getStyle('A1:'.$lastColumn.$lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);

                // Center align all data cells
                $sheet->getStyle('A1:'.$lastColumn.$lastRow)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                // Add totals row if data exists
                if ($lastRow > 1) {
                    $totalsRow = $lastRow + 1;

                    // Add totals row
                    $sheet->setCellValue('A'.$totalsRow, 'TOTALS');
                    $sheet->setCellValue('D'.$totalsRow, $this->data['totals']['total_qty'] ?? 0);
                    $sheet->setCellValue('G'.$totalsRow, $this->data['totals']['total_starter'] ?? 0);
                    $sheet->setCellValue('H'.$totalsRow, $this->data['totals']['total_grower'] ?? 0);
                    $sheet->setCellValue('I'.$totalsRow, $this->data['totals']['total_layer'] ?? 0);
                    $sheet->setCellValue('J'.$totalsRow, $this->data['totals']['total_water'] ?? 0);
                    $sheet->setCellValue('L'.$totalsRow, $this->data['totals']['total_eggs'] ?? 0);
                    $sheet->setCellValue('M'.$totalsRow, $this->data['totals']['total_grade_a'] ?? 0);
                    $sheet->setCellValue('N'.$totalsRow, $this->data['totals']['total_grade_b'] ?? 0);
                    $sheet->setCellValue('O'.$totalsRow, $this->data['totals']['total_mortality'] ?? 0);
                    $sheet->setCellValue('P'.$totalsRow, ($this->data['totals']['avg_mortality'] ?? 0).'%');

                    // Style the totals row
                    $sheet->getStyle('A'.$totalsRow.':'.$lastColumn.$totalsRow)->applyFromArray([
                        'font' => ['bold' => true],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'F0F0F0'],
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                    ]);
                }

                // Add report header information
                $sheet->insertNewRowBefore(1, 4);

                // Company info
                $sheet->setCellValue('A1', 'Company: '.($this->data['from_company'] ?? 'N/A'));
                $sheet->setCellValue('A2', 'Report: Daily Flock Report');

                // Date period
                if (isset($this->filters['date_from']) && isset($this->filters['date_to']) &&
                    ! empty($this->filters['date_from']) && ! empty($this->filters['date_to'])) {
                    $sheet->setCellValue('A3', 'Period: '.$this->filters['date_from'].' to '.$this->filters['date_to']);
                } else {
                    $sheet->setCellValue('A3', 'Report Date: '.now()->format('Y-m-d'));
                }

                // Style the header
                $sheet->getStyle('A1:A3')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                ]);

                // Adjust column widths for header
                $sheet->getColumnDimension('A')->setWidth(30);
            },
        ];
    }
}
