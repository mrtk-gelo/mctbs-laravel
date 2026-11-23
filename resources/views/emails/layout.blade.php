<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MCTBS</title>
</head>
<body style="margin:0;padding:0;background-color:#f7f1e9;font-family:Arial,Helvetica,sans-serif;color:#38291f;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#f7f1e9;padding:28px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="width:100%;max-width:600px;">
                    <tr>
                        <td style="padding:20px 28px;background-color:#9a4f20;border-radius:12px 12px 0 0;color:#ffffff;">
                            <div style="font-size:12px;font-weight:bold;letter-spacing:1.5px;text-transform:uppercase;">MCTBS</div>
                            <div style="margin-top:5px;font-size:15px;line-height:1.4;">Motal and Conteros Transient Booking System</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px 28px;background-color:#ffffff;border:1px solid #ead8c7;border-top:0;">
                            @yield('content')
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:18px 24px;background-color:#fffaf5;border:1px solid #ead8c7;border-top:0;border-radius:0 0 12px 12px;color:#78685e;font-size:12px;line-height:1.6;text-align:center;">
                            This is a transactional message from MCTBS. If you need assistance, contact the person or property associated with your booking or invitation.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
