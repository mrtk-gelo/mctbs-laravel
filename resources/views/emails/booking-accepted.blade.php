@extends('emails.layout')

@section('content')
    <p style="margin:0 0 14px;font-size:16px;line-height:1.6;">Hello {{ $guestName }},</p>
    <h1 style="margin:0 0 14px;color:#5e351f;font-size:24px;line-height:1.3;">Your booking request was accepted</h1>
    <p style="margin:0 0 20px;color:#5f5148;font-size:15px;line-height:1.7;">The dates are reserved while you submit the downpayment to confirm your reservation.</p>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin:0 0 22px;background-color:#fffaf5;border:1px solid #ead8c7;">
        <tr><td style="padding:16px 18px;font-size:14px;line-height:1.7;">
            <strong>Booking #{{ $booking->booking_id }}</strong><br>
            {{ $booking->unit->house->name }} · {{ $booking->unit->unit_type }}<br>
            Check-in: {{ $booking->check_in->format('M j, Y') }} at {{ \App\Support\UnitTimeFormatter::format((string) $booking->unit->check_in_time) }}<br>
            Check-out: {{ $booking->check_out->format('M j, Y') }} at {{ \App\Support\UnitTimeFormatter::format((string) $booking->unit->check_out_time) }}
        </td></tr>
    </table>

    <p style="margin:0 0 18px;color:#5f5148;font-size:15px;line-height:1.7;">Submit the downpayment of <strong>₱{{ number_format($downpaymentAmount, 2) }}</strong>. It will be credited to your final bill; any remaining balance, including booking changes and checkout charges, is due at checkout.</p>
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin:0 0 20px;">
        <tr><td bgcolor="#b65f25" style="border-radius:7px;">
            <a href="{{ $paymentUrl }}" style="display:inline-block;padding:13px 20px;color:#ffffff;font-size:15px;font-weight:bold;text-decoration:none;">Submit your downpayment</a>
        </td></tr>
    </table>
    <p style="margin:0;color:#78685e;font-size:13px;line-height:1.6;">If you requested a Senior Citizen or PWD discount, bring the original valid ID. The discount is applied after it is verified at checkout.</p>
@endsection
