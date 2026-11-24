@php
    $canonicalStatus = \App\Support\BookingStatus::canonical($booking->status);
    $statusBadge = match ($canonicalStatus) {
        \App\Support\BookingStatus::PENDING => 'badge-pending',
        \App\Support\BookingStatus::ACCEPTED, \App\Support\BookingStatus::CONFIRMED => 'badge-accepted',
        \App\Support\BookingStatus::CHECKED_IN => 'badge-accepted',
        \App\Support\BookingStatus::COMPLETED => 'badge-completed',
        \App\Support\BookingStatus::REJECTED => 'badge-rejected',
        default => 'badge-cancelled',
    };
    $paymentBadge = $canonicalStatus === \App\Support\BookingStatus::ACCEPTED
        ? ($hasUnderReviewPayment ? 'badge-pending' : 'badge-unpaid')
        : ($verifiedPaymentTotal >= $checkoutTotal
            ? 'badge-paid'
            : ($verifiedPaymentTotal > 0 ? 'badge-downpaid' : 'badge-pending'));
    $paymentLabel = $canonicalStatus === \App\Support\BookingStatus::ACCEPTED
        ? ($hasUnderReviewPayment ? 'Downpayment under review' : 'Downpayment due')
        : ($verifiedPaymentTotal >= $checkoutTotal
            ? 'Paid'
            : ($verifiedPaymentTotal > 0 ? 'Balance due at checkout' : 'Due at checkout'));
    $progressStep = match ($canonicalStatus) {
        \App\Support\BookingStatus::PENDING => 2,
        \App\Support\BookingStatus::ACCEPTED => 3,
        \App\Support\BookingStatus::CONFIRMED => 4,
        \App\Support\BookingStatus::CHECKED_IN => 5,
        \App\Support\BookingStatus::COMPLETED => 5,
        \App\Support\BookingStatus::CANCELLED,
        \App\Support\BookingStatus::REJECTED => 1,
    };
    $isStayComplete = \App\Support\BookingStatus::isTerminal($booking->status);
@endphp

<div wire:poll.15s.visible="refreshStatus">
    <div class="page-header-row mb-3">
        <div class="flex-center gap-1">
            @include('bookings.partials.status-indicator', [
                'booking' => $booking,
                'statusBadge' => $statusBadge,
                'statusLabel' => $canonicalStatus,
            ])
            <span class="badge {{ $paymentBadge }}">{{ $paymentLabel }}</span>
        </div>
    </div>

    @if ($booking->status === \App\Support\BookingStatus::ACCEPTED)
        <div class="alert alert-info mb-3" role="status">
            <i class="fa fa-clock"></i>
            <span>Booking accepted. Pay ₱{{ number_format($downpaymentAmount, 2) }} to confirm.</span>
        </div>
    @elseif (in_array($booking->status, [\App\Support\BookingStatus::CONFIRMED, \App\Support\BookingStatus::CHECKED_IN], true))
        <div class="alert alert-success mb-3" role="status">
            <i class="fa fa-check-circle"></i>
            <span>Downpayment verified. Pay any remaining balance at checkout.</span>
        </div>
    @elseif ($booking->status === \App\Support\BookingStatus::REJECTED && $booking->rejection_reason)
        <div class="alert alert-danger mb-3" role="alert">
            <strong>Rejection reason:</strong> {{ $booking->rejection_reason }}
        </div>
    @endif

    <section class="booking-progress" role="progressbar" aria-label="Booking progress" aria-valuemin="1" aria-valuemax="5" aria-valuenow="{{ $progressStep }}">
        @foreach (['Request sent', 'Owner review', 'Downpayment', 'Booking confirmed', 'Stay completed'] as $index => $stepLabel)
            @php
                $stepNumber = $index + 1;
                $stepDone = $stepNumber < $progressStep || $isStayComplete;
                $stepActive = $stepNumber === $progressStep && ! $isStayComplete;
            @endphp
            <div class="booking-progress-step {{ $stepDone ? 'is-done' : '' }} {{ $stepActive ? 'is-active' : '' }}">
                <span class="booking-progress-marker">
                    @if ($stepDone)
                        <i class="fa fa-check" aria-hidden="true"></i>
                    @else
                        {{ $stepNumber }}
                    @endif
                </span>
                <span>{{ $stepLabel }}</span>
            </div>
        @endforeach
    </section>

    @if ($booking->status === \App\Support\BookingStatus::PENDING)
        <div class="mb-3">
            <p class="text-muted mb-0">Waiting for review.</p>
        </div>
    @elseif ($verifiedPaymentTotal >= $checkoutTotal)
        <div class="mb-3">
            <p class="text-muted mb-0">Payment complete.</p>
        </div>
    @elseif ($hasUnderReviewPayment)
        <div class="mb-3">
            <p class="alert alert-info mb-0" role="status">Payment under review.</p>
        </div>
    @endif
</div>
