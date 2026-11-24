@php
    $indicatorMessage = null;

    if ($booking->refunds->where('refund_status', 'Refunded')->sum('amount') > 0) {
        $refundAmount = (float) $booking->refunds->where('refund_status', 'Refunded')->sum('amount');
        $indicatorMessage = 'Refund issued: ₱'.number_format($refundAmount, 2);
    } elseif ($booking->checkedOutEarly() && filled($booking->early_checkout_reason)) {
        $indicatorMessage = 'Early Check-out: '.$booking->early_checkout_reason;
    } elseif ($booking->status === \App\Support\BookingStatus::CANCELLED && filled($booking->cancellation_reason)) {
        $indicatorMessage = 'Cancellation reason: '.$booking->cancellation_reason;
    } elseif ($booking->status === \App\Support\BookingStatus::REJECTED && filled($booking->rejection_reason)) {
        $indicatorMessage = 'Rejection reason: '.$booking->rejection_reason;
    }
@endphp

<span
    class="booking-status-indicator"
    @if ($indicatorMessage)
        data-status-tooltip="{{ $indicatorMessage }}"
        aria-label="{{ $statusLabel }}. {{ $indicatorMessage }}"
        aria-describedby="booking-status-tooltip"
        tabindex="0"
    @endif
>
    <span class="badge {{ $statusBadge }}">{{ $statusLabel }}</span>
    @if ($indicatorMessage)
        <span class="booking-status-indicator__dot" aria-hidden="true"></span>
    @endif
</span>
