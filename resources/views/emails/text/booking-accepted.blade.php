Hello {{ $guestName }},

Your booking request was accepted, and the dates are reserved while you submit the downpayment.

Booking: #{{ $booking->booking_id }}
Property: {{ $booking->unit->house->name }}
Unit: {{ $booking->unit->unit_type }}
Check-in: {{ $booking->check_in->format('M j, Y') }} at {{ \App\Support\UnitTimeFormatter::format((string) $booking->unit->check_in_time) }}
Check-out: {{ $booking->check_out->format('M j, Y') }} at {{ \App\Support\UnitTimeFormatter::format((string) $booking->unit->check_out_time) }}

Submit the downpayment of ₱{{ number_format($downpaymentAmount, 2) }} to confirm your reservation. Your downpayment will be credited to your final bill; any remaining balance, including booking changes and checkout charges, is due at checkout.

Submit your downpayment: {{ $paymentUrl }}

If you requested a Senior Citizen or PWD discount, bring the original valid ID; the discount is applied after it is verified at checkout.
