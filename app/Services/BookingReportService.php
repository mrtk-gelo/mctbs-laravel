<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\TransientHouse;
use App\Models\Unit;
use App\Support\BookingStatus;
use App\Support\PaymentStatus;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class BookingReportService
{
    /**
     * @param  Collection<int, int>  $houseIds
     * @param  array{period: string, year: int, quarter: string, house_id: ?int, from_date: string, to_date: string}  $filters
     * @return array<string, mixed>
     */
    public function generate(Collection $houseIds, array $filters): array
    {
        $visibleHouseIds = $filters['house_id'] === null
            ? $houseIds
            : $houseIds->filter(fn (int $houseId): bool => $houseId === $filters['house_id'])->values();
        $from = Carbon::parse($filters['from_date'])->startOfDay();
        $to = Carbon::parse($filters['to_date'])->endOfDay();
        $payments = Payment::query()
            ->with(['booking.unit'])
            ->where('payment_status', PaymentStatus::VERIFIED)
            ->whereBetween('payment_date', [$from, $to])
            ->whereHas('booking.unit', fn ($query) => $query->whereIn('house_id', $visibleHouseIds))
            ->get();
        $refunds = Refund::query()
            ->with(['booking.unit'])
            ->where('refund_status', 'Refunded')
            ->whereBetween('processed_at', [$from, $to])
            ->whereHas('payment', fn ($query) => $query->where('payment_status', PaymentStatus::VERIFIED))
            ->whereHas('booking.unit', fn ($query) => $query->whereIn('house_id', $visibleHouseIds))
            ->get();
        $houseRecords = TransientHouse::query()
            ->whereIn('house_id', $visibleHouseIds)
            ->orderBy('name')
            ->get();
        $units = Unit::query()
            ->with('house')
            ->whereIn('house_id', $visibleHouseIds)
            ->orderBy('unit_type')
            ->get();
        $bookingScope = Booking::query()
            ->with(['unit.house', 'guest'])
            ->whereHas('unit', fn ($query) => $query->whereIn('house_id', $visibleHouseIds));
        $activityBookings = (clone $bookingScope)
            ->whereIn('status', [
                BookingStatus::CONFIRMED,
                BookingStatus::CHECKED_IN,
                ...BookingStatus::completedStatuses(),
            ])
            ->whereDate('check_in', '<=', $to->toDateString())
            ->whereDate('check_out', '>', $from->toDateString())
            ->get();
        $createdBookings = (clone $bookingScope)
            ->whereIn('status', [
                BookingStatus::PENDING,
                BookingStatus::ACCEPTED,
                BookingStatus::CONFIRMED,
                BookingStatus::CHECKED_IN,
                ...BookingStatus::completedStatuses(),
            ])
            ->whereDate('check_in', '>=', $from->toDateString())
            ->whereDate('check_in', '<=', $to->toDateString())
            ->get();

        $revenueByPeriodRows = $this->revenueByPeriodRows($payments, $refunds, $filters['period'], $from, $to);
        $revenueByHouse = $this->revenueRows(
            $payments,
            $refunds,
            $houseRecords,
            fn ($record): int => $record->booking->unit->house_id,
            fn ($house): array => ['house' => $house->name]
        );
        $revenueByUnit = $this->revenueRows(
            $payments,
            $refunds,
            $units,
            fn ($record): int => $record->booking->unit_id,
            fn ($unit): array => ['unit' => $unit->unit_type, 'house' => $unit->house->name]
        );
        $quarterRevenueRows = $this->quarterRevenueRows($payments, $refunds, $filters['year'], $from, $to);
        $activityRows = $this->activityRows($houseRecords, $activityBookings, $createdBookings, $from, $to);
        $totalReceived = (float) $payments->sum('amount');
        $totalRefunded = (float) $refunds->sum('amount');
        $periodLabel = ucfirst($filters['period']).' revenue from '.$from->format('M j, Y').' to '.$to->format('M j, Y');

        return [
            'period' => $filters['period'],
            'periodLabel' => $periodLabel,
            'selectedYear' => $filters['year'],
            'selectedQuarter' => $filters['quarter'],
            'selectedHouseId' => $filters['house_id'],
            'selectedHouseName' => $filters['house_id'] === null
                ? 'All my houses'
                : ($houseRecords->firstWhere('house_id', $filters['house_id'])?->name ?? 'Selected house'),
            'fromDate' => $from->toDateString(),
            'toDate' => $to->toDateString(),
            'generatedAt' => Carbon::now('Asia/Manila')->format('M j, Y g:i A'),
            'totalRevenue' => $totalReceived - $totalRefunded,
            'totalReceived' => $totalReceived,
            'totalRefunded' => $totalRefunded,
            'totalBookings' => $createdBookings->count(),
            'completedBookings' => $createdBookings->filter(
                fn (Booking $booking): bool => BookingStatus::isTerminal($booking->status)
            )->count(),
            'pendingBookings' => $createdBookings->where('status', BookingStatus::PENDING)->count(),
            'revenueByPeriodRows' => $revenueByPeriodRows,
            'revenueByHouse' => $revenueByHouse,
            'revenueByUnit' => $revenueByUnit,
            'quarterRevenueRows' => $quarterRevenueRows,
            'activityRows' => $activityRows,
            'paymentMethods' => $payments
                ->groupBy(fn (Payment $payment): string => $payment->payment_method ?: 'Other')
                ->map(fn (Collection $methodPayments): float => (float) $methodPayments->sum('amount'))
                ->sortKeys(),
            'payments' => $payments,
            'refunds' => $refunds,
            'periodBookings' => $createdBookings,
            'chartRows' => collect($revenueByPeriodRows)->map(fn (array $row): array => [
                'label' => $row['period'],
                'revenue' => $row['revenue'],
            ]),
        ];
    }

    /**
     * @param  Collection<int, Payment>  $payments
     * @param  Collection<int, Refund>  $refunds
     * @return array<int, array{period: string, payments: float, refunds: float, revenue: float}>
     */
    private function revenueByPeriodRows(Collection $payments, Collection $refunds, string $period, Carbon $from, Carbon $to): array
    {
        $keys = [];
        $cursor = match ($period) {
            'weekly' => $from->copy()->startOfWeek(Carbon::MONDAY),
            'annually' => $from->copy()->startOfYear(),
            default => $from->copy()->startOfMonth(),
        };

        while ($cursor->lte($to)) {
            $keys[$this->periodKey($cursor, $period)] = $this->periodLabel($cursor, $period);
            $cursor = match ($period) {
                'weekly' => $cursor->addWeek(),
                'annually' => $cursor->addYear(),
                default => $cursor->addMonth(),
            };
        }

        $receivedByKey = $payments->groupBy(fn (Payment $payment): string => $this->periodKey($payment->payment_date, $period))
            ->map(fn (Collection $rows): float => (float) $rows->sum('amount'));
        $refundsByKey = $refunds->groupBy(fn (Refund $refund): string => $this->periodKey($refund->processed_at, $period))
            ->map(fn (Collection $rows): float => (float) $rows->sum('amount'));

        return collect($keys)->map(function (string $label, string $key) use ($receivedByKey, $refundsByKey): array {
            $received = $receivedByKey->get($key, 0);
            $refunded = $refundsByKey->get($key, 0);

            return [
                'period' => $label,
                'payments' => $received,
                'refunds' => $refunded,
                'revenue' => $received - $refunded,
            ];
        })->values()->all();
    }

    private function periodKey(Carbon $date, string $period): string
    {
        return match ($period) {
            'weekly' => $date->copy()->startOfWeek(Carbon::MONDAY)->toDateString(),
            'annually' => $date->format('Y'),
            default => $date->format('Y-m'),
        };
    }

    private function periodLabel(Carbon $date, string $period): string
    {
        return match ($period) {
            'weekly' => 'Week of '.$date->copy()->startOfWeek(Carbon::MONDAY)->format('M j, Y'),
            'annually' => $date->format('Y'),
            default => $date->format('F Y'),
        };
    }

    /**
     * @param  Collection<int, Payment>  $payments
     * @param  Collection<int, Refund>  $refunds
     * @param  Collection<int, mixed>  $records
     * @param  callable(Payment|Refund): int  $recordKey
     * @param  callable(mixed): array<string, string>  $recordLabels
     * @return array<int, array<string, mixed>>
     */
    private function revenueRows(
        Collection $payments,
        Collection $refunds,
        Collection $records,
        callable $recordKey,
        callable $recordLabels
    ): array {
        $totals = [];

        foreach ([$payments, $refunds] as $rows) {
            foreach ($rows as $row) {
                $key = $recordKey($row);
                $totals[$key] ??= ['payments' => 0.0, 'refunds' => 0.0];
                $totals[$key][$row instanceof Payment ? 'payments' : 'refunds'] += (float) $row->amount;
            }
        }

        return $records->map(function ($record) use ($recordLabels, $totals): array {
            $values = $totals[$record->getKey()] ?? ['payments' => 0.0, 'refunds' => 0.0];

            return [
                ...$recordLabels($record),
                'payments' => $values['payments'],
                'refunds' => $values['refunds'],
                'revenue' => $values['payments'] - $values['refunds'],
            ];
        })->values()->all();
    }

    /**
     * @param  Collection<int, Payment>  $payments
     * @param  Collection<int, Refund>  $refunds
     * @return array<int, array{quarter: string, payments: float, refunds: float, revenue: float}>
     */
    private function quarterRevenueRows(Collection $payments, Collection $refunds, int $year, Carbon $from, Carbon $to): array
    {
        $rows = [];

        foreach (range(1, 4) as $quarter) {
            $startMonth = (($quarter - 1) * 3) + 1;
            $quarterStart = Carbon::createFromDate($year, $startMonth, 1)->startOfDay();
            $quarterEnd = $quarterStart->copy()->addMonths(3)->subDay()->endOfDay();
            $rangeStart = $quarterStart->greaterThan($from) ? $quarterStart : $from;
            $rangeEnd = $quarterEnd->lessThan($to) ? $quarterEnd : $to;

            if ($rangeStart->greaterThan($rangeEnd)) {
                $rows[] = ['quarter' => 'Q'.$quarter, 'payments' => 0.0, 'refunds' => 0.0, 'revenue' => 0.0];

                continue;
            }

            $received = (float) $payments
                ->filter(fn (Payment $payment): bool => $payment->payment_date->between($rangeStart, $rangeEnd))
                ->sum('amount');
            $refunded = (float) $refunds
                ->filter(fn (Refund $refund): bool => $refund->processed_at->between($rangeStart, $rangeEnd))
                ->sum('amount');
            $rows[] = [
                'quarter' => 'Q'.$quarter,
                'payments' => $received,
                'refunds' => $refunded,
                'revenue' => $received - $refunded,
            ];
        }

        return $rows;
    }

    /**
     * @param  Collection<int, TransientHouse>  $houses
     * @param  Collection<int, Booking>  $activityBookings
     * @param  Collection<int, Booking>  $createdBookings
     * @return array<int, array<string, mixed>>
     */
    private function activityRows(
        Collection $houses,
        Collection $activityBookings,
        Collection $createdBookings,
        Carbon $from,
        Carbon $to
    ): array {
        return $houses->map(function (TransientHouse $house) use ($activityBookings, $createdBookings, $from, $to): array {
            $houseBookings = $createdBookings->filter(
                fn (Booking $booking): bool => $booking->unit->house_id === $house->house_id
            );
            $monthCounts = $houseBookings
                ->groupBy(fn (Booking $booking): string => $booking->check_in->format('Y-m'))
                ->map(fn (Collection $bookings): int => $bookings->count());
            $days = [];

            foreach ($activityBookings->filter(
                fn (Booking $booking): bool => $booking->unit->house_id === $house->house_id
            ) as $booking) {
                $firstNight = $booking->check_in->copy()->startOfDay();
                $lastNight = $booking->check_out->copy()->startOfDay()->subDay();

                if ($firstNight->lt($from)) {
                    $firstNight = $from->copy()->startOfDay();
                }
                if ($lastNight->gt($to)) {
                    $lastNight = $to->copy()->startOfDay();
                }

                for ($night = $firstNight; $night->lte($lastNight); $night->addDay()) {
                    $date = $night->toDateString();
                    $days[$date] = ($days[$date] ?? 0) + 1;
                }
            }

            uksort($days, fn (string $left, string $right): int => $days[$right] <=> $days[$left] ?: strcmp($left, $right));
            $topDates = collect($days)
                ->filter(fn (int $count): bool => $count > 0)
                ->take(5)
                ->map(fn (int $count, string $date): array => ['date' => $date, 'bookings' => $count])
                ->values()
                ->all();
            $busiestMonth = $monthCounts->isEmpty()
                ? null
                : $monthCounts->keys()->sort()->first();
            if ($monthCounts->isNotEmpty()) {
                $monthCountsByCount = $monthCounts->all();
                uksort($monthCountsByCount, fn (string $left, string $right): int => $monthCountsByCount[$right] <=> $monthCountsByCount[$left] ?: strcmp($left, $right));
                $busiestMonth = array_key_first($monthCountsByCount);
            }

            return [
                'house' => $house->name,
                'most_booked_month' => $busiestMonth === null
                    ? 'No bookings'
                    : Carbon::createFromFormat('Y-m', $busiestMonth)->format('F Y'),
                'month_bookings' => (int) $monthCounts->max(),
                'top_dates' => $topDates,
            ];
        })->values()->all();
    }
}
