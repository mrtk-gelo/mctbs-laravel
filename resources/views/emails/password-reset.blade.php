@extends('emails.layout')

@section('content')
    <p style="margin:0 0 14px;font-size:16px;line-height:1.6;">Hello {{ $guestName }},</p>
    <h1 style="margin:0 0 14px;color:#5e351f;font-size:24px;line-height:1.3;">Reset your password</h1>
    <p style="margin:0 0 22px;color:#5f5148;font-size:15px;line-height:1.7;">We received a request to reset the password for your MCTBS account. Use the button below to choose a new password. If you did not request this, you can safely ignore this message.</p>
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin:0 0 20px;">
        <tr><td bgcolor="#b65f25" style="border-radius:7px;">
            <a href="{{ $resetUrl }}" style="display:inline-block;padding:13px 20px;color:#ffffff;font-size:15px;font-weight:bold;text-decoration:none;">Reset password</a>
        </td></tr>
    </table>
    <p style="margin:0;color:#78685e;font-size:13px;line-height:1.6;">This link expires in {{ $expiresInMinutes }} minutes and can be used only once.</p>
@endsection
