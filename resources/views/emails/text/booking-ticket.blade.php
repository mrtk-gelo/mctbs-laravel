Hello {{ $guestName }},

Your booking is confirmed. Your downpayment has been verified and your dates are reserved.

Booking reference: {{ $booking->booking_reference }}
Transient House: {{ $booking->unit->house->name }}
Unit: {{ $booking->unit->unit_type }}
Check-in: {{ $booking->check_in->format('M j, Y') }} at {{ \App\Support\UnitTimeFormatter::format((string) $booking->unit->check_in_time) }}
Check-out: {{ $booking->check_out->format('M j, Y') }} at {{ \App\Support\UnitTimeFormatter::format((string) $booking->unit->check_out_time) }}

Your Booking Ticket is attached as a PDF. Open or download it and show it to staff when you arrive.
