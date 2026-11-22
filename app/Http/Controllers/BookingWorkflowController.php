<?php

namespace App\Http\Controllers;

use App\Exports\BookingReportExport;
use App\Models\Booking;
use App\Models\BookingGuest;
use App\Models\Invitation;
use App\Models\Payment;
use App\Models\Role;
use App\Models\TransientHouse;
use App\Models\Unit;
use App\Models\UnitCalendar;
use App\Services\BookingReportService;
use App\Services\BookingService;
use App\Services\BookingWorkflowService;
use App\Support\BookingFinancialContract;
use App\Support\BookingStatus;
use App\Support\PaymentStatus;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BookingWorkflowController extends Controller
{
    public function index(Request $request, BookingWorkflowService $workflow): View|RedirectResponse
    {
        $user = $request->user();
        $dashboardPrefix = strtolower($user->role->name) === 'staff' ? 'staff' : 'owner';

        if ($dashboardPrefix === 'owner') {
            $houseIds = TransientHouse::query()->where('owner_id', $user->user_id)->pluck('house_id');
        } else {
            $staffRoleId = Role::where('name', 'Staff')->value('role_id');
            $houseIds = Invitation::query()
                ->where('email', $user->email)
                ->where('role_id', $staffRoleId)
                ->whereNotNull('used_at')
                ->whereNotNull('house_id')
                ->pluck('house_id')
                ->unique();
        }

        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'section' => ['nullable', Rule::in(['bookings', 'calendar', 'reports'])],
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['nullable', 'integer', 'between:2000,2100'],
            'unit_id' => ['nullable', 'integer', 'min:1'],
            'period' => ['nullable', Rule::in(['weekly', 'monthly', 'annually'])],
            'quarter' => ['nullable', Rule::in(['All', 'Q1', 'Q2', 'Q3', 'Q4', 'Custom'])],
            'house_id' => ['nullable', 'integer', 'min:1'],
            'report_tab' => ['nullable', Rule::in(['overview', 'transactions', 'breakdown'])],
            'breakdown_section' => ['nullable', Rule::in(['period', 'property', 'unit', 'quarter', 'activity'])],
            'transaction_search' => ['nullable', 'string', 'max:100'],
            'transaction_type' => ['nullable', Rule::in(['All', 'Payments', 'Refunds'])],
            'payment_method' => ['nullable', 'string', 'max:100'],
            'transaction_from' => ['nullable', 'date_format:Y-m-d'],
            'transaction_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:transaction_from'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);
        $rangeValidator = Validator::make($request->query(), [
            'from_date' => ['nullable', 'string', 'date_format:Y-m-d', 'required_with:to_date'],
            'to_date' => ['nullable', 'string', 'date_format:Y-m-d', 'required_with:from_date', 'after_or_equal:from_date'],
        ]);

        if ($rangeValidator->fails()) {
            return redirect()
                ->route($dashboardPrefix.'.bookings.index', ['section' => 'reports'])
                ->withErrors($rangeValidator)
                ->withInput($request->query());
        }

        $validated = array_merge($validated, $rangeValidator->validated());
        $section = $validated['section'] ?? 'bookings';

        if ($section === 'calendar') {
            return $this->calendar($houseIds, $dashboardPrefix, $validated);
        }

        if ($section === 'reports') {
            return $this->reports($houseIds, $dashboardPrefix, $validated);
        }

        $search = trim($validated['q'] ?? '');
        $bookingsQuery = Booking::query()
            ->whereHas('unit', fn ($query) => $query->whereIn('house_id', $houseIds))
            ->with(['unit.house', 'guest', 'guests', 'payments', 'discounts.guest', 'archive'])
            ->orderByRaw('CASE status WHEN ? THEN 1 WHEN ? THEN 2 WHEN ? THEN 3 WHEN ? THEN 4 WHEN ? THEN 5 WHEN ? THEN 6 ELSE 7 END', [
                BookingStatus::PENDING,
                BookingStatus::ACCEPTED,
                BookingStatus::CONFIRMED,
                ...BookingStatus::completedStatuses(),
                BookingStatus::CANCELLED,
            ])
            ->orderBy('created_at');

        if ($search !== '') {
            $bookingsQuery->where(function ($query) use ($search): void {
                $query->where('booking_id', 'like', '%'.$search.'%')
                    ->orWhereHas('guest', function ($query) use ($search): void {
                        $query->where('first_name', 'like', '%'.$search.'%')
                            ->orWhere('last_name', 'like', '%'.$search.'%')
                            ->orWhere('email', 'like', '%'.$search.'%');
                    })
                    ->orWhere('walk_in_phone', 'like', '%'.$search.'%')
                    ->orWhere('walk_in_email', 'like', '%'.$search.'%')
                    ->orWhereHas('guests', fn ($query) => $query->where('full_name', 'like', '%'.$search.'%'))
                    ->orWhereHas('unit', function ($query) use ($search): void {
                        $query->where('unit_type', 'like', '%'.$search.'%')
                            ->orWhereHas('house', fn ($query) => $query->where('name', 'like', '%'.$search.'%'));
                    });
            });
        }

        $bookings = $bookingsQuery->get();

        foreach ($bookings->where('status', BookingStatus::ACCEPTED) as $booking) {
            $booking->setAttribute('payment_overdue', $workflow->isPaymentOverdue($booking));
        }

        foreach ($bookings->where('status', BookingStatus::PENDING) as $booking) {
            $booking->has_active_hold = Booking::query()
                ->where('unit_id', $booking->unit_id)
                ->whereIn('status', [BookingStatus::ACCEPTED, BookingStatus::CONFIRMED, BookingStatus::CHECKED_IN])
                ->whereDate('check_in', '<', $booking->check_out->toDateString())
                ->whereDate('check_out', '>', $booking->check_in->toDateString())
                ->exists();
            $booking->queue_position = Booking::query()
                ->where('unit_id', $booking->unit_id)
                ->where('status', BookingStatus::PENDING)
                ->whereDate('check_in', '<', $booking->check_out->toDateString())
                ->whereDate('check_out', '>', $booking->check_in->toDateString())
                ->where(function ($query) use ($booking): void {
                    $query->where('created_at', '<', $booking->created_at)
                        ->orWhere(function ($query) use ($booking): void {
                            $query->where('created_at', $booking->created_at)
                                ->where('booking_id', '<=', $booking->booking_id);
                        });
                })
                ->count();
        }

        $viewData = [
            'pendingBookings' => $bookings->filter(fn (Booking $booking): bool => ! $booking->archive && $booking->status === BookingStatus::PENDING),
            'acceptedBookings' => $bookings->filter(fn (Booking $booking): bool => ! $booking->archive
                && in_array($booking->status, [BookingStatus::ACCEPTED, BookingStatus::CONFIRMED, BookingStatus::CHECKED_IN], true)),
            'historyBookings' => $bookings->filter(fn (Booking $booking): bool => ! $booking->archive && ! in_array(
                $booking->status,
                [BookingStatus::PENDING, BookingStatus::ACCEPTED, BookingStatus::CONFIRMED, BookingStatus::CHECKED_IN],
                true
            )),
            'archivedBookings' => $bookings->filter(fn (Booking $booking): bool => (bool) $booking->archive),
            'dashboardPrefix' => $dashboardPrefix,
            'search' => $search,
        ];

        if ($request->ajax()) {
            return view('bookings.partials.manage-results', $viewData);
        }

        return view('bookings.manage', $viewData);
    }

    /**
     * Render the calendar using only units and bookings the current owner or staff member can access.
     *
     * @param  Collection<int, int>  $houseIds
     * @param  array{month?: int, year?: int, unit_id?: int}  $filters
     */
    private function calendar(Collection $houseIds, string $dashboardPrefix, array $filters): View
    {
        $today = Carbon::today('Asia/Manila');
        $now = Carbon::now('Asia/Manila');
        $month = Carbon::createFromDate($filters['year'] ?? $now->year, $filters['month'] ?? $now->month, 1, 'Asia/Manila');
        $calendarUnits = Unit::query()
            ->with('house')
            ->whereIn('house_id', $houseIds)
            ->whereNull('archived_at')
            ->whereHas('house', fn (Builder $query) => $query->whereNull('archived_at'))
            ->orderBy('house_id')
            ->orderBy('unit_type')
            ->get();

        if (isset($filters['unit_id'])) {
            $selectedUnit = $calendarUnits->firstWhere('unit_id', $filters['unit_id']);
            abort_unless($selectedUnit, 404);
        } else {
            $selectedUnit = $calendarUnits->first();
        }

        $cells = collect();
        $availabilityByDate = collect();

        if ($selectedUnit) {
            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();
            $availabilityByDate = UnitCalendar::query()
                ->where('unit_id', $selectedUnit->unit_id)
                ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->get()
                ->keyBy(fn (UnitCalendar $availability): string => $availability->date->toDateString());

            $bookingsByDate = [];
            $checkoutBookingsByDate = [];
            $bookings = Booking::query()
                ->with(['guest', 'guests'])
                ->where('unit_id', $selectedUnit->unit_id)
                ->whereIn('status', [
                    BookingStatus::ACCEPTED,
                    BookingStatus::CONFIRMED,
                    BookingStatus::CHECKED_IN,
                    ...BookingStatus::completedStatuses(),
                ])
                ->whereDate('check_in', '<', $monthStart->copy()->addMonth()->toDateString())
                ->whereDate('check_out', '>=', $monthStart->toDateString())
                ->orderBy('check_in')
                ->get();

            foreach ($bookings as $booking) {
                $bookingDay = $booking->check_in->copy()->startOfDay()->max($monthStart);
                $lastOccupiedDay = $booking->check_out->copy()->startOfDay()->subDay()->min($monthEnd);

                $checkoutDay = $booking->check_out->copy()->startOfDay();
                if ($checkoutDay->gte($monthStart) && $checkoutDay->lte($monthEnd)) {
                    $checkoutBookingsByDate[$booking->check_out->toDateString()] = $booking;
                }

                while ($bookingDay->lte($lastOccupiedDay)) {
                    $bookingsByDate[$bookingDay->toDateString()] = $booking;
                    $bookingDay->addDay();
                }
            }

            for ($day = 1; $day <= $month->daysInMonth; $day++) {
                $date = $month->copy()->day($day);
                $dateKey = $date->toDateString();
                $booking = $bookingsByDate[$dateKey] ?? null;
                $checkoutBooking = $checkoutBookingsByDate[$dateKey] ?? null;
                $availability = $availabilityByDate->get($dateKey);
                $isPast = $date->lt($today);
                $historicalCheckout = $checkoutBooking && ! $booking && $isPast;
                $status = $booking
                    ? 'Booked'
                    : ($historicalCheckout
                        ? 'Booked'
                        : ($availability?->status ?? ($selectedUnit->status === 'Unavailable' ? 'Unavailable' : 'Available')));
                $checkoutTurnover = $checkoutBooking && ! $booking && ! $isPast && $status === 'Available';

                $cells->push([
                    'date' => $date,
                    'status' => $status,
                    'note' => $availability?->note,
                    'booking' => $booking ?? (($checkoutTurnover || $historicalCheckout) ? $checkoutBooking : null),
                    'checkoutTurnover' => $checkoutTurnover,
                    'isPast' => $isPast,
                    'isToday' => $date->isSameDay($today),
                ]);
            }
        }

        return view('bookings.calendar', [
            'dashboardPrefix' => $dashboardPrefix,
            'calendarUnits' => $calendarUnits,
            'selectedUnit' => $selectedUnit,
            'calendarMonth' => $month,
            'calendarCells' => $cells,
            'availabilityByDate' => $availabilityByDate,
            'calendarToday' => $today,
            'weekdayOffset' => $month->copy()->startOfMonth()->dayOfWeek,
            'previousMonth' => $month->copy()->subMonth(),
            'nextMonth' => $month->copy()->addMonth(),
        ]);
    }

    /**
     * Summarize bookings and verified payments for the current owner's or staff member's houses.
     *
     * @param  Collection<int, int>  $houseIds
     * @param  array{period?: string, month?: int, year?: int, from_date?: string, to_date?: string}  $filters
     */
    private function reports(Collection $houseIds, string $dashboardPrefix, array $filters): View
    {
        $report = app(BookingReportService::class)->generate(
            $houseIds,
            $this->resolvedReportFilters($filters, $houseIds)
        );
        $transactions = $report['transactions'];
        $search = trim((string) ($filters['transaction_search'] ?? ''));
        $type = $filters['transaction_type'] ?? 'All';
        $method = trim((string) ($filters['payment_method'] ?? ''));
        $transactionFrom = $filters['transaction_from'] ?? null;
        $transactionTo = $filters['transaction_to'] ?? null;

        $transactions = $transactions->filter(function (array $transaction) use ($search, $type, $method, $transactionFrom, $transactionTo): bool {
            if ($type !== 'All' && $transaction['type'] !== $type) {
                return false;
            }
            if ($method !== '' && strcasecmp($transaction['payment_method'], $method) !== 0) {
                return false;
            }
            if ($transactionFrom !== null && $transaction['date']->toDateString() < $transactionFrom) {
                return false;
            }
            if ($transactionTo !== null && $transaction['date']->toDateString() > $transactionTo) {
                return false;
            }
            if ($search !== '') {
                $haystack = implode(' ', array_filter([
                    $transaction['reference'],
                    $transaction['booking_reference'],
                    $transaction['guest'],
                    $transaction['property'],
                    $transaction['unit'],
                    $transaction['payment_method'],
                ]));

                if (! str_contains(strtolower($haystack), strtolower($search))) {
                    return false;
                }
            }

            return true;
        })->values();
        $page = max(1, (int) ($filters['page'] ?? 1));
        $transactionPaginator = new LengthAwarePaginator(
            $transactions->forPage($page, 10)->values(),
            $transactions->count(),
            10,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('bookings.reports', [
            ...$report,
            'dashboardPrefix' => $dashboardPrefix,
            'houses' => TransientHouse::query()->whereIn('house_id', $houseIds)->orderBy('name')->get(),
            'transactions' => $transactionPaginator,
            'activeReportTab' => $filters['report_tab'] ?? 'overview',
            'activeBreakdownSection' => $filters['breakdown_section'] ?? 'period',
            'transactionFilters' => [
                'search' => $search,
                'type' => $type,
                'payment_method' => $method,
                'from' => $transactionFrom,
                'to' => $transactionTo,
            ],
            'reportQuery' => [
                'section' => 'reports',
                'period' => $report['period'],
                'year' => $report['selectedYear'],
                'quarter' => $report['selectedQuarter'],
                'house_id' => $report['selectedHouseId'],
                'from_date' => $report['fromDate'],
                'to_date' => $report['toDate'],
            ],
        ]);
    }

    public function downloadExcel(Request $request, BookingReportService $reports): SymfonyResponse
    {
        $houseIds = $this->reportAccess($request);
        $filters = $this->validatedReportFilters($request);
        $report = $reports->generate($houseIds, $this->resolvedReportFilters($filters, $houseIds));

        return Excel::download(
            new BookingReportExport($report),
            'booking-report-'.$report['selectedYear'].'.xlsx'
        );
    }

    public function downloadPdf(Request $request, BookingReportService $reports): SymfonyResponse
    {
        $houseIds = $this->reportAccess($request);
        $filters = $this->validatedReportFilters($request);
        $report = $reports->generate($houseIds, $this->resolvedReportFilters($filters, $houseIds));

        return Pdf::loadView('bookings.reports-export', $report)
            ->setPaper('a4', 'landscape')
            ->download('booking-report-'.$report['selectedYear'].'.pdf');
    }

    /**
     * @return Collection<int, int>
     */
    private function reportAccess(Request $request): Collection
    {
        $user = $request->user();
        $dashboardPrefix = strtolower($user->role->name) === 'staff' ? 'staff' : 'owner';
        $houseIds = $dashboardPrefix === 'owner'
            ? TransientHouse::query()->where('owner_id', $user->user_id)->pluck('house_id')
            : Invitation::query()
                ->where('email', $user->email)
                ->where('role_id', Role::where('name', 'Staff')->value('role_id'))
                ->whereNotNull('used_at')
                ->whereNotNull('house_id')
                ->pluck('house_id')
                ->unique();

        return $houseIds;
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedReportFilters(Request $request): array
    {
        $filters = $request->validate([
            'period' => ['nullable', Rule::in(['weekly', 'monthly', 'annually'])],
            'year' => ['nullable', 'integer', 'between:2000,2100'],
            'quarter' => ['nullable', Rule::in(['All', 'Q1', 'Q2', 'Q3', 'Q4', 'Custom'])],
            'house_id' => ['nullable', 'integer', 'min:1'],
            'from_date' => ['nullable', 'date_format:Y-m-d', 'required_with:to_date'],
            'to_date' => ['nullable', 'date_format:Y-m-d', 'required_with:from_date', 'after_or_equal:from_date'],
        ]);

        return $filters;
    }

    /**
     * @param  Collection<int, int>  $houseIds
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    private function resolvedReportFilters(array $filters, Collection $houseIds): array
    {
        $year = (int) ($filters['year'] ?? now()->year);
        $quarter = $filters['quarter'] ?? 'All';
        $selectedHouseId = isset($filters['house_id']) ? (int) $filters['house_id'] : null;

        if ($selectedHouseId !== null && ! $houseIds->contains($selectedHouseId)) {
            abort(404);
        }

        if (in_array($quarter, ['Q1', 'Q2', 'Q3', 'Q4'], true)) {
            $quarterNumber = (int) substr($quarter, 1);
            $quarterStartMonth = (($quarterNumber - 1) * 3) + 1;
            $quarterStart = Carbon::createFromDate($year, $quarterStartMonth, 1)->startOfDay();
            $quarterEnd = $quarterStart->copy()->addMonths(3)->subDay()->endOfDay();
            $fromDate = $quarterStart->toDateString();
            $toDate = $quarterEnd->toDateString();
        } elseif (isset($filters['from_date'], $filters['to_date'])) {
            $fromDate = $filters['from_date'];
            $toDate = $filters['to_date'];
            $quarter = $this->quarterForRange($fromDate, $toDate, $year);
        } else {
            $fromDate = Carbon::createFromDate($year, 1, 1)->toDateString();
            $toDate = Carbon::createFromDate($year, 12, 31)->toDateString();
            $quarter = 'All';
        }

        return [
            'period' => $filters['period'] ?? 'monthly',
            'year' => $year,
            'quarter' => $quarter,
            'house_id' => $selectedHouseId,
            'from_date' => $fromDate,
            'to_date' => $toDate,
        ];
    }

    private function quarterForRange(string $fromDate, string $toDate, int $year): string
    {
        foreach (range(1, 4) as $quarterNumber) {
            $startMonth = (($quarterNumber - 1) * 3) + 1;
            $start = Carbon::createFromDate($year, $startMonth, 1)->toDateString();
            $end = Carbon::createFromDate($year, $startMonth, 1)->addMonths(3)->subDay()->toDateString();

            if ($fromDate === $start && $toDate === $end) {
                return 'Q'.$quarterNumber;
            }
        }

        if (
            $fromDate === Carbon::createFromDate($year, 1, 1)->toDateString()
            && $toDate === Carbon::createFromDate($year, 12, 31)->toDateString()
        ) {
            return 'All';
        }

        return 'Custom';
    }

    public function show(Request $request, int $bookingId, BookingWorkflowService $workflow): View
    {
        $booking = Booking::query()
            ->with([
                'unit.house',
                'guest',
                'guests',
                'payments',
                'review.guest',
                'legacyFeedback.user',
            ])
            ->findOrFail($bookingId);
        abort_unless($workflow->canManageHouse($request->user(), $booking->unit->house), 404);

        $pendingRequests = $workflow->overlappingPendingBookings($booking);
        $booking->has_active_hold = Booking::query()
            ->where('unit_id', $booking->unit_id)
            ->whereIn('status', [BookingStatus::ACCEPTED, BookingStatus::CONFIRMED, BookingStatus::CHECKED_IN])
            ->whereDate('check_in', '<', $booking->check_out->toDateString())
            ->whereDate('check_out', '>', $booking->check_in->toDateString())
            ->exists();
        $booking->queue_position = $pendingRequests->search(
            fn (Booking $request): bool => $request->booking_id === $booking->booking_id
        ) + 1;

        return view('bookings.show', [
            'booking' => $booking,
            'dashboardPrefix' => strtolower($request->user()->role->name) === 'staff' ? 'staff' : 'owner',
            'verifiedPaymentTotal' => BookingFinancialContract::verifiedPayments($booking),
            'underReviewStatus' => PaymentStatus::UNDER_REVIEW,
            'isPaymentOverdue' => $workflow->isPaymentOverdue($booking),
        ]);
    }

    public function guestCheckIn(Request $request, int $bookingId, BookingWorkflowService $workflow): View
    {
        $booking = Booking::query()
            ->with([
                'unit.house',
                'guest',
                'discounts.guest',
                'discounts.verifiedBy',
                'payments',
                'refunds.processedBy',
                'addons',
                'unit.addons',
            ])
            ->findOrFail($bookingId);
        abort_unless($workflow->canManageHouse($request->user(), $booking->unit->house), 404);
        abort_unless(in_array($booking->status, [BookingStatus::CONFIRMED, BookingStatus::CHECKED_IN], true), 404);

        $verifiedPaymentTotal = BookingFinancialContract::verifiedPayments($booking);
        $refundTotal = BookingFinancialContract::completedRefunds($booking);

        return view('bookings.guest-check-in', [
            'booking' => $booking,
            'dashboardPrefix' => strtolower($request->user()->role->name) === 'staff' ? 'staff' : 'owner',
            'canUpdateCheckInHeadcount' => $booking->status === BookingStatus::CONFIRMED
                && $booking->check_in->toDateString() === today()->toDateString(),
            'refundAmountLimit' => max(
                0,
                round($booking->refundableOverpayment() - (float) $booking->refunds()
                    ->where('refund_status', 'Pending')
                    ->sum('amount'), 2)
            ),
        ]);
    }

    public function updateCheckInHeadcount(
        Request $request,
        int $bookingId,
        BookingWorkflowService $workflow,
        BookingService $bookingService
    ): RedirectResponse {
        $booking = Booking::query()
            ->with(['unit.house'])
            ->findOrFail($bookingId);
        abort_unless($workflow->canManageHouse($request->user(), $booking->unit->house), 404);

        $maximumCapacity = (int) ($booking->unit->maximum_capacity ?? $booking->unit->capacity);
        $validated = $request->validate([
            'adult_count' => ['required', 'integer', 'min:1', 'max:'.$maximumCapacity],
            'child_count' => [
                'required',
                'integer',
                'min:0',
                'max:'.($booking->unit->child_free_through_age === null ? 0 : $maximumCapacity),
            ],
        ]);

        $workflow->updateCheckInHeadcount(
            $request->user(),
            $bookingId,
            (int) $validated['adult_count'],
            (int) $validated['child_count'],
            $bookingService
        );
        $dashboardPrefix = strtolower($request->user()->role->name) === 'staff' ? 'staff' : 'owner';

        return redirect()
            ->route($dashboardPrefix.'.guests.check-in', $bookingId)
            ->with('success', 'Guest headcount updated and booking pricing recalculated.');
    }

    public function recordGuestArrival(
        Request $request,
        int $bookingId,
        int $bookingGuestId,
        BookingWorkflowService $workflow
    ): RedirectResponse {
        $workflow->recordGuestArrival($request->user(), $bookingId, $bookingGuestId);
        $dashboardPrefix = strtolower($request->user()->role->name) === 'staff' ? 'staff' : 'owner';

        return redirect()
            ->route($dashboardPrefix.'.guests.check-in', $bookingId)
            ->with('success', 'Guest arrival recorded.');
    }

    public function markGuestNoShow(
        Request $request,
        int $bookingId,
        int $bookingGuestId,
        BookingWorkflowService $workflow
    ): RedirectResponse {
        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);
        $workflow->markGuestNoShow(
            $request->user(),
            $bookingId,
            $bookingGuestId,
            $validated['reason'] ?? null
        );
        $dashboardPrefix = strtolower($request->user()->role->name) === 'staff' ? 'staff' : 'owner';

        return redirect()
            ->route($dashboardPrefix.'.guests.check-in', $bookingId)
            ->with('success', 'Guest marked as No-show.');
    }

    public function replaceGuest(
        Request $request,
        int $bookingId,
        int $bookingGuestId,
        BookingWorkflowService $workflow
    ): RedirectResponse {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);
        $workflow->replaceExpectedGuest(
            $request->user(),
            $bookingId,
            $bookingGuestId,
            $validated['full_name'],
            $validated['reason'] ?? null
        );
        $dashboardPrefix = strtolower($request->user()->role->name) === 'staff' ? 'staff' : 'owner';

        return redirect()
            ->route($dashboardPrefix.'.guests.check-in', $bookingId)
            ->with('success', 'Guest replacement recorded.');
    }

    public function reviewGuestIdentity(
        Request $request,
        int $bookingId,
        int $bookingGuestId,
        BookingWorkflowService $workflow
    ): RedirectResponse {
        $validated = $request->validate([
            'identity_status' => [
                'required',
                Rule::in([
                    BookingGuest::IDENTITY_VERIFIED,
                    BookingGuest::IDENTITY_EXCEPTION,
                    BookingGuest::IDENTITY_FAILED,
                ]),
            ],
            'reason' => ['nullable', 'string', 'max:500', 'required_if:identity_status,'.BookingGuest::IDENTITY_EXCEPTION],
        ]);
        $workflow->reviewGuestIdentity(
            $request->user(),
            $bookingId,
            $bookingGuestId,
            $validated['identity_status'],
            $validated['reason'] ?? null
        );
        $dashboardPrefix = strtolower($request->user()->role->name) === 'staff' ? 'staff' : 'owner';

        return redirect()
            ->route($dashboardPrefix.'.guests.check-in', $bookingId)
            ->with('success', 'Guest identity review recorded.');
    }

    public function refundCheckedInGuest(Request $request, int $bookingId, BookingWorkflowService $workflow): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0'],
            'reason' => ['required', 'string', 'max:255'],
        ]);
        $workflow->refundCheckedInGuest(
            $request->user(),
            $bookingId,
            (float) $validated['amount'],
            $validated['reason']
        );

        $dashboardPrefix = strtolower($request->user()->role->name) === 'staff' ? 'staff' : 'owner';

        return redirect()
            ->route($dashboardPrefix.'.guests.check-in', $bookingId)
            ->with('success', 'The refund was recorded.');
    }

    public function addBookingAddons(Request $request, int $bookingId, BookingWorkflowService $workflow): RedirectResponse
    {
        $validated = $request->validate([
            'addons' => ['required', 'array', 'min:1'],
            'addons.*.unit_addon_id' => ['required', 'integer', 'distinct'],
            'addons.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
        ]);
        $workflow->addBookingAddons($request->user(), $bookingId, $validated['addons']);

        $dashboardPrefix = strtolower($request->user()->role->name) === 'staff' ? 'staff' : 'owner';

        return redirect()
            ->route($dashboardPrefix.'.guests.check-in', $bookingId)
            ->with('success', 'The amenities were added to the booking.');
    }

    public function adjustBookingAddonQuantity(Request $request, int $bookingId, int $addonId, BookingWorkflowService $workflow): RedirectResponse
    {
        $validated = $request->validate([
            'change' => ['required', 'integer', Rule::in([-1, 1])],
        ]);
        $workflow->adjustBookingAddonQuantity(
            $request->user(),
            $bookingId,
            $addonId,
            (int) $validated['change']
        );
        $dashboardPrefix = strtolower($request->user()->role->name) === 'staff' ? 'staff' : 'owner';

        return redirect()
            ->route($dashboardPrefix.'.guests.check-in', $bookingId)
            ->with('success', 'Amenity quantity updated.');
    }

    public function checkIn(Request $request, int $bookingId, BookingWorkflowService $workflow): RedirectResponse
    {
        $request->validate([
            'guest_details_confirmed' => ['required', 'accepted'],
        ]);
        $workflow->checkInGuest($request->user(), $bookingId, true);
        $dashboardPrefix = strtolower($request->user()->role->name) === 'staff' ? 'staff' : 'owner';

        return redirect()
            ->route($dashboardPrefix.'.dashboard')
            ->with('success', 'Guest checked in successfully.');
    }

    public function storeCalendarBlock(Request $request, BookingWorkflowService $workflow): RedirectResponse
    {
        $data = $request->validate([
            'unit_id' => ['required', 'integer', 'min:1'],
            'dates' => ['required', 'array', 'min:1', 'max:31'],
            'dates.*' => ['required', 'date', 'after_or_equal:today', 'distinct'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $unit = Unit::query()->with('house')->findOrFail($data['unit_id']);
        abort_unless($workflow->canManageHouse($request->user(), $unit->house), 404);
        abort_unless($unit->archived_at === null && $unit->house->archived_at === null, 404);

        $dates = collect($data['dates'])
            ->map(fn (string $date): Carbon => Carbon::parse($date)->startOfDay())
            ->sort()
            ->values();
        $dateKeys = $dates->map(fn (Carbon $date): string => $date->toDateString())->all();

        DB::transaction(function () use ($unit, $dates, $dateKeys, $data): void {
            $lockedUnit = Unit::query()->whereKey($unit->unit_id)->lockForUpdate()->firstOrFail();
            $hasBookedCalendarDate = $lockedUnit->availability()
                ->where('status', 'Booked')
                ->where(function ($query) use ($dateKeys): void {
                    foreach ($dateKeys as $dateKey) {
                        $query->orWhereDate('date', $dateKey);
                    }
                })
                ->exists();
            $overlappingBookings = Booking::query()
                ->where('unit_id', $lockedUnit->unit_id)
                ->whereIn('status', [BookingStatus::ACCEPTED, BookingStatus::CONFIRMED, BookingStatus::CHECKED_IN])
                ->whereDate('check_in', '<', $dates->last()->copy()->addDay()->toDateString())
                ->whereDate('check_out', '>', $dates->first()->toDateString())
                ->get(['check_in', 'check_out']);
            $hasBooking = $dates->contains(function (Carbon $date) use ($overlappingBookings): bool {
                return $overlappingBookings->contains(fn (Booking $booking): bool => $date->gte($booking->check_in->copy()->startOfDay())
                    && $date->lt($booking->check_out->copy()->startOfDay()));
            });

            if ($hasBooking || $hasBookedCalendarDate) {
                throw ValidationException::withMessages([
                    'dates' => 'One or more selected dates are reserved by an accepted or confirmed booking.',
                ]);
            }

            foreach ($dates as $date) {
                UnitCalendar::updateOrCreate(
                    ['unit_id' => $lockedUnit->unit_id, 'date' => $date->toDateString()],
                    ['status' => 'Unavailable', 'note' => $data['note'] ?? null]
                );
            }
        });

        return back()->with('success', 'The selected dates are now blocked on the unit calendar.');
    }

    public function destroyCalendarBlock(Request $request, int $availabilityId, BookingWorkflowService $workflow): RedirectResponse
    {
        $availability = UnitCalendar::query()
            ->with('unit.house')
            ->findOrFail($availabilityId);
        abort_unless(
            $availability->status === 'Unavailable'
                && $workflow->canManageHouse($request->user(), $availability->unit->house),
            404
        );

        DB::transaction(function () use ($availability): void {
            Unit::query()->whereKey($availability->unit_id)->lockForUpdate()->firstOrFail();
            $lockedAvailability = UnitCalendar::query()
                ->whereKey($availability->availability_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedAvailability->status !== 'Unavailable') {
                abort(404);
            }

            $lockedAvailability->delete();
        });

        return back()->with('success', 'The calendar block was removed.');
    }

    public function edit(Request $request, int $bookingId, BookingWorkflowService $workflow): View
    {
        $booking = Booking::query()->with([
            'unit.house',
            'guest',
            'additionalCharges',
            'damageReports',
            'payments',
            'addons',
        ])->findOrFail($bookingId);
        abort_unless($workflow->canManageHouse($request->user(), $booking->unit->house), 404);
        abort_unless(in_array($booking->status, [
            BookingStatus::PENDING,
            BookingStatus::ACCEPTED,
            BookingStatus::CONFIRMED,
            BookingStatus::CHECKED_IN,
        ], true), 404);
        abort_if(
            $booking->status !== BookingStatus::PENDING
                && $booking->status !== BookingStatus::CHECKED_IN
                && $booking->check_in->toDateString() <= today()->toDateString(),
            404
        );
        abort_if($booking->payments->contains('payment_status', PaymentStatus::UNDER_REVIEW), 404);

        return view('bookings.edit', [
            'booking' => $booking,
            'verifiedPaymentTotal' => BookingFinancialContract::verifiedPayments($booking),
            'currentCheckoutCharges' => BookingFinancialContract::amenities($booking)
                + BookingFinancialContract::additionalCharges($booking),
            'dashboardPrefix' => strtolower($request->user()->role->name) === 'staff' ? 'staff' : 'owner',
        ]);
    }

    public function update(
        Request $request,
        int $bookingId,
        BookingWorkflowService $workflow,
        BookingService $bookingService
    ): RedirectResponse {
        $booking = Booking::query()
            ->with(['unit.house'])
            ->findOrFail($bookingId);
        abort_unless($workflow->canManageHouse($request->user(), $booking->unit->house), 404);
        $checkedIn = $booking->status === BookingStatus::CHECKED_IN;
        $rules = [
            'check_out' => $checkedIn
                ? ['required', 'date', 'after:today', 'after:'.$booking->check_in->toDateString()]
                : ['required', 'date', 'after:check_in'],
            'reason' => ['required', 'string', 'max:2000'],
            'adult_count' => [
                'sometimes',
                'integer',
                'min:1',
                'max:'.($booking->unit->maximum_capacity ?? $booking->unit->capacity),
            ],
            'child_count' => [
                'sometimes',
                'integer',
                'min:0',
                'max:'.($booking->unit->child_free_through_age === null ? 0 : 100),
            ],
        ];
        if (! $checkedIn) {
            $rules['check_in'] = ['required', 'date', 'after:tomorrow'];
        }
        $validated = $request->validate($rules, [
            'check_out.after' => 'The check-out date must be later than today and the original check-in date.',
        ]);
        if ($checkedIn) {
            $validated['check_in'] = $booking->check_in->toDateString();
        }
        $workflow->editBooking($request->user(), $bookingId, $validated, $validated['reason'], $bookingService);
        $dashboardPrefix = strtolower($request->user()->role->name) === 'staff' ? 'staff' : 'owner';

        return redirect()
            ->route($checkedIn ? $dashboardPrefix.'.guests.check-in' : $dashboardPrefix.'.bookings.show', $bookingId)
            ->with('success', 'The booking was updated and the Guest was notified.');
    }

    public function checkout(Request $request, int $bookingId, BookingWorkflowService $workflow): View
    {
        $booking = Booking::query()
            ->with([
                'unit.house',
                'unit.damageItems',
                'guest',
                'guests',
                'discounts.guest',
                'discounts.verifiedBy',
                'payments',
                'refunds.processedBy',
                'refunds.payment',
                'addons',
                'damages',
                'additionalCharges.createdBy',
                'damageReports.reportedBy',
                'receipt',
            ])
            ->findOrFail($bookingId);
        abort_unless($workflow->canManageHouse($request->user(), $booking->unit->house), 404);
        abort_unless(in_array($booking->status, [
            BookingStatus::CONFIRMED,
            BookingStatus::CHECKED_IN,
            ...BookingStatus::completedStatuses(),
        ], true), 404);

        $financialSummary = BookingFinancialContract::receiptSummary($booking);
        $verifiedPaymentTotal = $financialSummary['verifiedPayments'];
        $refundedTotal = $financialSummary['completedRefunds'];
        $netBalance = $financialSummary['outstandingBalance'];
        $approvedDiscountTotal = $financialSummary['approvedDiscounts'];
        $isReadOnly = BookingStatus::isTerminal($booking->status);

        return view('bookings.checkout', [
            'booking' => $booking,
            'dashboardPrefix' => strtolower($request->user()->role->name) === 'staff' ? 'staff' : 'owner',
            'financialSummary' => $financialSummary,
            'verifiedPaymentTotal' => $verifiedPaymentTotal,
            'refundedTotal' => $refundedTotal,
            'netBalance' => $netBalance,
            'balanceDue' => max(0, $netBalance),
            'approvedDiscountTotal' => $approvedDiscountTotal,
            'hasUnderReviewPayment' => $booking->payments->contains('payment_status', PaymentStatus::UNDER_REVIEW),
            'isReadOnly' => $isReadOnly,
            'isEarlyCheckout' => ! $isReadOnly
                && $booking->status === BookingStatus::CHECKED_IN
                && today()->lt($booking->check_out->copy()->startOfDay()),
        ]);
    }

    public function addCheckoutDamages(Request $request, int $bookingId, BookingWorkflowService $workflow): RedirectResponse
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.unit_damage_item_id' => ['required', 'integer', 'distinct', Rule::exists('unit_damage_items', 'unit_damage_item_id')],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
        ]);
        $workflow->addBookingDamages($request->user(), $bookingId, array_values($validated['items']));

        $dashboardPrefix = strtolower($request->user()->role->name) === 'staff' ? 'staff' : 'owner';

        return redirect()
            ->route($dashboardPrefix.'.bookings.checkout', $bookingId)
            ->with('success', 'Damage or missing items were added to the checkout.');
    }

    public function addCustomCheckoutDamage(Request $request, int $bookingId, BookingWorkflowService $workflow): RedirectResponse
    {
        $validated = $request->validate([
            'description' => ['required', 'string', 'max:500'],
            'quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'unit_price' => ['required', 'numeric', 'gt:0', 'max:999999'],
        ]);
        $workflow->addCustomBookingDamage(
            $request->user(),
            $bookingId,
            $validated['description'],
            (int) $validated['quantity'],
            (float) $validated['unit_price']
        );

        $dashboardPrefix = strtolower($request->user()->role->name) === 'staff' ? 'staff' : 'owner';

        return redirect()
            ->route($dashboardPrefix.'.bookings.checkout', $bookingId)
            ->with('success', 'The custom damage charge was added to the checkout.');
    }

    public function adjustCheckoutDamageQuantity(Request $request, int $bookingId, int $damageId, BookingWorkflowService $workflow): RedirectResponse
    {
        $validated = $request->validate([
            'change' => ['required', 'integer', Rule::in([-1, 1])],
        ]);
        $workflow->adjustDamageReportQuantity(
            $request->user(),
            $bookingId,
            $damageId,
            (int) $validated['change']
        );

        $dashboardPrefix = strtolower($request->user()->role->name) === 'staff' ? 'staff' : 'owner';

        return redirect()
            ->route($dashboardPrefix.'.bookings.checkout', $bookingId)
            ->with('success', 'Damage quantity updated.');
    }

    public function adjustCheckoutChargeQuantity(Request $request, int $bookingId, int $chargeId, BookingWorkflowService $workflow): RedirectResponse
    {
        $validated = $request->validate([
            'change' => ['required', 'integer', Rule::in([-1, 1])],
        ]);
        $workflow->adjustCheckoutChargeQuantity(
            $request->user(),
            $bookingId,
            $chargeId,
            (int) $validated['change']
        );

        $dashboardPrefix = strtolower($request->user()->role->name) === 'staff' ? 'staff' : 'owner';

        return redirect()
            ->route($dashboardPrefix.'.bookings.checkout', $bookingId)
            ->with('success', 'Checkout charge quantity updated.');
    }

    public function addCheckoutCharge(Request $request, int $bookingId, BookingWorkflowService $workflow): RedirectResponse
    {
        $validated = $request->validate([
            'description' => ['required', 'string', 'max:200'],
            'quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'unit_price' => ['required', 'numeric', 'gt:0', 'max:999999'],
        ]);
        $workflow->addCheckoutCharge(
            $request->user(),
            $bookingId,
            $validated['description'],
            (int) $validated['quantity'],
            (float) $validated['unit_price']
        );

        $dashboardPrefix = strtolower($request->user()->role->name) === 'staff' ? 'staff' : 'owner';

        return redirect()
            ->route($dashboardPrefix.'.bookings.checkout', $bookingId)
            ->with('success', 'The checkout charge was added.');
    }

    public function removeCheckoutCharge(Request $request, int $bookingId, int $chargeId, BookingWorkflowService $workflow): RedirectResponse
    {
        $workflow->removeCheckoutCharge($request->user(), $bookingId, $chargeId);

        $dashboardPrefix = strtolower($request->user()->role->name) === 'staff' ? 'staff' : 'owner';

        return redirect()
            ->route($dashboardPrefix.'.bookings.checkout', $bookingId)
            ->with('success', 'The checkout charge was removed.');
    }

    public function recordCheckoutPayment(Request $request, int $bookingId, BookingWorkflowService $workflow): RedirectResponse
    {
        $referenceNumber = trim((string) $request->input('reference_number', ''));
        $request->merge(['reference_number' => $referenceNumber !== '' ? $referenceNumber : null]);

        $validated = $request->validate([
            'payment_method' => ['required', 'in:Cash,GCash,Bank Transfer'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'reference_number' => ['nullable', 'string', 'max:100', Rule::unique('payments', 'reference_number')],
            'payment_receipt' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
        $receiptPath = $request->file('payment_receipt')?->store('payment-proofs');
        $paymentSaved = false;

        try {
            $workflow->recordCheckoutPayment(
                $request->user(),
                $bookingId,
                $validated['payment_method'],
                (float) $validated['amount'],
                $validated['reference_number'] ?? null,
                $receiptPath,
                $validated['notes'] ?? null
            );
            $paymentSaved = true;
        } finally {
            if (! $paymentSaved && $receiptPath) {
                Storage::disk('local')->delete($receiptPath);
            }
        }

        $dashboardPrefix = strtolower($request->user()->role->name) === 'staff' ? 'staff' : 'owner';

        return redirect()
            ->route($dashboardPrefix.'.bookings.checkout', $bookingId)
            ->with('success', 'The payment was recorded as verified.');
    }

    public function processCheckoutRefund(Request $request, int $bookingId, int $refundId, BookingWorkflowService $workflow): RedirectResponse
    {
        $reference = trim((string) $request->input('refund_reference', ''));
        $validated = $request->validate([
            'refund_reference' => ['nullable', 'string', 'max:100'],
        ]);
        $workflow->processCheckoutRefund(
            $request->user(),
            $bookingId,
            $refundId,
            $reference !== '' ? $reference : null
        );

        $dashboardPrefix = strtolower($request->user()->role->name) === 'staff' ? 'staff' : 'owner';

        return redirect()
            ->route($dashboardPrefix.'.bookings.checkout', $bookingId)
            ->with('success', 'The refund was marked as issued and the Guest was notified.');
    }

    public function reviewDiscount(Request $request, int $discountId, BookingWorkflowService $workflow): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:Verified,Invalid,Rejected'],
            'verification_note' => [
                Rule::requiredIf($request->input('status') !== 'Verified'),
                'nullable',
                'string',
                'max:500',
            ],
        ], [
            'verification_note.required' => 'Please explain why this ID cannot be accepted.',
            'verification_note.string' => 'Please enter a reason in words.',
            'verification_note.max' => 'Please keep the reason to 500 characters or fewer.',
        ]);
        $discount = $workflow->reviewDiscount(
            $request->user(),
            $discountId,
            $validated['status'],
            $validated['verification_note'] ?? null
        );
        $dashboardPrefix = strtolower($request->user()->role->name) === 'staff' ? 'staff' : 'owner';

        return redirect()
            ->route($dashboardPrefix.'.guests.check-in', $discount->booking_id)
            ->with('success', 'The Senior/PWD ID review was saved.');
    }

    public function completeCheckout(Request $request, int $bookingId, BookingWorkflowService $workflow): RedirectResponse
    {
        $validated = $request->validate([
            'early_checkout_reason' => ['nullable', 'string', 'max:1000'],
        ]);
        $workflow->completeCheckout(
            $request->user(),
            $bookingId,
            $validated['early_checkout_reason'] ?? null
        );
        $dashboardPrefix = strtolower($request->user()->role->name) === 'staff' ? 'staff' : 'owner';

        if (Booking::query()
            ->whereKey($bookingId)
            ->whereHas('refunds', fn ($query) => $query->where('refund_status', 'Pending'))
            ->exists()) {
            return redirect()
                ->route($dashboardPrefix.'.bookings.checkout', $bookingId)
                ->with('success', 'Checkout completed. The refund is ready to return to the Guest from the bill breakdown.');
        }

        return redirect()
            ->route($dashboardPrefix.'.bookings.checkout', $bookingId)
            ->with('success', 'Checkout completed. The Booking Breakdown is ready.');
    }

    public function sendFeedbackRequest(Request $request, int $bookingId, BookingWorkflowService $workflow): RedirectResponse
    {
        $feedbackUrl = $workflow->sendFeedbackRequest($request->user(), $bookingId);
        $booking = Booking::query()->with('guest')->findOrFail($bookingId);

        $dashboardPrefix = strtolower($request->user()->role->name) === 'staff' ? 'staff' : 'owner';

        $redirect = redirect()->route($dashboardPrefix.'.guests.check-in', $bookingId);

        if ($booking->user_id === null && ! $booking->displayGuestEmail()) {
            return $redirect
                ->with('success', 'Show this feedback link to the guest.')
                ->with('feedbackUrl', $feedbackUrl);
        }

        return $redirect->with('success', 'The feedback link was emailed to the guest.');
    }

    public function archive(Request $request, int $bookingId, BookingWorkflowService $workflow): RedirectResponse
    {
        $validated = $request->validate([
            'note' => ['nullable', 'string', 'max:2000'],
        ]);
        $workflow->archiveBooking($request->user(), $bookingId, $validated['note'] ?? null);
        $dashboardPrefix = strtolower($request->user()->role->name) === 'staff' ? 'staff' : 'owner';

        return redirect()
            ->route($dashboardPrefix.'.bookings.index', ['tab' => 'archive'])
            ->with('success', 'The booking was archived and is available in the Archive tab.');
    }

    public function archiveSelected(Request $request, BookingWorkflowService $workflow): RedirectResponse
    {
        $validated = $request->validate([
            'booking_ids' => ['required', 'array', 'min:1'],
            'booking_ids.*' => ['required', 'integer', 'distinct', 'min:1'],
        ]);
        $bookingIds = array_map('intval', $validated['booking_ids']);
        $archivedCount = $workflow->archiveBookings($request->user(), $bookingIds);
        $dashboardPrefix = strtolower($request->user()->role->name) === 'staff' ? 'staff' : 'owner';
        $message = $archivedCount === 1
            ? 'The booking was archived.'
            : "{$archivedCount} bookings were archived.";

        return redirect()
            ->route($dashboardPrefix.'.bookings.index', ['tab' => 'archive'])
            ->with('success', $message);
    }

    public function accept(Request $request, int $bookingId, BookingWorkflowService $workflow): RedirectResponse
    {
        $workflow->accept($request->user(), $bookingId);

        return back()->with('success', 'The booking request was accepted. The dates are reserved while the Guest submits the downpayment.');
    }

    public function reject(Request $request, int $bookingId, BookingWorkflowService $workflow): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ], [
            'reason.required' => 'Please explain why you are rejecting this booking.',
            'reason.string' => 'Please enter a reason in words.',
            'reason.max' => 'Please keep the reason to 500 characters or fewer.',
        ]);
        $workflow->reject($request->user(), $bookingId, $validated['reason']);
        $dashboardPrefix = strtolower($request->user()->role->name) === 'staff' ? 'staff' : 'owner';

        return redirect()
            ->route($dashboardPrefix.'.bookings.show', $bookingId)
            ->with('success', 'The booking request was rejected and the Guest was notified.');
    }

    public function cancel(Request $request, int $bookingId, BookingWorkflowService $workflow): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ], [
            'reason.required' => 'Please explain why you are cancelling this booking.',
            'reason.string' => 'Please enter a reason in words.',
            'reason.max' => 'Please keep the reason to 500 characters or fewer.',
        ]);
        $workflow->cancelAccepted($request->user(), $bookingId, $validated['reason']);
        $dashboardPrefix = strtolower($request->user()->role->name) === 'staff' ? 'staff' : 'owner';

        return redirect()
            ->route($dashboardPrefix.'.bookings.show', $bookingId)
            ->with('success', 'The booking was cancelled and the Guest was notified.');
    }

    public function reviewPayment(Request $request, int $paymentId, BookingWorkflowService $workflow): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:'.implode(',', [
                PaymentStatus::VERIFIED,
                PaymentStatus::INSUFFICIENT,
                PaymentStatus::MISSING,
                PaymentStatus::INCORRECT,
            ])],
            'notes' => [
                Rule::requiredIf($request->input('status') !== PaymentStatus::VERIFIED),
                'nullable',
                'string',
                'max:500',
            ],
        ], [
            'notes.required' => 'Please explain why this payment cannot be accepted.',
            'notes.string' => 'Please enter a reason in words.',
            'notes.max' => 'Please keep the reason to 500 characters or fewer.',
        ]);

        $payment = $workflow->reviewPayment(
            $request->user(),
            $paymentId,
            $validated['status'],
            $validated['notes'] ?? null
        );
        $dashboardPrefix = strtolower($request->user()->role->name) === 'staff' ? 'staff' : 'owner';

        return redirect()
            ->route($dashboardPrefix.'.bookings.show', $payment->booking_id)
            ->with('success', 'The payment review was saved and the Guest was notified.');
    }

    public function downloadProof(Request $request, int $paymentId, BookingWorkflowService $workflow): StreamedResponse
    {
        $payment = Payment::query()
            ->with(['booking.unit.house'])
            ->findOrFail($paymentId);
        $user = $request->user();

        if ($user->role?->name === 'Guest') {
            abort_unless($payment->booking->user_id === $user->user_id, 404);
        } else {
            abort_unless($workflow->canManageHouse($user, $payment->booking->unit->house), 404);
        }

        abort_unless($payment->payment_receipt, 404);
        $disk = Storage::disk('local');
        abort_unless($disk->exists($payment->payment_receipt), 404);

        $extension = pathinfo($payment->payment_receipt, PATHINFO_EXTENSION);
        $filename = 'payment-proof-'.$payment->payment_id.($extension ? '.'.$extension : '');

        if ($request->boolean('download')) {
            return $disk->download($payment->payment_receipt, $filename);
        }

        return $disk->response($payment->payment_receipt, $filename);
    }
}
