<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\DefaultValueBinder;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BookingReportSheetExport extends DefaultValueBinder implements FromArray, WithColumnWidths, WithCustomValueBinder, WithEvents, WithStyles, WithTitle
{
    /**
     * @param  array<string, mixed>  $report
     * @param  array<int, string>  $headings
     * @param  array<int, array<int, string|int|float|null>>  $rows
     * @param  array<int, int>  $moneyColumns
     * @param  array<int, int>  $refundColumns
     * @param  array<int, float|int|string>|null  $totals
     * @param  array<int, string|float>|null  $summaryLine
     * @param  array<int, int>  $columnWidths
     */
    public function __construct(
        private string $sheetTitle,
        private array $report,
        private array $headings,
        private array $rows,
        private array $columnWidths,
        private array $moneyColumns = [],
        private array $refundColumns = [],
        private ?array $totals = null,
        private ?array $summaryLine = null
    ) {}

    /**
     * @return array<int, array<int, string|int|float|null>>
     */
    public function array(): array
    {
        $title = implode(' | ', [
            'MCTBS Booking Report',
            $this->report['selectedHouseName'],
            $this->report['fromDate'].' to '.$this->report['toDate'],
            $this->report['selectedQuarter'],
        ]);
        $subtitle = implode(' | ', [
            'Period: '.ucfirst($this->report['period']),
            'Year: '.$this->report['selectedYear'],
            'Quarter: '.$this->report['selectedQuarter'],
            'House: '.$this->report['selectedHouseName'],
            'Generated: '.$this->report['generatedAt'],
        ]);
        $rows = [
            [$title],
            [$subtitle],
            $this->summaryLine ?? [null],
            $this->headings,
            ...$this->rows,
        ];

        if ($this->totals !== null) {
            $rows[] = $this->totals;
        }

        return $rows;
    }

    public function title(): string
    {
        return $this->sheetTitle;
    }

    /**
     * @return array<string, int>
     */
    public function columnWidths(): array
    {
        $widths = [];

        foreach ($this->columnWidths as $index => $width) {
            $widths[chr(65 + $index)] = $width;
        }

        return $widths;
    }

    /**
     * @return array<string|int, array<string, mixed>>
     */
    public function styles(Worksheet $sheet): array
    {
        $lastColumn = chr(64 + count($this->headings));
        $lastRow = count($this->array());
        $styles = [
            'A1:'.$lastColumn.'1' => [
                'font' => ['bold' => true, 'size' => 18, 'color' => ['rgb' => '6B3A14']],
            ],
            'A2:'.$lastColumn.'2' => [
                'font' => ['italic' => true, 'color' => ['rgb' => '666666']],
            ],
            'A4:'.$lastColumn.'4' => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '6B3A14']],
            ],
        ];

        if ($this->summaryLine !== null) {
            $styles['A3:'.$lastColumn.'3'] = [
                'font' => ['size' => 9, 'color' => ['rgb' => 'C00000']],
            ];
        }

        if ($this->totals !== null) {
            $styles[$lastRow] = [
                'font' => ['bold' => true],
                'borders' => ['top' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '6B3A14']]],
            ];
        }

        foreach ($this->moneyColumns as $columnIndex) {
            $column = chr(64 + $columnIndex);
            $styles[$column.'5:'.$column.$lastRow] = [
                'numberFormat' => ['formatCode' => '"₱"#,##0.00;[Red]-"₱"#,##0.00'],
            ];
        }

        foreach ($this->refundColumns as $columnIndex) {
            $column = chr(64 + $columnIndex);
            $styles[$column.'5:'.$column.$lastRow]['font'] = ['color' => ['rgb' => 'C00000']];
        }

        return $styles;
    }

    /**
     * @return array<class-string, callable>
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $lastColumn = chr(64 + count($this->headings));
                $lastDataRow = 4 + count($this->rows);
                $sheet->mergeCells('A1:'.$lastColumn.'1');
                $sheet->mergeCells('A2:'.$lastColumn.'2');
                $sheet->freezePane('A5');

                $lastMoneyRow = count($this->array());
                foreach ($this->moneyColumns as $columnIndex) {
                    $column = chr(64 + $columnIndex);

                    for ($row = 5; $row <= $lastMoneyRow; $row++) {
                        $cell = $sheet->getCell($column.$row);

                        if (is_numeric($cell->getValue())) {
                            $cell->setValueExplicit((float) $cell->getValue(), DataType::TYPE_NUMERIC);
                        }
                    }
                }

                if ($this->rows !== []) {
                    $sheet->setAutoFilter('A4:'.$lastColumn.$lastDataRow);
                }
            },
        ];
    }

    public function bindValue(Cell $cell, mixed $value): bool
    {
        $isMoneyCell = ($cell->getRow() >= 5 && in_array(ord($cell->getColumn()) - 64, $this->moneyColumns, true))
            || ($this->summaryLine !== null && $cell->getCoordinate() === 'B3');

        if ($isMoneyCell && is_numeric($value)) {
            $cell->setValueExplicit((float) $value, DataType::TYPE_NUMERIC);

            return true;
        }

        if (is_string($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }
}
