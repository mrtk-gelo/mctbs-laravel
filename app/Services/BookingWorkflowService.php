<?php

namespace App\Services;

use App\Mail\BookingAccepted;
use App\Mail\BookingFeedbackRequested;
use App\Models\AdditionalCharge;
use App\Models\Booking;
use App\Models\BookingAddon;
use App\Models\BookingDamage;
use App\Models\BookingDiscount;
use App\Models\BookingEdit;
use App\Models\BookingGuest;
use App\Models\BookingGuestEvent;
use App\Models\BookingReceipt;
use App\Models\DamageReport;
use App\Models\Invitation;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\Role;
use App\Models\TransactionArchive;
use App\Models\TransientHouse;
use App\Models\Unit;
use App\Models\UnitAddon;
use App\Models\UnitCalendar;
use App\Models\UnitDamageItem;
use App\Models\User;
use App\Support\BookingFinancialContract;
use App\Support\BookingStatus;
use App\Support\PaymentStatus;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BookingWorkflowService
{
    public function notifyBookingRequested(Booking $booking, Unit $unit, User $guest): void
    {
        $pendingRequests = $this->overlappingPendingBookings($booking);
        $overlappingCount = $pendingRequests->count();
        $heldByAcceptedRequest = Booking::query()
            ->where('unit_id', $booking->unit_id)
            ->whereIn('status', [BookingStatus::ACCEPTED, BookingStatus::CONFIRMED, BookingStatus::CHECKED_IN])
            ->whereDate('check_in', '<', $booking->check_out->toDateString())
            ->whereDate('check_out', '>', $booking->check_in->toDateString())
            ->exists();
        $queuePosition = $pendingRequests->search(fn (Booking $request): bool => $request->booking_id === $booking->booking_id) + 1;
        $queueMessage = $heldByAcceptedRequest
            ? 'These dates are already reserved by an earlier booking.'
            : ($overlappingCount > 1
                ? "There are {$overlappingCount} overlapping requests. Your request is #{$queuePosition} in submission order."
                : 'You are the first request for these dates.');

        $this->notifyUser(
            $guest,
            $booking,
            'booking_submitted',
            'Booking request submitted',
            "Your request for {$unit->unit_type} is pending Owner/Staff review. {$queueMessage}"
        );

        if ($overlappingCount > 1) {
            foreach ($pendingRequests as $position => $request) {
                if ($request->booking_id === $booking->booking_id) {
                    continue;
                }

                $request->load('guest');
                $this->notifyUser(
                    $request->guest,
                    $request,
                    'booking_queue_updated',
                    'Another request joined the queue',
                    "There are {$overlappingCount} overlapping requests. Your request is #".($position + 1).' in submission order.'
                );
            }
        }

        $this->notifyHouse(
            $unit->house,
            $booking,
            'booking_requested',
            'New booking request',
            "{$guest->first_name} {$guest->last_name} requested {$unit->unit_type}. {$queueMessage}"
        );
    }

    public function accept(User $actor, int $bookingId): Booking
    {
        $booking = Booking::with(['unit.house', 'guest'])->findOrFail($bookingId);
        $this->authorizeHouse($actor, $booking->unit->house);
        $acceptedBooking = DB::transaction(function () use ($booking): Booking {
            $unit = Unit::query()->whereKey($booking->unit_id)->lockForUpdate()->firstOrFail();
            $booking = Booking::query()->whereKey($booking->booking_id)->lockForUpdate()->firstOrFail();

            if ($booking->status !== BookingStatus::PENDING) {
                throw ValidationException::withMessages(['booking' => 'Only pending booking requests can be accepted.']);
            }

            $firstPending = $this->overlappingPendingBookings($booking)->first();
            if ($firstPending && $firstPending->booking_id !== $booking->booking_id) {
                throw ValidationException::withMessages(['booking' => 'Accept the earliest overlapping request first.']);
            }

            if ($this->hasActiveOverlap($booking)) {
                throw ValidationException::withMessages(['booking' => 'Another accepted or confirmed booking already holds these dates.']);
            }

            if ($unit->status !== 'Available' || $this->hasBlockedDates($unit, $booking)) {
                throw ValidationException::withMessages(['booking' => 'These dates are blocked on the unit calendar.']);
            }

            $booking->status = BookingStatus::ACCEPTED;
            $acceptedAt = now();
            $booking->accepted_at = $acceptedAt;
            $booking->payment_deadline = $acceptedAt->copy()->addHours(
                (int) config('booking.payment_deadline_hours')
            );
            $booking->save();
            $booking->load(['guest', 'unit.house']);
            $this->reserveCalendarDates($unit, $booking);

            $this->notifyUser(
                $booking->guest,
                $booking,
                'booking_accepted',
                'Booking accepted — downpayment required',
                "Your booking #{$booking->booking_id} was accepted. Submit the ₱".number_format($booking->downpaymentAmount(), 2)
                    .' downpayment to confirm your reservation.'
            );
            $this->notifyHouse(
                $booking->unit->house,
                $booking,
                'booking_accepted',
                'Booking accepted',
                "Booking #{$booking->booking_id} was accepted and its dates are reserved pending the guest's downpayment."
            );

            return $booking;
        });

        if ($acceptedBooking->guest?->email) {
            Mail::to($acceptedBooking->guest->email)->send(new BookingAccepted($acceptedBooking));
        }

        return $acceptedBooking;
    }

    public function reject(User $actor, int $bookingId, string $reason): Booking
    {
        $reason = trim($reason);
        if ($reason === '') {
            throw ValidationException::withMessages(['reason' => 'Please explain why you are rejecting this booking.']);
        }

        $booking = Booking::with(['unit.house', 'guest'])->findOrFail($bookingId);
        $this->authorizeHouse($actor, $booking->unit->house);

        return DB::transaction(function () use ($booking, $reason): Booking {
            $unit = Unit::query()->whereKey($booking->unit_id)->lockForUpdate()->firstOrFail();
            $booking = Booking::query()->whereKey($booking->booking_id)->lockForUpdate()->firstOrFail();

            if ($booking->status !== BookingStatus::PENDING) {
                throw ValidationException::withMessages(['booking' => 'Only pending booking requests can be rejected.']);
            }

            $booking->status = BookingStatus::REJECTED;
            $booking->rejection_reason = $reason;
            $booking->save();
            $this->releaseCalendarDates($unit, $booking);
            $booking->load(['guest', 'unit.house']);

            $this->notifyUser(
                $booking->guest,
                $booking,
                'booking_rejected',
                'Booking request rejected',
                "Your booking request was not accepted. Reason: {$reason}"
            );
            $this->notifyHouse(
                $booking->unit->house,
                $booking,
                'booking_rejected',
                'Booking request rejected',
                "Booking #{$booking->booking_id} was rejected. Reason: {$reason}"
            );
            $this->notifyNextWaitingRequest($booking);

            return $booking;
        });
    }

    public function cancelAccepted(User $actor, int $bookingId, string $reason): Booking
    {
        $reason = trim($reason);
        if ($reason === '') {
            throw ValidationException::withMessages(['reason' => 'Please explain why you are cancelling this booking.']);
        }

        $booking = Booking::with(['unit.house', 'guest'])->findOrFail($bookingId);
        $this->authorizeHouse($actor, $booking->unit->house);

        return DB::transaction(function () use ($booking, $actor, $reason): Booking {
            $unit = Unit::query()->whereKey($booking->unit_id)->lockForUpdate()->firstOrFail();
            $booking = Booking::query()->whereKey($booking->booking_id)->lockForUpdate()->firstOrFail();

            if (! in_array($booking->status, [BookingStatus::PENDING, BookingStatus::ACCEPTED, BookingStatus::CONFIRMED], true)) {
                throw ValidationException::withMessages(['booking' => 'Only pending, accepted, or confirmed bookings can be cancelled by Owner/Staff.']);
            }

            $booking->status = BookingStatus::CANCELLED;
            $booking->cancellation_reason = $reason;
            $booking->save();
            $this->releaseCalendarDates($unit, $booking);
            $booking->load(['guest', 'unit.house']);

            $this->notifyUser(
                $booking->guest,
                $booking,
                'booking_cancelled',
                'Booking cancelled by Owner/Staff',
                "Your booking was cancelled. Reason: {$reason}"
            );
            $this->notifyHouse(
                $booking->unit->house,
                $booking,
                'booking_cancelled',
                'Booking cancelled',
                "Booking #{$booking->booking_id} was cancelled by {$actor->first_name} {$actor->last_name}. Reason: {$reason}"
            );
            $this->notifyNextWaitingRequest($booking);

            return $booking;
        });
    }

    public function cancelByGuest(User $guest, int $bookingId, string $reason): Booking
    {
        $reason = trim($reason);
        if ($reason === '') {
            throw ValidationException::withMessages(['reason' => 'Please explain why you are cancelling this booking.']);
        }

        $booking = Booking::query()->with(['unit.house', 'guest'])->findOrFail($bookingId);
        abort_unless($booking->user_id === $guest->user_id, 404);

        return DB::transaction(function () use ($booking, $guest, $reason): Booking {
            $unit = Unit::query()->whereKey($booking->unit_id)->lockForUpdate()->firstOrFail();
            $booking = Booking::query()->whereKey($booking->booking_id)->lockForUpdate()->firstOrFail();

            $isPendingRequest = $booking->status === BookingStatus::PENDING;
            $isUpcomingConfirmedBooking = in_array($booking->status, [BookingStatus::ACCEPTED, BookingStatus::CONFIRMED], true)
                && $booking->check_in->toDateString() > today()->toDateString();
            if (! $isPendingRequest && ! $isUpcomingConfirmedBooking) {
                throw ValidationException::withMessages([
                    'booking' => 'Only pending requests or upcoming accepted and confirmed bookings can be cancelled here.',
                ]);
            }

            $booking->status = BookingStatus::CANCELLED;
            $booking->cancellation_reason = 'Cancelled by guest: '.$reason;
            $booking->save();

            $this->releaseCalendarDates($unit, $booking);

            $booking->load(['guest', 'unit.house']);
            $this->notifyHouse(
                $booking->unit->house,
                $booking,
                'booking_cancelled',
                'Guest cancelled booking',
                "{$guest->first_name} {$guest->last_name} cancelled booking #{$booking->booking_id}. Reason: {$reason}"
            );
            $this->notifyNextWaitingRequest($booking);

            return $booking;
        });
    }

    /**
     * @param  array{payment_method: string, amount: float, reference_number?: ?string, payment_receipt?: ?string}  $data
     */
    public function submitPayment(User $guest, int $bookingId, array $data): Payment
    {
        $booking = Booking::query()->with(['unit.house', 'guest'])->findOrFail($bookingId);
        abort_unless($booking->user_id === $guest->user_id, 404);

        return DB::transaction(function () use ($booking, $data): Payment {
            $lockedBooking = Booking::query()->whereKey($booking->booking_id)->lockForUpdate()->firstOrFail();

            if ($lockedBooking->status !== BookingStatus::ACCEPTED) {
                throw ValidationException::withMessages([
                    'booking' => 'A downpayment can only be submitted for an accepted booking awaiting payment.',
                ]);
            }
            if ($lockedBooking->payments()->where('payment_status', PaymentStatus::UNDER_REVIEW)->exists()) {
                throw ValidationException::withMessages([
                    'payment' => 'A downpayment submission is already waiting for review.',
                ]);
            }

            $verifiedPayments = BookingFinancialContract::verifiedPayments($lockedBooking);
            $remainingDownpayment = max(0, round($lockedBooking->downpaymentAmount() - $verifiedPayments, 2));
            $remainingBalance = $lockedBooking->outstandingBalance();
            $submittedAmount = round((float) $data['amount'], 2);

            if ($submittedAmount < $remainingDownpayment
                || $submittedAmount > $remainingBalance
                || $remainingDownpayment <= 0) {
                throw ValidationException::withMessages([
                    'amount' => 'The payment must be at least the remaining downpayment and cannot exceed the current balance.',
                ]);
            }

            $referenceNumber = $this->ensureReferenceNumberAvailable($data['reference_number'] ?? null);
            $payment = Payment::create([
                'booking_id' => $lockedBooking->booking_id,
                'payment_method' => $data['payment_method'],
                'amount' => $submittedAmount,
                'payment_status' => PaymentStatus::UNDER_REVIEW,
                'payment_date' => now(),
                'reference_number' => $referenceNumber,
                'payment_receipt' => $data['payment_receipt'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->notifyHouse(
                $booking->unit->house,
                $booking,
                'payment_submitted',
                'Payment submitted',
                'A payment of ₱'.number_format($submittedAmount, 2)." was submitted for booking #{$booking->booking_id} and is awaiting review."
            );

            return $payment;
        });
    }

    public function reviewPayment(User $actor, int $paymentId, string $status, ?string $notes): Payment
    {
        $notes = trim((string) $notes);
        if ($status !== PaymentStatus::VERIFIED && $notes === '') {
            throw ValidationException::withMessages(['notes' => 'Please explain why this payment cannot be accepted.']);
        }

        $payment = Payment::with(['booking.unit.house', 'booking.guest'])->findOrFail($paymentId);
        $booking = $payment->booking;
        $this->authorizeHouse($actor, $booking->unit->house);

        return DB::transaction(function () use ($payment, $booking, $actor, $status, $notes): Payment {
            Unit::query()->whereKey($booking->unit_id)->lockForUpdate()->firstOrFail();
            $booking = Booking::query()->whereKey($booking->booking_id)->lockForUpdate()->firstOrFail();
            $payment = Payment::query()->whereKey($payment->payment_id)->lockForUpdate()->firstOrFail();

            if (! in_array($booking->status, [BookingStatus::ACCEPTED, BookingStatus::CONFIRMED], true)
                || $payment->payment_status !== PaymentStatus::UNDER_REVIEW) {
                throw ValidationException::withMessages(['payment' => 'This payment is no longer awaiting review.']);
            }

            $bookingWasAccepted = $booking->status === BookingStatus::ACCEPTED;
            if ($status === PaymentStatus::VERIFIED && $bookingWasAccepted) {
                $verifiedBefore = BookingFinancialContract::verifiedPayments($booking);
                $requiredAmount = max(0, round($booking->downpaymentAmount() - $verifiedBefore, 2));
                $remainingBalance = $booking->outstandingBalance();

                if (round((float) $payment->amount, 2) < $requiredAmount
                    || round((float) $payment->amount, 2) > $remainingBalance
                    || $requiredAmount <= 0) {
                    throw ValidationException::withMessages([
                        'payment' => 'The payment is below the remaining downpayment or exceeds the current balance, so it cannot be verified.',
                    ]);
                }
            }

            $payment->payment_status = $status;
            $payment->notes = $status === PaymentStatus::VERIFIED || $notes === '' ? null : $notes;
            $payment->save();

            if ($status === PaymentStatus::VERIFIED && $bookingWasAccepted) {
                $booking->status = BookingStatus::CONFIRMED;
                $booking->booking_reference ??= 'MCTBS-'.str_pad((string) $booking->booking_id, 6, '0', STR_PAD_LEFT);
                $booking->payment_deadline = null;
                $booking->save();
                $booking->load(['guest', 'unit.house']);
                $this->cancelOverlappingRequests($booking);

                $this->notifyUser(
                    $booking->guest,
                    $booking,
                    'booking_confirmed',
                    'Booking confirmed',
                    "The payment for booking #{$booking->booking_id} was verified. The dates are confirmed and any remaining balance is due at checkout."
                );
                $this->notifyHouse(
                    $booking->unit->house,
                    $booking,
                    'booking_confirmed',
                    'Booking confirmed',
                    "The downpayment for booking #{$booking->booking_id} was verified and the booking is confirmed."
                );

                return $payment;
            }

            $suffix = $this->notificationSuffix($status);
            $message = $notes ?: "Payment review result: {$status}.";

            $booking->load(['guest', 'unit.house']);
            $this->notifyUser($booking->guest, $booking, 'payment_'.$suffix, 'Payment '.$suffix, $message);
            $this->notifyHouse(
                $booking->unit->house,
                $booking,
                'payment_reviewed',
                'Payment review updated',
                "Payment for booking #{$booking->booking_id} was marked {$status} by {$actor->first_name} {$actor->last_name}."
            );

            return $payment;
        });
    }

    public function isPaymentOverdue(Booking $booking): bool
    {
        if ($booking->status !== BookingStatus::ACCEPTED
            || ! $booking->payment_deadline
            || $booking->payment_deadline->isFuture()) {
            return false;
        }

        $timelyPaymentQuery = $booking->payments()
            ->whereIn('payment_status', [PaymentStatus::UNDER_REVIEW, PaymentStatus::VERIFIED])
            ->where('payment_date', '<=', $booking->payment_deadline);
        if ($booking->accepted_at) {
            $timelyPaymentQuery->where('payment_date', '>=', $booking->accepted_at);
        }
        $timelyPayments = (float) $timelyPaymentQuery->sum('amount');

        return round($timelyPayments, 2) < $booking->downpaymentAmount();
    }

    public function checkInGuest(User $actor, int $bookingId, bool $guestDetailsConfirmed = false): Booking
    {
        $booking = Booking::query()
            ->with(['unit.house'])
            ->findOrFail($bookingId);
        $this->authorizeHouse($actor, $booking->unit->house);

        return DB::transaction(function () use ($actor, $booking, $guestDetailsConfirmed): Booking {
            $lockedBooking = Booking::query()
                ->whereKey($booking->booking_id)
                ->lockForUpdate()
                ->with(['unit.house'])
                ->firstOrFail();
            $this->authorizeHouse($actor, $lockedBooking->unit->house);

            if ($lockedBooking->status !== BookingStatus::CONFIRMED) {
                throw ValidationException::withMessages([
                    'check_in' => 'Only confirmed bookings can be checked in.',
                ]);
            }

            if (! $guestDetailsConfirmed) {
                throw ValidationException::withMessages([
                    'guest_details_confirmed' => 'Confirm the booking reference and guest headcount before completing check-in.',
                ]);
            }

            $verifiedPayments = $lockedBooking->payments()
                ->where('payment_status', PaymentStatus::VERIFIED);
            $hasVerifiedPayment = $verifiedPayments->exists();
            $verifiedAmount = (float) $verifiedPayments->sum('amount');

            if (! $hasVerifiedPayment || $verifiedAmount < $lockedBooking->downpaymentAmount()) {
                throw ValidationException::withMessages([
                    'check_in' => 'A verified downpayment is required before check-in.',
                ]);
            }

            $lockedBooking->status = BookingStatus::CHECKED_IN;
            $lockedBooking->checked_in_at = now();
            $lockedBooking->save();

            return $lockedBooking;
        });
    }

    public function recordGuestArrival(User $actor, int $bookingId, int $bookingGuestId): BookingGuest
    {
        return DB::transaction(function () use ($actor, $bookingId, $bookingGuestId): BookingGuest {
            $booking = $this->lockCheckInBooking($actor, $bookingId);
            $guest = $this->lockBookingGuest($booking, $bookingGuestId);

            if ($guest->roster_status !== BookingGuest::ROSTER_ACTIVE
                || ($guest->attendance_status ?? BookingGuest::ATTENDANCE_EXPECTED) !== BookingGuest::ATTENDANCE_EXPECTED) {
                throw ValidationException::withMessages([
                    'attendance' => 'Only active roster members with Expected attendance can be marked Arrived.',
                ]);
            }

            $eventAt = now();
            $guest->attendance_status = BookingGuest::ATTENDANCE_ARRIVED;
            $guest->save();

            BookingGuestEvent::create([
                'booking_id' => $booking->booking_id,
                'booking_guest_id' => $guest->booking_guest_id,
                'event_type' => BookingGuestEvent::TYPE_ARRIVAL,
                'performed_by' => $actor->user_id,
                'event_at' => $eventAt,
                'before_state' => ['attendance_status' => BookingGuest::ATTENDANCE_EXPECTED],
                'after_state' => ['attendance_status' => BookingGuest::ATTENDANCE_ARRIVED],
            ]);

            return $guest;
        });
    }

    public function markGuestNoShow(
        User $actor,
        int $bookingId,
        int $bookingGuestId,
        ?string $reason = null
    ): BookingGuest {
        $reason = trim((string) $reason) ?: null;

        return DB::transaction(function () use ($actor, $bookingId, $bookingGuestId, $reason): BookingGuest {
            $booking = $this->lockCheckInBooking($actor, $bookingId);
            $guest = $this->lockBookingGuest($booking, $bookingGuestId);

            if ($guest->roster_status !== BookingGuest::ROSTER_ACTIVE
                || ($guest->attendance_status ?? BookingGuest::ATTENDANCE_EXPECTED) !== BookingGuest::ATTENDANCE_EXPECTED) {
                throw ValidationException::withMessages([
                    'attendance' => 'Only active roster members with Expected attendance can be marked No-show.',
                ]);
            }

            $eventAt = now();
            $guest->attendance_status = BookingGuest::ATTENDANCE_NO_SHOW;
            $guest->save();

            BookingGuestEvent::create([
                'booking_id' => $booking->booking_id,
                'booking_guest_id' => $guest->booking_guest_id,
                'event_type' => BookingGuestEvent::TYPE_NO_SHOW,
                'performed_by' => $actor->user_id,
                'reason' => $reason,
                'event_at' => $eventAt,
                'before_state' => ['attendance_status' => BookingGuest::ATTENDANCE_EXPECTED],
                'after_state' => ['attendance_status' => BookingGuest::ATTENDANCE_NO_SHOW],
            ]);

            return $guest;
        });
    }

    public function replaceExpectedGuest(
        User $actor,
        int $bookingId,
        int $bookingGuestId,
        string $replacementName,
        ?string $reason = null
    ): BookingGuest {
        $replacementName = trim($replacementName);
        $reason = trim((string) $reason) ?: null;
        if ($replacementName === '') {
            throw ValidationException::withMessages([
                'full_name' => 'A replacement guest name is required.',
            ]);
        }

        return DB::transaction(function () use (
            $actor,
            $bookingId,
            $bookingGuestId,
            $replacementName,
            $reason
        ): BookingGuest {
            $booking = $this->lockCheckInBooking($actor, $bookingId);
            $guest = $this->lockBookingGuest($booking, $bookingGuestId);

            if ($guest->roster_status !== BookingGuest::ROSTER_ACTIVE
                || ($guest->attendance_status ?? BookingGuest::ATTENDANCE_EXPECTED) !== BookingGuest::ATTENDANCE_EXPECTED) {
                throw ValidationException::withMessages([
                    'attendance' => 'Only active roster members with Expected attendance can be replaced.',
                ]);
            }

            $eventAt = now();
            $beforeState = [
                'attendance_status' => BookingGuest::ATTENDANCE_EXPECTED,
                'is_primary_guest' => $guest->is_primary_guest,
            ];
            $isPrimaryGuest = $guest->is_primary_guest;
            $guest->attendance_status = BookingGuest::ATTENDANCE_REPLACED;
            $guest->is_primary_guest = false;
            $guest->save();

            $replacement = $booking->guests()->create([
                'full_name' => $replacementName,
                'guest_type' => $guest->guest_type,
                'age' => $guest->age,
                'is_free_child' => $guest->is_free_child,
                'share_amount' => $guest->share_amount,
                'is_primary_guest' => $isPrimaryGuest,
                'roster_status' => BookingGuest::ROSTER_ACTIVE,
                'attendance_status' => BookingGuest::ATTENDANCE_EXPECTED,
                'replaces_booking_guest_id' => $guest->booking_guest_id,
                'identity_verification_status' => BookingGuest::IDENTITY_UNVERIFIED,
            ]);

            BookingGuestEvent::create([
                'booking_id' => $booking->booking_id,
                'booking_guest_id' => $guest->booking_guest_id,
                'related_booking_guest_id' => $replacement->booking_guest_id,
                'event_type' => BookingGuestEvent::TYPE_REPLACEMENT,
                'performed_by' => $actor->user_id,
                'reason' => $reason,
                'event_at' => $eventAt,
                'before_state' => $beforeState,
                'after_state' => [
                    'attendance_status' => BookingGuest::ATTENDANCE_REPLACED,
                    'is_primary_guest' => false,
                    'replacement_booking_guest_id' => $replacement->booking_guest_id,
                    'replacement_attendance_status' => BookingGuest::ATTENDANCE_EXPECTED,
                ],
            ]);

            return $replacement;
        });
    }

    public function reviewGuestIdentity(
        User $actor,
        int $bookingId,
        int $bookingGuestId,
        string $identityStatus,
        ?string $reason = null
    ): BookingGuest {
        $reason = trim((string) $reason) ?: null;
        if (! in_array($identityStatus, [
            BookingGuest::IDENTITY_VERIFIED,
            BookingGuest::IDENTITY_EXCEPTION,
            BookingGuest::IDENTITY_FAILED,
        ], true)) {
            throw ValidationException::withMessages([
                'identity_status' => 'Choose a valid identity verification result.',
            ]);
        }
        if ($identityStatus === BookingGuest::IDENTITY_EXCEPTION && $reason === null) {
            throw ValidationException::withMessages([
                'reason' => 'An authorized identity exception requires a reason.',
            ]);
        }

        return DB::transaction(function () use (
            $actor,
            $bookingId,
            $bookingGuestId,
            $identityStatus,
            $reason
        ): BookingGuest {
            $booking = $this->lockCheckInBooking($actor, $bookingId);
            $guest = $this->lockBookingGuest($booking, $bookingGuestId);

            if ($guest->roster_status !== BookingGuest::ROSTER_ACTIVE
                || ($guest->attendance_status ?? BookingGuest::ATTENDANCE_EXPECTED) !== BookingGuest::ATTENDANCE_ARRIVED) {
                throw ValidationException::withMessages([
                    'identity' => 'Identity can only be reviewed for an active guest marked Arrived.',
                ]);
            }

            $eventAt = now();
            $beforeStatus = $guest->identity_verification_status ?? BookingGuest::IDENTITY_UNVERIFIED;
            $beforeState = [
                'identity_verification_status' => $beforeStatus,
                'identity_verified_by' => $guest->identity_verified_by,
                'identity_verified_at' => $guest->identity_verified_at?->toDateTimeString(),
                'identity_exception_reason' => $guest->identity_exception_reason,
            ];
            $guest->identity_verification_status = $identityStatus;
            $guest->identity_verified_by = $actor->user_id;
            $guest->identity_verified_at = $eventAt;
            $guest->identity_exception_reason = $identityStatus === BookingGuest::IDENTITY_EXCEPTION
                ? $reason
                : null;
            $guest->save();

            BookingGuestEvent::create([
                'booking_id' => $booking->booking_id,
                'booking_guest_id' => $guest->booking_guest_id,
                'event_type' => $identityStatus === BookingGuest::IDENTITY_EXCEPTION
                    ? BookingGuestEvent::TYPE_IDENTITY_EXCEPTION
                    : BookingGuestEvent::TYPE_IDENTITY_VERIFICATION,
                'performed_by' => $actor->user_id,
                'reason' => $reason,
                'event_at' => $eventAt,
                'before_state' => $beforeState,
                'after_state' => [
                    'identity_verification_status' => $identityStatus,
                    'identity_verified_by' => $actor->user_id,
                    'identity_verified_at' => $eventAt->toDateTimeString(),
                    'identity_exception_reason' => $guest->identity_exception_reason,
                ],
            ]);

            return $guest;
        });
    }

    /**
     * @param  array{check_in: string, check_out: string, adult_count?: int, child_count?: int}  $changes
     */
    public function updateCheckInHeadcount(
        User $actor,
        int $bookingId,
        int $adultCount,
        int $freeChildCount,
        BookingService $bookingService
    ): Booking {
        $booking = Booking::query()->findOrFail($bookingId);

        return $this->editBooking(
            $actor,
            $bookingId,
            [
                'check_in' => $booking->check_in->toDateString(),
                'check_out' => $booking->check_out->toDateString(),
                'adult_count' => $adultCount,
                'child_count' => $freeChildCount,
            ],
            'Guest headcount corrected at check-in.',
            $bookingService,
            true
        );
    }

    public function editBooking(
        User $actor,
        int $bookingId,
        array $changes,
        string $reason,
        BookingService $bookingService,
        bool $allowCheckInDayHeadcountUpdate = false
    ): Booking {
        $booking = Booking::query()->with(['unit.house', 'guest'])->findOrFail($bookingId);
        $this->authorizeHouse($actor, $booking->unit->house);

        return DB::transaction(function () use ($actor, $booking, $changes, $reason, $bookingService, $allowCheckInDayHeadcountUpdate): Booking {
            $unit = Unit::query()->whereKey($booking->unit_id)->lockForUpdate()->firstOrFail();
            $booking = Booking::query()
                ->with('guests')
                ->whereKey($booking->booking_id)
                ->lockForUpdate()
                ->firstOrFail();

            $isCheckedIn = $booking->status === BookingStatus::CHECKED_IN;
            $isCheckInDayHeadcountUpdate = $allowCheckInDayHeadcountUpdate
                && $booking->status === BookingStatus::CONFIRMED
                && $booking->check_in->toDateString() === today()->toDateString()
                && Carbon::parse($changes['check_in'])->toDateString() === $booking->check_in->toDateString()
                && Carbon::parse($changes['check_out'])->toDateString() === $booking->check_out->toDateString()
                && array_key_exists('adult_count', $changes)
                && array_key_exists('child_count', $changes);
            if ($allowCheckInDayHeadcountUpdate && ! $isCheckInDayHeadcountUpdate) {
                throw ValidationException::withMessages([
                    'adult_count' => 'Guest headcount can only be corrected for a confirmed booking on its check-in date.',
                ]);
            }
            if (! $isCheckedIn
                && ! in_array($booking->status, [BookingStatus::PENDING, BookingStatus::ACCEPTED, BookingStatus::CONFIRMED], true)) {
                throw ValidationException::withMessages(['booking' => 'This booking can no longer be edited.']);
            }
            if (! $isCheckedIn
                && ! $isCheckInDayHeadcountUpdate
                && $booking->status !== BookingStatus::PENDING
                && $booking->check_in->toDateString() <= today()->toDateString()) {
                throw ValidationException::withMessages(['booking' => 'A booking cannot be edited on or after its check-in date.']);
            }
            if ($booking->payments()->where('payment_status', PaymentStatus::UNDER_REVIEW)->exists()) {
                throw ValidationException::withMessages(['booking' => 'Wait until the submitted payment has been reviewed before editing this booking.']);
            }

            $adultCount = (int) ($changes['adult_count'] ?? $booking->adult_count);
            $childCount = (int) ($changes['child_count'] ?? $booking->child_count);
            if ($adultCount < 1 || $childCount < 0) {
                throw ValidationException::withMessages([
                    'adult_count' => 'At least one paying guest and a non-negative free-child count are required.',
                ]);
            }
            if ($isCheckedIn
                && ($adultCount < $booking->adult_count || $childCount < $booking->child_count)) {
                throw ValidationException::withMessages([
                    'adult_count' => 'Guest-count reductions cannot be made after check-in.',
                ]);
            }

            $checkIn = $isCheckedIn
                ? $booking->check_in->copy()->startOfDay()
                : Carbon::parse($changes['check_in'])->startOfDay();
            $checkOut = Carbon::parse($changes['check_out'])->startOfDay();
            if ((! $isCheckedIn
                    && ! $isCheckInDayHeadcountUpdate
                    && $checkIn->lessThanOrEqualTo(Carbon::tomorrow()->startOfDay()))
                || $checkOut->lessThanOrEqualTo($checkIn)) {
                throw ValidationException::withMessages([
                    'check_in' => $isCheckedIn
                        ? 'Check-out must be after check-in.'
                        : 'Choose a check-in date after tomorrow and a check-out date after check-in.',
                ]);
            }

            if (in_array($booking->status, [BookingStatus::ACCEPTED, BookingStatus::CONFIRMED, BookingStatus::CHECKED_IN], true)) {
                $unit->availability()
                    ->where('status', 'Booked')
                    ->where('note', 'Confirmed booking #'.$booking->booking_id)
                    ->delete();
            }

            if ((! $isCheckedIn && $unit->status !== 'Available')
                || Booking::query()
                    ->where('unit_id', $unit->unit_id)
                    ->where('booking_id', '!=', $booking->booking_id)
                    ->whereIn('status', [BookingStatus::ACCEPTED, BookingStatus::CONFIRMED, BookingStatus::CHECKED_IN])
                    ->whereDate('check_in', '<', $checkOut->toDateString())
                    ->whereDate('check_out', '>', $checkIn->toDateString())
                    ->exists()
                || $this->hasBlockedCalendarDates($unit, $checkIn->toDateString(), $checkOut->toDateString())) {
                throw ValidationException::withMessages(['check_in' => 'One or more selected dates are unavailable.']);
            }

            $oldCheckIn = $booking->check_in->copy();
            $oldCheckOut = $booking->check_out->copy();
            $oldTotal = $booking->amountDueBeforeCheckout();

            $nightlyRate = (float) ($booking->nightly_rate ?? $unit->price);
            $extraGuestFee = (float) ($booking->extra_guest_fee ?? $unit->extra_guest_fee);
            $adultCount = (int) ($changes['adult_count'] ?? $booking->adult_count);
            $childCount = (int) ($changes['child_count'] ?? $booking->child_count);
            $maximumCapacity = (int) ($unit->maximum_capacity ?? $unit->capacity);
            if ($adultCount + $childCount > $maximumCapacity) {
                throw ValidationException::withMessages([
                    'adult_count' => 'The total number of guests, including free children, cannot exceed this unit’s maximum capacity.',
                ]);
            }
            if ($childCount > 0 && $unit->child_free_through_age === null) {
                throw ValidationException::withMessages([
                    'child_count' => 'This unit does not have a free-child age policy.',
                ]);
            }

            $pricingUnit = clone $unit;
            $pricingUnit->pricing_mode = $booking->pricing_mode ?? 'Per Unit';
            $pricingUnit->price = $nightlyRate;
            $pricingUnit->extra_guest_fee = $extraGuestFee;
            $pricing = $bookingService->calculate(
                $pricingUnit,
                $checkIn->toDateString(),
                $checkOut->toDateString(),
                $adultCount
            );
            $activeGuests = $this->synchronizeGuestRoster(
                $booking,
                $adultCount,
                $childCount,
                $actor,
                $reason,
                ! $isCheckedIn
            );
            $activePayingGuests = $activeGuests->where('is_free_child', false)->values();
            $payingGuestShares = $bookingService->allocateGuestShares($pricing, $activePayingGuests->count());
            $payingGuestIndex = 0;
            foreach ($activeGuests as $activeGuest) {
                $activeGuest->share_amount = $activeGuest->is_free_child
                    ? '0.00'
                    : $payingGuestShares[$payingGuestIndex++];
                $activeGuest->save();
            }

            $booking->check_in = $checkIn;
            $booking->check_out = $checkOut;
            $booking->adult_count = $adultCount;
            $booking->child_count = $childCount;
            $booking->pricing_mode = $pricing['pricing_mode'];
            $booking->nightly_rate = $pricing['nightly_rate'];
            $booking->room_subtotal = $pricing['room_subtotal'];
            $booking->extra_guest_fee = $pricing['extra_guest_fee'];
            $booking->extra_guest_count = $pricing['extra_guest_count'];
            $booking->extra_guest_total = $pricing['extra_guest_total'];
            $booking->save();

            foreach (BookingDiscount::query()
                ->with('guest')
                ->where('booking_id', $booking->booking_id)
                ->whereIn('booking_guest_id', $activeGuests->modelKeys())
                ->get() as $discount) {
                $eligibleAmount = (float) $discount->guest->share_amount;
                $discountAmount = round($eligibleAmount * ((float) $discount->discount_rate / 100), 2);
                $discount->eligible_amount = $eligibleAmount;
                $discount->discount_amount = $discountAmount;
                $discount->final_amount = round($eligibleAmount - $discountAmount, 2);
                $discount->save();
            }

            $booking->total_price = BookingFinancialContract::storedAccommodationAmountAfterAppliedDiscounts($booking);
            $booking->save();
            $newTotal = $booking->amountDueBeforeCheckout();

            BookingEdit::create([
                'booking_id' => $booking->booking_id,
                'edited_by' => $actor->user_id,
                'old_check_in' => $oldCheckIn,
                'old_check_out' => $oldCheckOut,
                'new_check_in' => $checkIn,
                'new_check_out' => $checkOut,
                'old_total_price' => $oldTotal,
                'new_total_price' => $newTotal,
                'edit_reason' => $reason,
            ]);

            if (in_array($booking->status, [BookingStatus::ACCEPTED, BookingStatus::CONFIRMED, BookingStatus::CHECKED_IN], true)) {
                $this->reserveCalendarDates($unit, $booking);
            }

            $booking->load(['unit.house', 'guest']);
            $this->notifyUser(
                $booking->guest,
                $booking,
                'booking_updated',
                'Booking updated',
                "Booking #{$booking->booking_id} was updated by Owner/Staff. The revised total is ₱".number_format($newTotal, 2).". Reason: {$reason}"
            );

            return $booking;
        });
    }

    private function synchronizeGuestRoster(
        Booking $booking,
        int $payingGuestCount,
        int $freeChildCount,
        User $actor,
        string $reason,
        bool $allowRemovals
    ): Collection {
        $activeGuests = $booking->activeGuests()
            ->orderBy('booking_guest_id')
            ->lockForUpdate()
            ->get();

        $this->synchronizeGuestGroup(
            $booking,
            $activeGuests->where('is_free_child', false)->values(),
            $payingGuestCount,
            false,
            $actor,
            $reason,
            $allowRemovals
        );
        $this->synchronizeGuestGroup(
            $booking,
            $activeGuests->where('is_free_child', true)->values(),
            $freeChildCount,
            true,
            $actor,
            $reason,
            $allowRemovals
        );

        return $booking->activeGuests()
            ->orderBy('booking_guest_id')
            ->lockForUpdate()
            ->get();
    }

    private function synchronizeGuestGroup(
        Booking $booking,
        Collection $activeGuests,
        int $targetCount,
        bool $isFreeChild,
        User $actor,
        string $reason,
        bool $allowRemovals
    ): void {
        $difference = $targetCount - $activeGuests->count();
        if ($difference === 0) {
            return;
        }

        if ($difference < 0) {
            if (! $allowRemovals) {
                throw ValidationException::withMessages([
                    'adult_count' => 'Guest records cannot be reduced after check-in.',
                ]);
            }

            $guestsToRemove = $activeGuests
                ->filter(fn (BookingGuest $guest): bool => $guest->attendance_status !== BookingGuest::ATTENDANCE_ARRIVED)
                ->sortByDesc('booking_guest_id')
                ->take(abs($difference));

            if ($guestsToRemove->count() !== abs($difference)) {
                throw ValidationException::withMessages([
                    'adult_count' => 'Arrived guests cannot be removed from the reservation.',
                ]);
            }

            foreach ($guestsToRemove as $guest) {
                $removedAt = now();
                $beforeState = [
                    'roster_status' => $guest->roster_status,
                    'removed_at' => $guest->removed_at?->toDateTimeString(),
                    'removed_by' => $guest->removed_by,
                    'removal_reason' => $guest->removal_reason,
                ];
                $guest->roster_status = BookingGuest::ROSTER_REMOVED;
                $guest->removed_at = $removedAt;
                $guest->removed_by = $actor->user_id;
                $guest->removal_reason = $reason;
                $guest->save();

                BookingGuestEvent::create([
                    'booking_id' => $booking->booking_id,
                    'booking_guest_id' => $guest->booking_guest_id,
                    'event_type' => BookingGuestEvent::TYPE_ROSTER_REMOVAL,
                    'performed_by' => $actor->user_id,
                    'reason' => $reason,
                    'event_at' => $removedAt,
                    'before_state' => $beforeState,
                    'after_state' => [
                        'roster_status' => BookingGuest::ROSTER_REMOVED,
                        'removed_at' => $removedAt->toDateTimeString(),
                        'removed_by' => $actor->user_id,
                        'removal_reason' => $reason,
                    ],
                ]);
            }

            return;
        }

        $guestType = $isFreeChild ? 'child' : 'adult';
        $existingGuestCount = $booking->guests()->where('guest_type', $guestType)->count();
        $hasPrimaryGuest = $booking->guests()->where('is_primary_guest', true)->exists();
        for ($index = 1; $index <= $difference; $index++) {
            $booking->guests()->create([
                'full_name' => ucfirst($guestType).' '.($existingGuestCount + $index),
                'guest_type' => $guestType,
                'age' => null,
                'is_free_child' => $isFreeChild,
                'share_amount' => '0.00',
                'is_primary_guest' => ! $hasPrimaryGuest && ! $isFreeChild && $index === 1,
            ]);
        }
    }

    public function reviewDiscount(User $actor, int $discountId, string $status, ?string $verificationNote = null): BookingDiscount
    {
        $verificationNote = trim((string) $verificationNote);
        if ($status !== 'Verified' && $verificationNote === '') {
            throw ValidationException::withMessages([
                'verification_note' => 'Please explain why this ID cannot be accepted.',
            ]);
        }

        $discount = BookingDiscount::query()->with(['booking.unit.house', 'booking.guest', 'guest'])->findOrFail($discountId);
        $booking = $discount->booking;
        $this->authorizeHouse($actor, $booking->unit->house);

        return DB::transaction(function () use ($actor, $discount, $booking, $status, $verificationNote): BookingDiscount {
            $lockedBooking = Booking::query()->whereKey($booking->booking_id)->lockForUpdate()->firstOrFail();
            $discount = BookingDiscount::query()->whereKey($discount->discount_id)->lockForUpdate()->firstOrFail();

            if (! in_array($lockedBooking->status, [BookingStatus::CONFIRMED, BookingStatus::CHECKED_IN], true)
                || $discount->verification_status !== BookingStatus::PENDING
                || ! in_array($status, ['Verified', 'Invalid', 'Rejected'], true)) {
                throw ValidationException::withMessages([
                    'discount' => 'This discount request is no longer awaiting verification.',
                ]);
            }

            $discountGuest = BookingGuest::query()
                ->whereKey($discount->booking_guest_id)
                ->lockForUpdate()
                ->firstOrFail();
            if ($discountGuest->roster_status !== BookingGuest::ROSTER_ACTIVE) {
                throw ValidationException::withMessages([
                    'discount' => 'A discount for a guest removed from the reservation cannot be reviewed.',
                ]);
            }

            $discount->verification_status = $status;
            $discount->verification_note = $status === 'Verified' || $verificationNote === ''
                ? null
                : $verificationNote;
            $discount->verified_by = $actor->user_id;
            $discount->verified_at = now();
            $discount->save();

            if ($status === 'Verified') {
                $lockedBooking->total_price = max(
                    0,
                    round((float) $lockedBooking->total_price - (float) $discount->discount_amount, 2)
                );
                $lockedBooking->save();

            }

            $lockedBooking->load(['guest', 'unit.house']);
            $decision = $status === 'Verified' ? 'verified' : 'not verified';
            $this->notifyUser(
                $lockedBooking->guest,
                $lockedBooking,
                'discount_'.$status,
                'Discount ID '.$decision,
                "The {$discount->discount_type} ID for {$discount->guest->full_name} was {$decision} at checkout."
                    .($verificationNote !== '' ? " Reason: {$verificationNote}" : '')
            );

            return $discount;
        });
    }

    /**
     * @param  array<int, array{unit_addon_id: int, quantity: int}>  $addons
     */
    public function addBookingAddons(User $actor, int $bookingId, array $addons): void
    {
        $booking = Booking::query()->with('unit.house')->findOrFail($bookingId);
        $this->authorizeHouse($actor, $booking->unit->house);

        DB::transaction(function () use ($actor, $booking, $addons): void {
            $lockedBooking = Booking::query()
                ->whereKey($booking->booking_id)
                ->lockForUpdate()
                ->with('unit.house')
                ->firstOrFail();

            $this->authorizeHouse($actor, $lockedBooking->unit->house);

            if ($lockedBooking->status !== BookingStatus::CHECKED_IN) {
                throw ValidationException::withMessages([
                    'addons' => 'Amenities can only be added while the guest is checked in.',
                ]);
            }

            $addonIds = collect($addons)
                ->pluck('unit_addon_id')
                ->map(fn ($addonId): int => (int) $addonId)
                ->unique()
                ->values();
            $unitAddons = UnitAddon::query()
                ->where('unit_id', $lockedBooking->unit_id)
                ->whereIn('unit_addon_id', $addonIds)
                ->get()
                ->keyBy('unit_addon_id');

            if ($unitAddons->count() !== $addonIds->count()) {
                throw ValidationException::withMessages([
                    'addons' => 'One or more selected amenities are no longer available for this unit.',
                ]);
            }

            foreach ($addons as $addon) {
                $unitAddon = $unitAddons->get((int) $addon['unit_addon_id']);
                $bookingAddon = BookingAddon::query()
                    ->where('booking_id', $lockedBooking->booking_id)
                    ->where('name', $unitAddon->name)
                    ->where('price', $unitAddon->price)
                    ->lockForUpdate()
                    ->first();

                if ($bookingAddon) {
                    $bookingAddon->increment('quantity', (int) $addon['quantity']);
                } else {
                    BookingAddon::create([
                        'booking_id' => $lockedBooking->booking_id,
                        'name' => $unitAddon->name,
                        'price' => $unitAddon->price,
                        'quantity' => (int) $addon['quantity'],
                    ]);
                }
            }
        });
    }

    public function adjustBookingAddonQuantity(User $actor, int $bookingId, int $addonId, int $change): void
    {
        $booking = Booking::query()->with('unit.house')->findOrFail($bookingId);
        $this->authorizeHouse($actor, $booking->unit->house);

        DB::transaction(function () use ($actor, $booking, $addonId, $change): void {
            $lockedBooking = Booking::query()
                ->whereKey($booking->booking_id)
                ->lockForUpdate()
                ->with('unit.house')
                ->firstOrFail();
            $this->authorizeHouse($actor, $lockedBooking->unit->house);

            if ($lockedBooking->status !== BookingStatus::CHECKED_IN) {
                throw ValidationException::withMessages([
                    'quantity' => 'Amenity quantities can only be changed during an active stay.',
                ]);
            }

            $addon = BookingAddon::query()
                ->where('booking_id', $lockedBooking->booking_id)
                ->whereKey($addonId)
                ->lockForUpdate()
                ->firstOrFail();
            $quantity = $addon->quantity + $change;

            if (! in_array($change, [-1, 1], true) || $quantity < 0 || $quantity > 100) {
                throw ValidationException::withMessages([
                    'quantity' => 'Amenity quantity must remain between 0 and 100.',
                ]);
            }

            $addon->quantity = $quantity;
            $addon->save();
        });
    }

    public function refundCheckedInGuest(User $actor, int $bookingId, float $amount, string $reason): Refund
    {
        $booking = Booking::query()
            ->with(['unit.house', 'guest'])
            ->findOrFail($bookingId);
        $this->authorizeHouse($actor, $booking->unit->house);

        return DB::transaction(function () use ($actor, $booking, $amount, $reason): Refund {
            $lockedBooking = Booking::query()
                ->whereKey($booking->booking_id)
                ->lockForUpdate()
                ->with('guest')
                ->firstOrFail();

            if ($lockedBooking->status !== BookingStatus::CHECKED_IN) {
                throw ValidationException::withMessages([
                    'refund' => 'A refund can only be recorded while the guest is checked in.',
                ]);
            }

            $verifiedPayments = $lockedBooking->payments()
                ->where('payment_status', PaymentStatus::VERIFIED)
                ->orderByDesc('payment_date')
                ->orderByDesc('payment_id')
                ->get();
            $pendingRefundTotal = (float) $lockedBooking->refunds()
                ->where('refund_status', 'Pending')
                ->sum('amount');
            $refundableCredit = max(0, round($lockedBooking->refundableOverpayment() - $pendingRefundTotal, 2));
            if ($verifiedPayments->isEmpty() || $refundableCredit <= 0) {
                throw ValidationException::withMessages([
                    'amount' => 'There is no refundable overpayment from verified payments.',
                ]);
            }

            $amountCents = (int) round($amount * 100);
            if ($amountCents <= 0 || $amountCents > (int) round($refundableCredit * 100)) {
                throw ValidationException::withMessages([
                    'amount' => 'The refund cannot exceed the remaining refundable overpayment.',
                ]);
            }
            $refundsByPayment = $lockedBooking->refunds()
                ->whereIn('payment_id', $verifiedPayments->modelKeys())
                ->whereIn('refund_status', ['Pending', 'Refunded'])
                ->selectRaw('payment_id, SUM(amount) as total')
                ->groupBy('payment_id')
                ->pluck('total', 'payment_id');
            $payment = $verifiedPayments->first(function (Payment $verifiedPayment) use ($refundsByPayment, $amountCents): bool {
                $paymentAvailableCents = (int) round(
                    ((float) $verifiedPayment->amount - (float) $refundsByPayment->get($verifiedPayment->payment_id, 0)) * 100
                );

                return $paymentAvailableCents >= $amountCents;
            });
            if (! $payment) {
                throw ValidationException::withMessages([
                    'amount' => 'The refund amount exceeds the refundable amount on each verified payment.',
                ]);
            }

            $refund = Refund::create([
                'booking_id' => $lockedBooking->booking_id,
                'payment_id' => $payment->payment_id,
                'processed_by' => $actor->user_id,
                'amount' => $amountCents / 100,
                'reason' => $reason,
                'refund_status' => 'Refunded',
                'processed_at' => now(),
                'created_at' => now(),
            ]);

            $this->notifyUser(
                $lockedBooking->guest,
                $lockedBooking,
                'refund_issued',
                'Refund issued',
                'A refund of ₱'.number_format($amountCents / 100, 2)." for booking #{$lockedBooking->booking_id} has been issued. Reason: {$reason}"
            );

            return $refund;
        });
    }

    public function addCheckoutCharge(User $actor, int $bookingId, string $description, int $quantity, float $unitPrice): AdditionalCharge
    {
        $booking = Booking::query()->with('unit.house')->findOrFail($bookingId);
        $this->authorizeHouse($actor, $booking->unit->house);

        return DB::transaction(function () use ($actor, $booking, $description, $quantity, $unitPrice): AdditionalCharge {
            $lockedBooking = Booking::query()->whereKey($booking->booking_id)->lockForUpdate()->firstOrFail();

            if (! in_array($lockedBooking->status, [BookingStatus::CONFIRMED, BookingStatus::CHECKED_IN], true)) {
                throw ValidationException::withMessages(['booking' => 'Charges can only be added before checkout is completed.']);
            }

            return AdditionalCharge::create([
                'booking_id' => $lockedBooking->booking_id,
                'created_by' => $actor->user_id,
                'description' => $description,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_amount' => round($quantity * $unitPrice, 2),
            ]);
        });
    }

    /**
     * @param  array<int, array{unit_damage_item_id: int, quantity: int}>  $items
     */
    public function addBookingDamages(User $actor, int $bookingId, array $items): void
    {
        $booking = Booking::query()->with('unit.house')->findOrFail($bookingId);
        $this->authorizeHouse($actor, $booking->unit->house);

        DB::transaction(function () use ($actor, $booking, $items): void {
            $lockedBooking = Booking::query()->whereKey($booking->booking_id)->lockForUpdate()->firstOrFail();

            if (! in_array($lockedBooking->status, [BookingStatus::CONFIRMED, BookingStatus::CHECKED_IN], true)) {
                throw ValidationException::withMessages(['booking' => 'Damage or missing items can only be added before checkout is completed.']);
            }

            foreach ($items as $item) {
                $unitDamageItem = UnitDamageItem::query()
                    ->where('unit_id', $lockedBooking->unit_id)
                    ->whereKey($item['unit_damage_item_id'])
                    ->lockForUpdate()
                    ->first();

                if (! $unitDamageItem) {
                    throw ValidationException::withMessages([
                        'items' => 'One or more selected items are not listed for this unit.',
                    ]);
                }

                $damageReport = DamageReport::query()
                    ->where('booking_id', $lockedBooking->booking_id)
                    ->where('unit_damage_item_id', $unitDamageItem->unit_damage_item_id)
                    ->where('unit_price', $unitDamageItem->price)
                    ->where('approval_status', DamageReport::CHARGE_INCLUDED)
                    ->whereNull('resolved_at')
                    ->lockForUpdate()
                    ->first();

                if ($damageReport) {
                    $damageReport->quantity += (int) $item['quantity'];
                    $damageReport->amount = round((float) $damageReport->unit_price * $damageReport->quantity, 2);
                    $damageReport->save();
                } else {
                    DamageReport::create([
                        'booking_id' => $lockedBooking->booking_id,
                        'unit_id' => $lockedBooking->unit_id,
                        'unit_damage_item_id' => $unitDamageItem->unit_damage_item_id,
                        'reported_by' => $actor->user_id,
                        'description' => $unitDamageItem->name,
                        'amount' => round((float) $unitDamageItem->price * (int) $item['quantity'], 2),
                        'quantity' => $item['quantity'],
                        'unit_price' => $unitDamageItem->price,
                        'status' => 'Reported',
                        'approval_status' => DamageReport::CHARGE_INCLUDED,
                        'reported_at' => now(),
                    ]);
                }
            }
        });
    }

    public function addCustomBookingDamage(
        User $actor,
        int $bookingId,
        string $description,
        int $quantity,
        float $unitPrice
    ): void {
        $booking = Booking::query()->with('unit.house')->findOrFail($bookingId);
        $this->authorizeHouse($actor, $booking->unit->house);
        $description = trim($description);

        DB::transaction(function () use ($actor, $booking, $description, $quantity, $unitPrice): void {
            $lockedBooking = Booking::query()->whereKey($booking->booking_id)->lockForUpdate()->firstOrFail();

            if (! in_array($lockedBooking->status, [BookingStatus::CONFIRMED, BookingStatus::CHECKED_IN], true)) {
                throw ValidationException::withMessages(['booking' => 'Damage or missing items can only be added before checkout is completed.']);
            }

            $damageReport = DamageReport::query()
                ->where('booking_id', $lockedBooking->booking_id)
                ->whereNull('unit_damage_item_id')
                ->where('description', $description)
                ->where('unit_price', $unitPrice)
                ->where('approval_status', DamageReport::CHARGE_INCLUDED)
                ->whereNull('resolved_at')
                ->lockForUpdate()
                ->first();

            if ($damageReport) {
                $damageReport->quantity += $quantity;
                $damageReport->amount = round((float) $damageReport->unit_price * $damageReport->quantity, 2);
                $damageReport->save();

                return;
            }

            DamageReport::create([
                'booking_id' => $lockedBooking->booking_id,
                'unit_id' => $lockedBooking->unit_id,
                'reported_by' => $actor->user_id,
                'description' => $description,
                'amount' => round($unitPrice * $quantity, 2),
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'status' => 'Reported',
                'approval_status' => DamageReport::CHARGE_INCLUDED,
                'reported_at' => now(),
            ]);
        });
    }

    public function adjustDamageReportQuantity(User $actor, int $bookingId, int $damageId, int $change): void
    {
        $booking = Booking::query()->with('unit.house')->findOrFail($bookingId);
        $this->authorizeHouse($actor, $booking->unit->house);

        DB::transaction(function () use ($actor, $booking, $damageId, $change): void {
            $lockedBooking = Booking::query()
                ->whereKey($booking->booking_id)
                ->lockForUpdate()
                ->with('unit.house')
                ->firstOrFail();
            $this->authorizeHouse($actor, $lockedBooking->unit->house);

            if (! in_array($lockedBooking->status, [BookingStatus::CONFIRMED, BookingStatus::CHECKED_IN], true)) {
                throw ValidationException::withMessages([
                    'quantity' => 'Damage quantities can only be changed before checkout is completed.',
                ]);
            }

            $damageReport = DamageReport::query()
                ->where('booking_id', $lockedBooking->booking_id)
                ->whereKey($damageId)
                ->lockForUpdate()
                ->firstOrFail();
            $quantity = $damageReport->quantity + $change;

            if (! $damageReport->isIncludedInFinalAmount()
                || ! in_array($change, [-1, 1], true)
                || $quantity < 0
                || $quantity > 100) {
                throw ValidationException::withMessages([
                    'quantity' => 'This damage quantity cannot be changed.',
                ]);
            }

            $damageReport->quantity = $quantity;
            $damageReport->amount = round((float) $damageReport->unit_price * $quantity, 2);
            $damageReport->save();
        });
    }

    public function removeBookingDamage(User $actor, int $bookingId, int $damageId): void
    {
        $booking = Booking::query()->with('unit.house')->findOrFail($bookingId);
        $this->authorizeHouse($actor, $booking->unit->house);

        DB::transaction(function () use ($booking, $damageId): void {
            $lockedBooking = Booking::query()->whereKey($booking->booking_id)->lockForUpdate()->firstOrFail();

            if (! in_array($lockedBooking->status, [BookingStatus::CONFIRMED, BookingStatus::CHECKED_IN], true)) {
                throw ValidationException::withMessages(['booking' => 'Damage or missing items can only be removed before checkout is completed.']);
            }

            BookingDamage::query()
                ->where('booking_id', $lockedBooking->booking_id)
                ->whereKey($damageId)
                ->lockForUpdate()
                ->firstOrFail()
                ->delete();
        });
    }

    public function removeCheckoutCharge(User $actor, int $bookingId, int $chargeId): void
    {
        $charge = AdditionalCharge::query()
            ->where('booking_id', $bookingId)
            ->with('booking.unit.house')
            ->findOrFail($chargeId);
        $booking = $charge->booking;
        $this->authorizeHouse($actor, $booking->unit->house);

        DB::transaction(function () use ($booking, $charge): void {
            $lockedBooking = Booking::query()->whereKey($booking->booking_id)->lockForUpdate()->firstOrFail();
            $lockedCharge = AdditionalCharge::query()->whereKey($charge->additional_charge_id)->lockForUpdate()->firstOrFail();

            if (! in_array($lockedBooking->status, [BookingStatus::CONFIRMED, BookingStatus::CHECKED_IN], true)) {
                throw ValidationException::withMessages(['booking' => 'Checkout charges can only be removed before checkout is completed.']);
            }

            $lockedCharge->delete();
        });
    }

    public function adjustCheckoutChargeQuantity(User $actor, int $bookingId, int $chargeId, int $change): void
    {
        $charge = AdditionalCharge::query()
            ->where('booking_id', $bookingId)
            ->with('booking.unit.house')
            ->findOrFail($chargeId);
        $booking = $charge->booking;
        $this->authorizeHouse($actor, $booking->unit->house);

        DB::transaction(function () use ($actor, $booking, $charge, $change): void {
            $lockedBooking = Booking::query()
                ->whereKey($booking->booking_id)
                ->lockForUpdate()
                ->with('unit.house')
                ->firstOrFail();
            $this->authorizeHouse($actor, $lockedBooking->unit->house);

            if (! in_array($lockedBooking->status, [BookingStatus::CONFIRMED, BookingStatus::CHECKED_IN], true)) {
                throw ValidationException::withMessages([
                    'quantity' => 'Charge quantities can only be changed before checkout is completed.',
                ]);
            }

            $lockedCharge = AdditionalCharge::query()
                ->where('booking_id', $lockedBooking->booking_id)
                ->whereKey($charge->additional_charge_id)
                ->lockForUpdate()
                ->firstOrFail();
            $quantity = $lockedCharge->quantity + $change;

            if (! in_array($change, [-1, 1], true) || $quantity < 0 || $quantity > 100) {
                throw ValidationException::withMessages([
                    'quantity' => 'Charge quantity must remain between 0 and 100.',
                ]);
            }

            $lockedCharge->quantity = $quantity;
            $lockedCharge->total_amount = round((float) $lockedCharge->unit_price * $quantity, 2);
            $lockedCharge->save();
        });
    }

    public function recordCheckoutPayment(
        User $actor,
        int $bookingId,
        string $method,
        float $amount,
        ?string $reference,
        ?string $receiptPath,
        ?string $notes
    ): Payment {
        $booking = Booking::query()->with(['unit.house', 'guest'])->findOrFail($bookingId);
        $this->authorizeHouse($actor, $booking->unit->house);

        return DB::transaction(function () use ($booking, $method, $amount, $reference, $receiptPath, $notes): Payment {
            $lockedBooking = Booking::query()->whereKey($booking->booking_id)->lockForUpdate()->firstOrFail();

            if (! in_array($lockedBooking->status, [BookingStatus::CONFIRMED, BookingStatus::CHECKED_IN], true)) {
                throw ValidationException::withMessages(['booking' => 'Payments can only be recorded before checkout is completed.']);
            }

            $balanceDue = $lockedBooking->outstandingBalance();

            if ($amount > $balanceDue) {
                throw ValidationException::withMessages(['amount' => 'The recorded payment cannot exceed the remaining checkout balance.']);
            }

            $referenceNumber = $this->ensureReferenceNumberAvailable($reference);
            $payment = Payment::create([
                'booking_id' => $lockedBooking->booking_id,
                'payment_method' => $method,
                'amount' => $amount,
                'payment_status' => PaymentStatus::VERIFIED,
                'payment_date' => now(),
                'reference_number' => $referenceNumber,
                'payment_receipt' => $receiptPath,
                'notes' => $notes,
            ]);

            $lockedBooking->load(['guest', 'unit.house']);
            $this->notifyUser(
                $lockedBooking->guest,
                $lockedBooking,
                'checkout_payment_recorded',
                'Checkout payment recorded',
                "A {$method} payment of ₱".number_format($amount, 2)." was recorded for booking #{$lockedBooking->booking_id}."
            );

            return $payment;
        });
    }

    public function completeCheckout(User $actor, int $bookingId, ?string $earlyCheckoutReason = null): BookingReceipt
    {
        $booking = Booking::query()->with(['unit.house', 'guest'])->findOrFail($bookingId);
        $this->authorizeHouse($actor, $booking->unit->house);

        $receipt = DB::transaction(function () use ($booking, $earlyCheckoutReason): BookingReceipt {
            $lockedBooking = Booking::query()->whereKey($booking->booking_id)->lockForUpdate()->firstOrFail();

            if (! in_array($lockedBooking->status, [BookingStatus::CONFIRMED, BookingStatus::CHECKED_IN], true)) {
                throw ValidationException::withMessages(['booking' => 'Only active bookings can be checked out.']);
            }

            if ($lockedBooking->payments()->where('payment_status', PaymentStatus::UNDER_REVIEW)->exists()) {
                throw ValidationException::withMessages(['booking' => 'Finish reviewing submitted payments before completing checkout.']);
            }

            if ($lockedBooking->outstandingBalance() > 0) {
                throw ValidationException::withMessages([
                    'booking' => 'Checkout cannot be completed while an unpaid booking or checkout balance remains.',
                ]);
            }

            $isEarlyCheckout = $lockedBooking->status === BookingStatus::CHECKED_IN
                && today()->lt($lockedBooking->check_out->copy()->startOfDay());
            $reason = trim($earlyCheckoutReason ?? '');
            if ($isEarlyCheckout && $reason === '') {
                throw ValidationException::withMessages([
                    'early_checkout_reason' => 'Please provide a reason for checking out before the scheduled departure date.',
                ]);
            }

            $pendingRefundTotal = $this->recordApprovedDiscountRefunds($lockedBooking);
            $lockedBooking->status = BookingStatus::COMPLETED;
            $lockedBooking->checked_out_at = now();
            $lockedBooking->early_checkout_reason = $isEarlyCheckout ? $reason : null;
            $lockedBooking->save();
            $receipt = BookingReceipt::create([
                'booking_id' => $lockedBooking->booking_id,
                'receipt_number' => 'MCTBS-RCP-'.str_pad((string) $lockedBooking->booking_id, 8, '0', STR_PAD_LEFT),
                'issued_at' => now(),
            ]);

            $lockedBooking->load(['unit.house', 'guest']);
            $this->notifyUser(
                $lockedBooking->guest,
                $lockedBooking,
                'booking_completed',
                'Booking completed',
                "Your stay at {$lockedBooking->unit->unit_type} is complete. Your Booking Breakdown is ready."
                    .($pendingRefundTotal > 0
                        ? ' A discount refund of ₱'.number_format($pendingRefundTotal, 2).' is pending.'
                        : '')
            );

            return $receipt;
        });

        return $receipt;
    }

    public function sendFeedbackRequest(User $actor, int $bookingId): string
    {
        $booking = Booking::query()
            ->with(['unit.house', 'guest'])
            ->findOrFail($bookingId);
        $this->authorizeHouse($actor, $booking->unit->house);

        $token = DB::transaction(function () use ($booking): string {
            $lockedBooking = Booking::query()
                ->whereKey($booking->booking_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedBooking->status !== BookingStatus::CHECKED_IN) {
                throw ValidationException::withMessages([
                    'feedback' => 'Feedback links can only be sent for checked-in guests.',
                ]);
            }

            if ($lockedBooking->hasReviewOrLegacyFeedback()) {
                throw ValidationException::withMessages([
                    'feedback' => 'This booking already has a review.',
                ]);
            }

            do {
                $token = Str::random(64);
                $tokenHash = hash('sha256', $token);
            } while (Booking::query()->where('feedback_token', $tokenHash)->exists());

            $lockedBooking->feedback_token = $tokenHash;
            $lockedBooking->feedback_token_expires_at = now()->addDays(7);
            $lockedBooking->save();

            return $token;
        });

        $booking->refresh()->load(['unit.house', 'guest']);
        $feedbackUrl = route('guest.feedback.show', [
            'booking' => $booking->booking_id,
            'token' => $token,
        ]);

        if ($booking->displayGuestEmail()) {
            Mail::to($booking->displayGuestEmail())->send(new BookingFeedbackRequested($booking, $feedbackUrl));
        }

        return $feedbackUrl;
    }

    public function processCheckoutRefund(
        User $actor,
        int $bookingId,
        int $refundId,
        ?string $reference
    ): Refund {
        $booking = Booking::query()
            ->with(['unit.house', 'guest'])
            ->findOrFail($bookingId);
        $this->authorizeHouse($actor, $booking->unit->house);

        return DB::transaction(function () use ($actor, $booking, $refundId, $reference): Refund {
            $lockedBooking = Booking::query()
                ->whereKey($booking->booking_id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! BookingStatus::isTerminal($lockedBooking->status)) {
                throw ValidationException::withMessages([
                    'refund' => 'Refunds can only be issued after checkout is complete.',
                ]);
            }

            $refund = Refund::query()
                ->where('booking_id', $lockedBooking->booking_id)
                ->whereKey($refundId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($refund->refund_status !== 'Pending') {
                throw ValidationException::withMessages([
                    'refund' => 'This refund has already been processed.',
                ]);
            }
            $payment = Payment::query()
                ->whereKey($refund->payment_id)
                ->lockForUpdate()
                ->firstOrFail();
            $alreadyRefundedFromPayment = (float) $lockedBooking->refunds()
                ->where('payment_id', $payment->payment_id)
                ->where('refund_status', 'Refunded')
                ->sum('amount');
            $otherPendingRefundsFromPayment = (float) $lockedBooking->refunds()
                ->where('payment_id', $payment->payment_id)
                ->where('refund_status', 'Pending')
                ->where('refund_id', '!=', $refund->refund_id)
                ->sum('amount');
            if ($payment->booking_id !== $lockedBooking->booking_id
                || $payment->payment_status !== PaymentStatus::VERIFIED
                || (float) $refund->amount > $lockedBooking->refundableOverpayment()
                || (float) $refund->amount + $alreadyRefundedFromPayment + $otherPendingRefundsFromPayment > (float) $payment->amount) {
                throw ValidationException::withMessages([
                    'refund' => 'This refund no longer has sufficient verified refundable credit.',
                ]);
            }

            $refund->refund_status = 'Refunded';
            $refund->processed_by = $actor->user_id;
            $refund->refund_reference = $reference;
            $refund->processed_at = now();
            $refund->save();

            if (! $lockedBooking->refunds()->where('refund_status', 'Pending')->exists()) {
                $lockedBooking->discounts()
                    ->where('verification_status', 'Verified')
                    ->where('refund_status', 'Pending')
                    ->update(['refund_status' => 'Refunded']);
            }

            $this->notifyUser(
                $booking->guest,
                $lockedBooking,
                'refund_issued',
                'Refund issued',
                'A refund of ₱'.number_format((float) $refund->amount, 2)." for booking #{$lockedBooking->booking_id} has been issued."
                    .($reference ? " Reference: {$reference}." : '')
            );

            return $refund;
        });
    }

    private function recordApprovedDiscountRefunds(Booking $booking): float
    {
        $verifiedPayments = $booking->payments()
            ->where('payment_status', PaymentStatus::VERIFIED)
            ->orderByDesc('payment_date')
            ->orderByDesc('payment_id')
            ->get();

        if ($verifiedPayments->isEmpty()) {
            return 0;
        }

        $refundAvailable = $booking->refundableOverpayment();
        $pendingRefundTotal = (float) $booking->refunds()
            ->where('refund_status', 'Pending')
            ->sum('amount');
        $refundAvailable = max(0, round($refundAvailable - $pendingRefundTotal, 2));
        $refundsByPayment = $booking->refunds()
            ->whereIn('payment_id', $verifiedPayments->modelKeys())
            ->whereIn('refund_status', ['Pending', 'Refunded'])
            ->selectRaw('payment_id, SUM(amount) as total')
            ->groupBy('payment_id')
            ->pluck('total', 'payment_id');
        $paymentCapacities = $verifiedPayments->mapWithKeys(function (Payment $payment) use ($refundsByPayment): array {
            $alreadyRefunded = (float) $refundsByPayment->get($payment->payment_id, 0);

            return [
                $payment->payment_id => max(0, round((float) $payment->amount - $alreadyRefunded, 2)),
            ];
        });
        $refundTotal = 0;

        foreach ($booking->discounts()
            ->where('verification_status', 'Verified')
            ->whereHas('guest', fn ($query) => $query->where('roster_status', BookingGuest::ROSTER_ACTIVE))
            ->orderBy('discount_id')
            ->get() as $discount) {
            $refundAmount = min(
                (float) $discount->discount_amount,
                $refundAvailable,
                (float) $paymentCapacities->sum()
            );
            $discount->refund_amount = $refundAmount;
            $discount->refund_status = $refundAmount > 0 ? 'Pending' : 'None';
            $discount->save();

            if ($refundAmount <= 0) {
                continue;
            }

            $amountToAllocate = $refundAmount;
            foreach ($verifiedPayments as $payment) {
                $paymentCapacity = (float) $paymentCapacities->get($payment->payment_id, 0);
                $paymentRefund = min($amountToAllocate, $paymentCapacity);
                if ($paymentRefund <= 0) {
                    continue;
                }

                Refund::create([
                    'booking_id' => $booking->booking_id,
                    'payment_id' => $payment->payment_id,
                    'amount' => $paymentRefund,
                    'reason' => $discount->discount_type.' discount approved after payment.',
                    'refund_status' => 'Pending',
                    'created_at' => now(),
                ]);
                $paymentCapacities->put($payment->payment_id, round($paymentCapacity - $paymentRefund, 2));
                $amountToAllocate = round($amountToAllocate - $paymentRefund, 2);
                if ($amountToAllocate <= 0) {
                    break;
                }
            }

            $refundAvailable -= $refundAmount;
            $refundTotal += $refundAmount;
        }

        return $refundTotal;
    }

    public function archiveBooking(User $actor, int $bookingId, ?string $note): TransactionArchive
    {
        $booking = Booking::query()->with('unit.house')->findOrFail($bookingId);
        $this->authorizeHouse($actor, $booking->unit->house);

        return DB::transaction(function () use ($actor, $booking, $note): TransactionArchive {
            $lockedBooking = Booking::query()->whereKey($booking->booking_id)->lockForUpdate()->firstOrFail();

            if (! in_array($lockedBooking->status, [
                ...BookingStatus::completedStatuses(),
                BookingStatus::CANCELLED,
                BookingStatus::REJECTED,
            ], true)) {
                throw ValidationException::withMessages(['booking' => 'Only completed, cancelled, or rejected bookings can be archived.']);
            }

            if ($lockedBooking->archive()->exists()) {
                throw ValidationException::withMessages(['booking' => 'This booking has already been archived.']);
            }

            return TransactionArchive::create([
                'booking_id' => $lockedBooking->booking_id,
                'archived_by' => $actor->user_id,
                'archive_note' => $note,
                'archived_at' => now(),
                'scheduled_deletion_at' => null,
            ]);
        });
    }

    /**
     * @param  list<int>  $bookingIds
     */
    public function archiveBookings(User $actor, array $bookingIds): int
    {
        if ($bookingIds === []) {
            throw ValidationException::withMessages(['booking_ids' => 'Select at least one booking to archive.']);
        }

        return DB::transaction(function () use ($actor, $bookingIds): int {
            foreach ($bookingIds as $bookingId) {
                $this->archiveBooking($actor, $bookingId, null);
            }

            return count($bookingIds);
        });
    }

    public function canManageHouse(User $user, TransientHouse $house): bool
    {
        if ($user->role?->name === 'Owner') {
            return $house->owner_id === $user->user_id;
        }

        if ($user->role?->name !== 'Staff') {
            return false;
        }

        $staffRoleId = Role::where('name', 'Staff')->value('role_id');

        return Invitation::query()
            ->where('email', $user->email)
            ->where('role_id', $staffRoleId)
            ->where('house_id', $house->house_id)
            ->whereNotNull('used_at')
            ->exists();
    }

    private function lockCheckInBooking(User $actor, int $bookingId): Booking
    {
        $booking = Booking::query()
            ->with(['unit.house'])
            ->whereKey($bookingId)
            ->lockForUpdate()
            ->firstOrFail();
        $this->authorizeHouse($actor, $booking->unit->house);

        if (! in_array($booking->status, [BookingStatus::CONFIRMED, BookingStatus::CHECKED_IN], true)) {
            throw ValidationException::withMessages([
                'attendance' => 'Guest attendance can only be managed for confirmed or checked-in bookings.',
            ]);
        }

        return $booking;
    }

    private function lockBookingGuest(Booking $booking, int $bookingGuestId): BookingGuest
    {
        return $booking->guests()
            ->whereKey($bookingGuestId)
            ->lockForUpdate()
            ->firstOrFail();
    }

    /**
     * @return Collection<int, Booking>
     */
    public function overlappingPendingBookings(Booking $booking): Collection
    {
        return Booking::query()
            ->where('unit_id', $booking->unit_id)
            ->where('status', BookingStatus::PENDING)
            ->whereDate('check_in', '<', $booking->check_out->toDateString())
            ->whereDate('check_out', '>', $booking->check_in->toDateString())
            ->orderBy('created_at')
            ->orderBy('booking_id')
            ->get();
    }

    private function hasActiveOverlap(Booking $booking): bool
    {
        return Booking::query()
            ->where('unit_id', $booking->unit_id)
            ->where('booking_id', '!=', $booking->booking_id)
            ->whereIn('status', [BookingStatus::ACCEPTED, BookingStatus::CONFIRMED, BookingStatus::CHECKED_IN])
            ->whereDate('check_in', '<', $booking->check_out->toDateString())
            ->whereDate('check_out', '>', $booking->check_in->toDateString())
            ->exists();
    }

    private function hasBlockedDates(Unit $unit, Booking $booking): bool
    {
        return $this->hasBlockedCalendarDates(
            $unit,
            $booking->check_in->toDateString(),
            $booking->check_out->toDateString()
        );
    }

    private function hasBlockedCalendarDates(Unit $unit, string $checkIn, string $checkOut): bool
    {
        return $unit->availability()
            ->where(function ($query) use ($checkIn, $checkOut): void {
                $query->where(function ($query) use ($checkIn, $checkOut): void {
                    $query->whereIn('status', ['Booked', 'Unavailable'])
                        ->where('date', '>=', $checkIn)
                        ->where('date', '<', $checkOut);
                })->orWhere(function ($query) use ($checkOut): void {
                    $query->where('status', 'Unavailable')
                        ->whereDate('date', $checkOut);
                });
            })
            ->exists();
    }

    private function reserveCalendarDates(Unit $unit, Booking $booking): void
    {
        $dates = CarbonPeriod::create(
            $booking->check_in->copy()->startOfDay(),
            '1 day',
            $booking->check_out->copy()->startOfDay()->subDay()
        );

        foreach ($dates as $date) {
            UnitCalendar::updateOrCreate(
                ['unit_id' => $unit->unit_id, 'date' => $date->toDateString()],
                ['status' => 'Booked', 'note' => 'Confirmed booking #'.$booking->booking_id]
            );
        }
    }

    private function releaseCalendarDates(Unit $unit, Booking $booking): void
    {
        $unit->availability()
            ->where('status', 'Booked')
            ->where('note', 'Confirmed booking #'.$booking->booking_id)
            ->delete();
    }

    private function cancelOverlappingRequests(Booking $booking): void
    {
        $competingRequests = Booking::query()
            ->where('unit_id', $booking->unit_id)
            ->where('booking_id', '!=', $booking->booking_id)
            ->where('status', BookingStatus::PENDING)
            ->whereDate('check_in', '<', $booking->check_out->toDateString())
            ->whereDate('check_out', '>', $booking->check_in->toDateString())
            ->with('guest')
            ->get();

        foreach ($competingRequests as $request) {
            $request->status = BookingStatus::CANCELLED;
            $request->cancellation_reason = 'The requested dates were confirmed for an earlier booking.';
            $request->save();
            $this->notifyUser(
                $request->guest,
                $request,
                'booking_dates_unavailable',
                'Dates are no longer available',
                "Your booking was cancelled automatically. Reason: {$request->cancellation_reason} You may request another unit or different dates."
            );
        }
    }

    private function notifyNextWaitingRequest(Booking $booking): void
    {
        $unit = Unit::query()->with('house')->findOrFail($booking->unit_id);

        foreach ($this->overlappingPendingBookings($booking) as $next) {
            if ($next->status !== BookingStatus::PENDING
                || $next->check_in->toDateString() <= today()->toDateString()
                || $unit->status !== 'Available'
                || $this->hasActiveOverlap($next)
                || $this->hasBlockedDates($unit, $next)) {
                continue;
            }

            $next->load('guest');
            $this->notifyUser(
                $next->guest,
                $next,
                'booking_next_in_queue',
                'Your request is next in line',
                'The earlier booking request was released. Your pending request is now the earliest eligible request for these dates.'
            );
            $this->notifyHouse(
                $unit->house,
                $next,
                'booking_next_in_queue',
                'Next booking request ready',
                "Booking #{$next->booking_id} is the earliest pending request for the released dates."
            );

            return;
        }
    }

    private function notifyHouse(TransientHouse $house, Booking $booking, string $type, string $title, string $message): void
    {
        $staffRoleId = Role::where('name', 'Staff')->value('role_id');
        $staffEmails = Invitation::query()
            ->where('house_id', $house->house_id)
            ->where('role_id', $staffRoleId)
            ->whereNotNull('used_at')
            ->pluck('email');

        $recipientIds = collect([$house->owner_id])
            ->merge(User::query()->whereIn('email', $staffEmails)->pluck('user_id'))
            ->unique();

        foreach ($recipientIds as $userId) {
            Notification::create([
                'user_id' => $userId,
                'booking_id' => $booking->booking_id,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'is_read' => false,
                'created_at' => now(),
            ]);
        }
    }

    private function notifyUser(?User $user, Booking $booking, string $type, string $title, string $message): void
    {
        if (! $user) {
            return;
        }

        Notification::create([
            'user_id' => $user->user_id,
            'booking_id' => $booking->booking_id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'is_read' => false,
            'created_at' => now(),
        ]);
    }

    private function authorizeHouse(User $user, TransientHouse $house): void
    {
        abort_unless($this->canManageHouse($user, $house), 404);
    }

    public function isReferenceNumberAvailable(?string $referenceNumber): bool
    {
        $referenceNumber = trim((string) $referenceNumber);
        if ($referenceNumber === '') {
            return true;
        }

        return ! Payment::query()
            ->whereRaw('LOWER(TRIM(reference_number)) = ?', [mb_strtolower($referenceNumber)])
            ->exists();
    }

    private function ensureReferenceNumberAvailable(?string $referenceNumber): ?string
    {
        $referenceNumber = trim((string) $referenceNumber);
        if ($referenceNumber === '') {
            return null;
        }

        if (! $this->isReferenceNumberAvailable($referenceNumber)) {
            throw ValidationException::withMessages([
                'reference_number' => 'Reference number already used.',
            ]);
        }

        return $referenceNumber;
    }

    private function notificationSuffix(string $status): string
    {
        return mb_strtolower(str_replace(' ', '_', $status));
    }
}
