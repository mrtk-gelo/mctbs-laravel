<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @page { margin: 28pt; }
        body { margin: 0; color: #263238; font-family: DejaVu Sans, sans-serif; font-size: 9pt; }
        h1 { margin: 0; font-size: 19pt; }
        h2 { margin: 0 0 6pt; color: #506070; font-size: 11pt; }
        p { margin: 4pt 0; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .header { margin-bottom: 18pt; padding: 14pt; background: #2f5f7f; color: #fff; text-align: center; }
        .header p { margin-top: 5pt; }
        .metadata { margin-bottom: 16pt; }
        .metadata td { width: 50%; padding: 5pt 8pt; vertical-align: top; }
        .muted { color: #697783; }
        .breakdown th, .breakdown td, .transactions th, .transactions td { padding: 6pt 8pt; border-bottom: 1px solid #d9e0e5; text-align: left; vertical-align: top; }
        .breakdown th, .transactions th { background: #f1f4f6; font-weight: bold; }
        .amount { text-align: right !important; white-space: nowrap; }
        .blue { color: #1769aa; }
        .red { color: #bd3030; }
        .green { color: #237a45; }
        .orange { color: #c66a18; }
        .total td { font-weight: bold; }
        .section { margin-top: 16pt; }
        .footer { margin-top: 18pt; color: #697783; font-size: 8pt; text-align: center; }
    </style>
</head>
<body>
    @php
        $activeGuests = $booking->activeGuests;
        $payingGuestCount = $activeGuests->isNotEmpty()
            ? $activeGuests->where('is_free_child', false)->count()
            : null;
        $nightlyRate = (float) ($booking->nightly_rate ?? $booking->unit->price);
        $roomQuantity = ($booking->pricing_mode ?? 'Per Unit') === 'Per Head'
            ? ($payingGuestCount === null ? 'Paying guests as booked' : $payingGuestCount.' paying guests').' × '.$nights.' nights'
            : $nights.' nights';
        $roomPrice = '₱'.number_format($nightlyRate, 2)
            .(($booking->pricing_mode ?? 'Per Unit') === 'Per Head' ? ' / guest / night' : ' / unit / night');
    @endphp

    <header class="header">
        <h1>MCTBS</h1>
        <p>Motal and Conteros Transient Booking System</p>
        <strong>Booking Breakdown · {{ $receipt->receipt_number }}</strong>
    </header>

    <table class="metadata">
        <tr>
            <td>
                <h2>Guest</h2>
                <strong>{{ $booking->displayGuestName() }}</strong>
                @if ($booking->displayGuestEmail())<p>{{ $booking->displayGuestEmail() }}</p>@endif
                @if ($booking->displayGuestPhone())<p>{{ $booking->displayGuestPhone() }}</p>@endif
            </td>
            <td>
                <h2>Property</h2>
                <strong>{{ $booking->unit->unit_type }}</strong>
                <p>{{ $booking->unit->house->name }}</p>
                <p>{{ $booking->unit->house->address }}, {{ $booking->unit->house->city }}</p>
            </td>
        </tr>
        <tr>
            <td>
                <h2>Stay details</h2>
                <p>Booking ID: <strong>#{{ $booking->booking_id }}</strong></p>
                <p>Check-in: <strong>{{ $booking->check_in->format('M j, Y') }}</strong></p>
                <p>Check-out: <strong>{{ $booking->check_out->format('M j, Y') }}</strong></p>
                <p>Nights: <strong>{{ $nights }}</strong></p>
                <p>Adult paying guests: <strong>{{ $booking->adult_count }}</strong></p>
                <p>Free children: <strong>{{ $booking->child_count }}</strong></p>
            </td>
            <td>
                <h2>Payment summary</h2>
                <p>Status: <strong>{{ \App\Support\BookingStatus::canonical($booking->status) }}</strong></p>
                <p>Final amount due: <strong>₱{{ number_format($financialSummary['finalAmountDue'], 2) }}</strong></p>
                <p>Payments made (verified only): <strong class="green">₱{{ number_format($financialSummary['verifiedPayments'], 2) }}</strong></p>
                @if ($financialSummary['completedRefunds'] > 0)
                    <p>Completed refunds: <strong class="orange">₱{{ number_format($financialSummary['completedRefunds'], 2) }}</strong></p>
                @endif
                <p>Outstanding balance: <strong class="{{ $financialSummary['outstandingBalance'] > 0 ? 'red' : 'green' }}">₱{{ number_format($financialSummary['outstandingBalance'], 2) }}</strong></p>
                @if ($financialSummary['refundableOverpayment'] > 0)
                    <p>Refundable overpayment: <strong class="orange">₱{{ number_format($financialSummary['refundableOverpayment'], 2) }}</strong></p>
                @endif
            </td>
        </tr>
    </table>

    <table class="breakdown">
        <thead><tr><th>Booking breakdown</th><th class="amount">Amount</th></tr></thead>
        <tbody>
            <tr>
                <td>Stay/room charge · {{ $roomQuantity }} × {{ $roomPrice }}</td>
                <td class="amount">₱{{ number_format($financialSummary['roomCharges'], 2) }}</td>
            </tr>
            @if ($financialSummary['extraGuestCharges'] > 0)
                <tr>
                    <td>Extra guest charges · {{ $booking->extra_guest_count }} × {{ $nights }} nights × ₱{{ number_format((float) $booking->extra_guest_fee, 2) }} / guest / night · included in accommodation</td>
                    <td class="amount">₱{{ number_format($financialSummary['extraGuestCharges'], 2) }}</td>
                </tr>
            @endif
            @if ($financialSummary['approvedDiscounts'] > 0)
                <tr><td>Eligible verified discounts</td><td class="amount green">−₱{{ number_format($financialSummary['approvedDiscounts'], 2) }}</td></tr>
            @endif
            <tr class="total"><td>Accommodation after discounts</td><td class="amount">₱{{ number_format($financialSummary['accommodationAfterDiscounts'], 2) }}</td></tr>
            @foreach ($booking->addons as $addon)
                @if ((float) $addon->price * (int) $addon->quantity > 0)
                    <tr><td>Amenity/add-on · {{ $addon->name }} × {{ $addon->quantity }} (included by financial contract)</td><td class="amount blue">₱{{ number_format((float) $addon->price * $addon->quantity, 2) }}</td></tr>
                @endif
            @endforeach
            @foreach ($booking->damages as $damage)
                @if ((float) $damage->price * (int) $damage->quantity > 0)
                    <tr><td>Historical damage/missing item · {{ $damage->name }} · Qty {{ $damage->quantity }} · ₱{{ number_format((float) $damage->price, 2) }} / item (not included; no approval status)</td><td class="amount red">₱{{ number_format((float) $damage->price * $damage->quantity, 2) }}</td></tr>
                @endif
            @endforeach
            @foreach ($booking->additionalCharges as $charge)
                @if ((float) $charge->total_amount > 0)
                    <tr><td>Additional charge · {{ $charge->description }} × {{ $charge->quantity }} (included by financial contract)</td><td class="amount red">₱{{ number_format((float) $charge->total_amount, 2) }}</td></tr>
                @endif
            @endforeach
            @foreach ($booking->damageReports as $damage)
                @if ((float) $damage->amount > 0)
                    <tr>
                        <td>
                            Damage/missing items · {{ $damage->description }} · Qty {{ $damage->quantity }}
                            @if ($damage->unit_price !== null)
                                · ₱{{ number_format((float) $damage->unit_price, 2) }} / item
                            @else
                                · Unit price unavailable
                            @endif
                            ({{ $damage->isIncludedInFinalAmount() ? 'included in amount due' : 'not included in amount due' }})
                        </td>
                        <td class="amount red">₱{{ number_format($damage->calculatedChargeTotal(), 2) }}</td>
                    </tr>
                @endif
            @endforeach
            <tr class="total"><td>Final amount due</td><td class="amount">₱{{ number_format($financialSummary['finalAmountDue'], 2) }}</td></tr>
        </tbody>
    </table>

    @if ($booking->discounts->isNotEmpty())
        <section class="section">
            <h2>Discount verification</h2>
            <table class="transactions">
                <thead><tr><th>Guest</th><th>Discount</th><th>Status</th><th>Bill treatment</th><th class="amount">Amount</th></tr></thead>
                <tbody>
                    @foreach ($booking->discounts as $discount)
                        <tr>
                            <td>{{ $discount->guest->full_name }}</td>
                            <td>{{ $discount->discount_type }}</td>
                            <td>{{ $discount->verification_status }}</td>
                            <td>
                                @if ($discount->guest->roster_status !== \App\Models\BookingGuest::ROSTER_ACTIVE)
                                    Removed guest; excluded
                                @elseif ($discount->verification_status === 'Verified')
                                    Applied once to accommodation
                                @else
                                    Not applied
                                @endif
                            </td>
                            <td class="amount">{{ $discount->verification_status === 'Verified' && $discount->guest->roster_status === \App\Models\BookingGuest::ROSTER_ACTIVE ? '−₱'.number_format($discount->discount_amount, 2) : '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    @endif

    <section class="section">
        <h2>Payment transactions</h2>
        <table class="transactions">
            <thead><tr><th>Date</th><th>Method</th><th>Reference</th><th>Status</th><th class="amount">Amount</th></tr></thead>
            <tbody>
                @forelse ($booking->payments as $payment)
                    <tr>
                        <td>{{ $payment->payment_date?->format('M j, Y g:i A') ?? 'Date unavailable' }}</td>
                        <td>{{ $payment->payment_method }}</td>
                        <td>{{ $payment->reference_number ?: '—' }}</td>
                        <td>{{ $payment->payment_status }}</td>
                        <td class="amount {{ $payment->payment_status === \App\Support\PaymentStatus::VERIFIED ? 'green' : '' }}">₱{{ number_format($payment->amount, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="muted">No payments recorded.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <section class="section">
        <h2>Refund transactions</h2>
        <table class="transactions">
            <thead><tr><th>Date</th><th>Reason</th><th>Reference / payment</th><th>Status</th><th class="amount">Amount</th></tr></thead>
            <tbody>
                @forelse ($booking->refunds as $refund)
                    <tr>
                        <td>{{ $refund->processed_at?->format('M j, Y g:i A') ?? $refund->created_at?->format('M j, Y g:i A') ?? 'Date unavailable' }}</td>
                        <td>{{ $refund->reason }}</td>
                        <td>{{ $refund->refund_reference ?: '—' }} · Payment #{{ $refund->payment_id }}</td>
                        <td>{{ $refund->refund_status }}</td>
                        <td class="amount {{ $refund->refund_status === 'Refunded' ? 'orange' : '' }}">₱{{ number_format($refund->amount, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="muted">No refunds recorded.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <p class="footer">Created {{ $receipt->issued_at->format('F j, Y g:i A') }} · Not an official receipt.</p>
</body>
</html>
