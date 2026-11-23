@extends('emails.layout')

@section('content')
    <p style="margin:0 0 14px;font-size:16px;line-height:1.6;">Hello {{ $guestName }},</p>
    <h1 style="margin:0 0 14px;color:#5e351f;font-size:24px;line-height:1.3;">Your booking is confirmed</h1>
    <p style="margin:0 0 20px;color:#5f5148;font-size:15px;line-height:1.7;">Your downpayment has been verified and your dates are reserved. Your Booking Ticket is attached as a PDF; open or download the attachment and keep it available for check-in.</p>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin:0 0 20px;background-color:#fffaf5;border:1px solid #ead8c7;">
        <tr><td style="padding:16px 18px;font-size:14px;line-height:1.7;">
            <span style="color:#79513a;font-size:11px;font-weight:bold;letter-spacing:1px;text-transform:uppercase;">Booking reference</span><br>
            <strong style="color:#9a4f20;font-size:20px;">{{ $booking->booking_reference }}</strong><br>
            {{ $booking->unit->house->name }} · {{ $booking->unit->unit_type }}<br>
            {{ $booking->check_in->format('M j, Y') }} – {{ $booking->check_out->format('M j, Y') }}
        </td></tr>
    </table>

    <p style="margin:0;color:#78685e;font-size:13px;line-height:1.6;">Please show the attached ticket to staff when you arrive. If you cannot find the attachment, visit your booking in MCTBS to send it again.</p>
@endsection
