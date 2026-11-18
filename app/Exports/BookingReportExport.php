<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class BookingReportExport implements WithMultipleSheets
{
    /**
     * @param  array<string, mixed>  $report
     */
    public function __construct(private array $report) {}

    /**
     * @return array<int, BookingReportSheetExport>
     */
    public function sheets(): array
    {
        $sheets = [];

        $periodRows = array_map(fn (array $row): array => [
            $row['period'],
            (float) $row['payments'],
            (float) -$row['refunds'],
            (float) $row['revenue'],
        ], $this->report['revenueByPeriodRows']);
        $sheets[] = $this->financialSheet(
            'Revenue',
            [ucfirst($this->report['period']), 'Payments received', 'Refunds', 'Net Revenue (after refunds)'],
            $periodRows,
            true
        );

        $houseRows = array_map(fn (array $row): array => [
            $row['house'],
            (float) $row['payments'],
            (float) -$row['refunds'],
            (float) $row['revenue'],
        ], $this->report['revenueByHouse']);
        $sheets[] = $this->financialSheet(
            'Revenue per House',
            ['House', 'Payments received', 'Refunds', 'Net Revenue (after refunds)'],
            $houseRows
        );

        $unitRows = array_map(fn (array $row): array => [
            $row['unit'],
            $row['house'],
            (float) $row['payments'],
            (float) -$row['refunds'],
            (float) $row['revenue'],
        ], $this->report['revenueByUnit']);
        $sheets[] = $this->financialSheet(
            'Revenue per Unit',
            ['Unit', 'House', 'Payments received', 'Refunds', 'Net Revenue (after refunds)'],
            $unitRows
        );

        if ($this->report['period'] === 'annually') {
            $quarterRows = array_map(fn (array $row): array => [
                $row['quarter'],
                (float) $row['payments'],
                (float) -$row['refunds'],
                (float) $row['revenue'],
            ], $this->report['quarterRevenueRows']);
            $sheets[] = $this->financialSheet(
                'Revenue per Quarter',
                ['Quarter', 'Payments received', 'Refunds', 'Net Revenue (after refunds)'],
                $quarterRows
            );
        }

        $activityRows = array_map(function (array $row): array {
            $topDates = collect($row['top_dates'])
                ->map(fn (array $date): string => $date['date'].' ('.$date['bookings'].')')
                ->implode(', ');

            return [
                $row['house'],
                $row['most_booked_month'],
                $row['month_bookings'],
                $topDates ?: 'No bookings',
            ];
        }, $this->report['activityRows']);
        $sheets[] = new BookingReportSheetExport(
            'Booking Activity',
            $this->report,
            ['House', 'Month with most check-ins', 'Check-ins in busiest month', 'Top booked dates'],
            $activityRows,
            [30, 28, 25, 60]
        );

        return $sheets;
    }

    /**
     * @param  array<int, string>  $headings
     * @param  array<int, array<int, string|int|float|null>>  $rows
     */
    private function financialSheet(string $title, array $headings, array $rows, bool $showRefundSummary = false): BookingReportSheetExport
    {
        return new BookingReportSheetExport(
            $title,
            $this->report,
            $headings,
            $rows,
            count($headings) === 5 ? [28, 28, 20, 18, 30] : [28, 22, 18, 30],
            count($headings) === 5 ? [3, 4, 5] : [2, 3, 4],
            [count($headings) === 5 ? 4 : 3],
            $this->totalsRow($rows, count($headings)),
            $showRefundSummary && $this->report['totalRefunded'] > 0
                ? ['Refunds: -₱'.number_format($this->report['totalRefunded'], 2)]
                : null
        );
    }

    /**
     * @param  array<int, array<int, string|int|float|null>>  $rows
     * @return array<int, string|float>
     */
    private function totalsRow(array $rows, int $columnCount): array
    {
        $totals = array_fill(0, $columnCount, '');
        $totals[0] = 'Total';

        foreach (range(1, $columnCount - 1) as $column) {
            if ($column === 1 && $columnCount === 5) {
                continue;
            }

            $totals[$column] = array_sum(array_column($rows, $column));
        }

        return $totals;
    }
}
