<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingDiscount;
use App\Models\BookingGuest;
use App\Models\Unit;
use App\Models\User;
use App\Support\BookingStatus;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingService
{
    public function __construct(private BookingWorkflowService $workflow) {}

    /**
     * Dates that cannot be booked for this unit: anything from today
     * onward marked Booked or Unavailable in unit_availability.
     */
    public function getBlockedDates(Unit $unit): array
    {
        return $unit->availability()
            ->where('date', '>=', Carbon::today())
            ->whereIn('status', ['Booked', 'Unavailable'])
            ->pluck('date')
            ->map(fn ($d) => $d->format('Y-m-d'))
            ->toArray();
    }

    /**
     * True if any date in [checkIn, checkOut) is blocked on the calendar.
     */
    public function hasBlockedDate(Unit $unit, string $checkIn, string $checkOut): bool
    {
        $blocked = $this->getBlockedDates($unit);
        $period = CarbonPeriod::create($checkIn, '1 day', Carbon::parse($checkOut)->subDay());

        foreach ($period as $day) {
            if (in_array($day->format('Y-m-d'), $blocked, true)) {
                return true;
            }
        }

        return false;
    }

    public function dateRangeConflict(Unit $unit, string $checkIn, string $checkOut, bool $checkOutMustBeAvailable = true): ?string
    {
        if ($this->hasBlockedDate($unit, $checkIn, $checkOut)
            || ($checkOutMustBeAvailable && $this->hasUnavailableCheckoutDate($unit, $checkOut))) {
            return 'blocked';
        }

        if ($this->hasActiveBooking($unit, $checkIn, $checkOut)) {
            return 'booking';
        }

        return null;
    }

    private function hasUnavailableCheckoutDate(Unit $unit, string $checkOut): bool
    {
        return $unit->availability()
            ->whereDate('date', Carbon::parse($checkOut)->toDateString())
            ->where('status', 'Unavailable')
            ->exists();
    }

    /**
     * Price total for a stay using the unit's nightly rate.
     */
    public function calculate(
        Unit $unit,
        string $checkIn,
        string $checkOut,
        ?int $payingGuestCount = null
    ): array {
        $nights = max(1, Carbon::parse($checkIn)->diffInDays(Carbon::parse($checkOut)));
        $pricingMode = $unit->pricing_mode ?? 'Per Unit';
        $nightlyRate = (float) $unit->price;
        $payingGuestCount ??= 0;
        $standardCapacity = (int) $unit->capacity;
        $extraGuestCount = $pricingMode === 'Per Unit'
            ? max(0, $payingGuestCount - $standardCapacity)
            : 0;
        $extraGuestFee = (float) ($unit->extra_guest_fee ?? 0);
        $roomSubtotal = $pricingMode === 'Per Head'
            ? round($nights * $nightlyRate * $payingGuestCount, 2)
            : round($nights * $nightlyRate, 2);
        $extraGuestTotal = round($extraGuestCount * $extraGuestFee * $nights, 2);
        $total = round($roomSubtotal + $extraGuestTotal, 2);

        return [
            'nights' => $nights,
            'pricing_mode' => $pricingMode,
            'nightly_rate' => $nightlyRate,
            'room_subtotal' => $roomSubtotal,
            'extra_guest_count' => $extraGuestCount,
            'extra_guest_fee' => $extraGuestFee,
            'extra_guest_total' => $extraGuestTotal,
            'total' => $total,
        ];
    }

    /**
     * @param  array<string, int|float|string>  $pricing
     * @return array<int, string>
     */
    public function allocateGuestShares(array $pricing, int $payingGuestCount): array
    {
        if ($payingGuestCount < 1) {
            return [];
        }

        if ($pricing['pricing_mode'] === 'Per Head') {
            $shareCents = (int) round((float) $pricing['nightly_rate'] * (int) $pricing['nights'] * 100);

            return array_fill(0, $payingGuestCount, number_format($shareCents / 100, 2, '.', ''));
        }

        $totalCents = (int) round((float) $pricing['total'] * 100);
        $baseShareCents = intdiv($totalCents, $payingGuestCount);
        $remainderCents = $totalCents % $payingGuestCount;
        $shares = array_fill(0, $payingGuestCount, number_format($baseShareCents / 100, 2, '.', ''));
        $shares[$payingGuestCount - 1] = number_format(($baseShareCents + $remainderCents) / 100, 2, '.', '');

        return $shares;
    }

    /**
     * Validates availability, creates the booking, and notifies the Guest
     * and assigned house team. Throws ValidationException on conflict so
     * the Livewire component can show it as a form error.
     */
    public function createBooking(User $guest, Unit $unit, array $data): Booking
    {
        return $this->createBookingRecord($guest, $unit, $data, BookingStatus::PENDING);
    }

    /**
     * Create a confirmed account-less booking and record its first payment atomically.
     *
     * @param  array<string, mixed>  $data
     */
    public function createWalkInBooking(User $actor, Unit $unit, array $data): Booking
    {
        if (($data['check_in_now'] ?? false)
            && Carbon::parse($data['check_in'])->toDateString() !== Carbon::today('Asia/Manila')->toDateString()) {
            throw ValidationException::withMessages([
                'check_in_now' => 'Check-in now is only available when the stay starts today.',
            ]);
        }

        return DB::transaction(function () use ($actor, $unit, $data): Booking {
            $booking = $this->createBookingRecord(null, $unit, $data, BookingStatus::CONFIRMED);

            $this->workflow->recordCheckoutPayment(
                $actor,
                $booking->booking_id,
                $data['payment_method'],
                (float) $data['payment_amount'],
                $data['payment_reference'] ?? null,
                null,
                null
            );

            if ($data['check_in_now'] ?? false) {
                $booking = $this->workflow->checkInGuest($actor, $booking->booking_id, true);
            }

            return $booking->load(['unit.house', 'guests', 'payments']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function createBookingRecord(?User $guest, Unit $unit, array $data, string $status): Booking
    {
        return DB::transaction(function () use ($guest, $unit, $data, $status): Booking {
            $lockedUnit = Unit::query()
                ->where('unit_id', $unit->unit_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedUnit->status !== 'Available') {
                throw ValidationException::withMessages([
                    'check_in' => 'This unit is no longer available for booking.',
                ]);
            }

            $dateRangeConflict = $this->dateRangeConflict($lockedUnit, $data['check_in'], $data['check_out']);

            if ($dateRangeConflict === 'blocked') {
                throw ValidationException::withMessages([
                    'check_in' => 'One or more selected dates are unavailable. Please choose different dates.',
                ]);
            }

            if ($dateRangeConflict === 'booking') {
                throw ValidationException::withMessages([
                    'check_in' => 'These dates are already confirmed. Please choose different dates.',
                ]);
            }

            $guestRows = $this->bookingGuestRows($lockedUnit, $data);
            $capacityCount = count($guestRows);

            $maximumCapacity = (int) ($lockedUnit->maximum_capacity ?? $lockedUnit->capacity);
            if ($capacityCount > $maximumCapacity) {
                throw ValidationException::withMessages([
                    'guests' => 'The total number of guests, including free children, cannot exceed this unit’s maximum capacity.',
                ]);
            }

            $payingGuestCount = count(array_filter($guestRows, fn (array $guestRow): bool => ! $guestRow['is_free_child']));
            if (! $guest && isset($data['walk_in_name'])) {
                $guestRows[0]['full_name'] = trim((string) $data['walk_in_name']);
            }
            $pricing = $this->calculate(
                $lockedUnit,
                $data['check_in'],
                $data['check_out'],
                $payingGuestCount
            );

            $booking = Booking::create([
                'unit_id' => $lockedUnit->unit_id,
                'user_id' => $guest?->user_id,
                'walk_in_phone' => $guest ? null : ($data['walk_in_phone'] ?? null),
                'walk_in_email' => $guest ? null : ($data['walk_in_email'] ?? null),
                'check_in' => $data['check_in'],
                'check_out' => $data['check_out'],
                'adult_count' => $data['adult_count'],
                'child_count' => $data['child_count'],
                'total_price' => $pricing['total'],
                'pricing_mode' => $pricing['pricing_mode'],
                'nightly_rate' => $pricing['nightly_rate'],
                'room_subtotal' => $pricing['room_subtotal'],
                'extra_guest_count' => $pricing['extra_guest_count'],
                'extra_guest_fee' => $pricing['extra_guest_fee'],
                'extra_guest_total' => $pricing['extra_guest_total'],
                'status' => $status,
            ]);

            $payingGuestShares = $this->allocateGuestShares($pricing, $payingGuestCount);
            $payingGuestIndex = 0;

            foreach (array_values($guestRows) as $index => $guestData) {
                $guestShareAmount = $guestData['is_free_child']
                    ? '0.00'
                    : $payingGuestShares[$payingGuestIndex++];
                $bookingGuest = BookingGuest::create([
                    'booking_id' => $booking->booking_id,
                    'user_id' => $index === 0 ? $guest?->user_id : null,
                    'full_name' => $guestData['full_name'],
                    'guest_type' => $guestData['guest_type'],
                    'age' => $guestData['age'],
                    'is_free_child' => $guestData['is_free_child'],
                    'share_amount' => $guestShareAmount,
                    'is_primary_guest' => $index === 0,
                ]);

                if (in_array($guestData['discount_type'] ?? null, ['Senior Citizen', 'PWD'], true)) {
                    $discountAmount = round($guestShareAmount * 0.2, 2);

                    BookingDiscount::create([
                        'booking_id' => $booking->booking_id,
                        'booking_guest_id' => $bookingGuest->booking_guest_id,
                        'discount_type' => $guestData['discount_type'],
                        'id_reference' => $guestData['id_reference'],
                        'verification_status' => BookingStatus::PENDING,
                        'eligible_amount' => $guestShareAmount,
                        'discount_rate' => 20,
                        'discount_amount' => $discountAmount,
                        'final_amount' => round($guestShareAmount - $discountAmount, 2),
                        'refund_amount' => 0,
                        'refund_status' => 'None',
                    ]);
                }
            }

            if ($guest) {
                $this->workflow->notifyBookingRequested($booking, $lockedUnit, $guest);
            }

            return $booking;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<int, array{full_name: string, guest_type: string, age: ?int, is_free_child: bool, discount_type: ?string, id_reference: ?string}>
     */
    private function bookingGuestRows(Unit $unit, array $data): array
    {
        $adultCount = (int) $data['adult_count'];
        $childCount = (int) $data['child_count'];

        if ($adultCount < 1 || $childCount < 0) {
            throw ValidationException::withMessages([
                'adult_count' => 'At least one adult and a non-negative child count are required.',
            ]);
        }

        if (! array_key_exists('child_ages', $data) && isset($data['guests'])) {
            if (count($data['guests']) !== $adultCount + $childCount) {
                throw ValidationException::withMessages([
                    'guests' => 'The guest details must match the adult and child counts.',
                ]);
            }

            return collect(array_values($data['guests']))
                ->map(function (array $guest, int $index) use ($unit, $adultCount): array {
                    $guestType = $index < $adultCount ? 'adult' : 'child';
                    $age = $guestType === 'child' && isset($guest['age']) ? (int) $guest['age'] : null;
                    $isFreeChild = $guestType === 'child'
                        && $unit->child_free_through_age !== null
                        && $age !== null
                        && $age <= $unit->child_free_through_age;

                    return [
                        'full_name' => $guest['full_name'] ?? ucfirst($guestType).' '.($index + 1),
                        'guest_type' => $guestType,
                        'age' => $age,
                        'is_free_child' => $isFreeChild,
                        'discount_type' => $guest['discount_type'] ?? null,
                        'id_reference' => $guest['id_reference'] ?? null,
                    ];
                })
                ->all();
        }

        $seniorCount = (int) ($data['senior_count'] ?? 0);
        $pwdCount = (int) ($data['pwd_count'] ?? 0);
        $seniorIds = $data['senior_ids'] ?? [];
        $pwdIds = $data['pwd_ids'] ?? [];
        $childAges = $data['child_ages'] ?? [];
        $freeChildCount = (int) ($data['free_child_count'] ?? 0);

        if ($seniorCount < 0 || $pwdCount < 0 || $seniorCount + $pwdCount > $adultCount) {
            throw ValidationException::withMessages([
                'senior_count' => 'Senior Citizen and PWD counts cannot exceed the adult count.',
            ]);
        }

        if (! is_array($seniorIds) || count($seniorIds) !== $seniorCount
            || ! is_array($pwdIds) || count($pwdIds) !== $pwdCount) {
            throw ValidationException::withMessages([
                'senior_ids' => 'Provide an ID reference for each Senior Citizen and PWD guest.',
            ]);
        }

        foreach (array_merge($seniorIds, $pwdIds) as $idReference) {
            if (! is_string($idReference) || trim($idReference) === '' || mb_strlen($idReference) > 100) {
                throw ValidationException::withMessages([
                    'senior_ids' => 'Each Senior Citizen or PWD ID reference is required and must not exceed 100 characters.',
                ]);
            }
        }

        $rows = [];
        for ($index = 0; $index < $adultCount; $index++) {
            $discountType = null;
            $idReference = null;
            $label = 'Adult '.($index + 1);

            if ($index < $seniorCount) {
                $discountType = 'Senior Citizen';
                $idReference = trim($seniorIds[$index]);
                $label = 'Senior Citizen '.($index + 1);
            } elseif ($index < $seniorCount + $pwdCount) {
                $pwdIndex = $index - $seniorCount;
                $discountType = 'PWD';
                $idReference = trim($pwdIds[$pwdIndex]);
                $label = 'PWD '.($pwdIndex + 1);
            }

            $rows[] = [
                'full_name' => $label,
                'guest_type' => 'adult',
                'age' => null,
                'is_free_child' => false,
                'discount_type' => $discountType,
                'id_reference' => $idReference,
            ];
        }

        if (array_key_exists('free_child_count', $data)) {
            if ($freeChildCount < 0
                || $freeChildCount > $childCount
                || ($freeChildCount > 0 && $unit->child_free_through_age === null)) {
                throw ValidationException::withMessages([
                    'free_child_count' => 'Enter a valid count of children within the owner’s free-child age limit.',
                ]);
            }

            $countedChildren = $childCount - $freeChildCount;

            for ($index = 0; $index < $countedChildren; $index++) {
                $rows[] = [
                    'full_name' => 'Child '.($index + 1),
                    'guest_type' => 'child',
                    'age' => null,
                    'is_free_child' => false,
                    'discount_type' => null,
                    'id_reference' => null,
                ];
            }

            for ($index = 0; $index < $freeChildCount; $index++) {
                $rows[] = [
                    'full_name' => 'Child '.($countedChildren + $index + 1),
                    'guest_type' => 'child',
                    'age' => null,
                    'is_free_child' => true,
                    'discount_type' => null,
                    'id_reference' => null,
                ];
            }

            return $rows;
        }

        if (! is_array($childAges) || count($childAges) !== $childCount) {
            throw ValidationException::withMessages([
                'child_ages' => 'Provide an age for each child.',
            ]);
        }

        foreach (array_values($childAges) as $index => $age) {
            if (filter_var($age, FILTER_VALIDATE_INT) === false || (int) $age < 0 || (int) $age > 17) {
                throw ValidationException::withMessages([
                    "child_ages.{$index}" => 'Child ages must be whole numbers between 0 and 17.',
                ]);
            }

            $age = (int) $age;
            $isFreeChild = $unit->child_free_through_age !== null && $age <= $unit->child_free_through_age;
            $rows[] = [
                'full_name' => 'Child '.($index + 1),
                'guest_type' => 'child',
                'age' => $age,
                'is_free_child' => $isFreeChild,
                'discount_type' => null,
                'id_reference' => null,
            ];
        }

        return $rows;
    }

    private function hasActiveBooking(Unit $unit, string $checkIn, string $checkOut): bool
    {
        $checkInDate = Carbon::parse($checkIn);
        $checkOutDate = Carbon::parse($checkOut);

        return $unit->bookings()
            ->whereIn('status', [BookingStatus::ACCEPTED, BookingStatus::CONFIRMED, BookingStatus::CHECKED_IN])
            ->whereDate('check_in', '<', $checkOutDate->toDateString())
            ->whereDate('check_out', '>', $checkInDate->toDateString())
            ->exists();
    }
}
