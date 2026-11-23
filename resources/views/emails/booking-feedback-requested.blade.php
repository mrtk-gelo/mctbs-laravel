@extends('emails.layout')

@section('content')
    <p style="margin:0 0 14px;font-size:16px;line-height:1.6;">Hello {{ $guestName }},</p>
    <h1 style="margin:0 0 14px;color:#5e351f;font-size:24px;line-height:1.3;">How was your stay?</h1>
    <p style="margin:0 0 22px;color:#5f5148;font-size:15px;line-height:1.7;">Thank you for staying at {{ $booking->unit->house->name }}. We would appreciate a few moments of your time to rate your stay and share any feedback.</p>
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin:0 0 20px;">
        <tr><td bgcolor="#b65f25" style="border-radius:7px;">
            <a href="{{ $feedbackUrl }}" style="display:inline-block;padding:13px 20px;color:#ffffff;font-size:15px;font-weight:bold;text-decoration:none;">Share your feedback</a>
        </td></tr>
    </table>
    <p style="margin:0;color:#78685e;font-size:13px;line-height:1.6;">This secure link expires in 7 days and can be used once for booking {{ $booking->booking_reference }}.</p>
@endsection
