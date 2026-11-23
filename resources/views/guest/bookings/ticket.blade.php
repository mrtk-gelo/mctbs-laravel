<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <style>
        @page { size: A4 landscape; margin: 28pt; }
        html, body { margin: 0; padding: 0; font-family: DejaVu Sans, sans-serif; color: #38291f; }
        body { font-size: 11pt; }
        .ticket { width: 100%; padding: 0; }
        .header, .details, .footer { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .header { margin-bottom: 22pt; }
        .header td { vertical-align: middle; }
        .brand { margin: 0 0 5pt; color: #9a4f20; font-size: 10pt; font-weight: bold; letter-spacing: 1.2pt; text-transform: uppercase; }
        .brand-name { margin: 0; color: #5e351f; font-size: 17pt; font-weight: bold; }
        h1 { margin: 14pt 0 0; color: #38291f; font-size: 25pt; line-height: 1.15; }
        .reference-card { width: 100%; padding: 16pt 18pt; border: 1px solid #ead8c7; border-top: 4pt solid #c86a2b; border-radius: 6pt; background: #fffaf5; }
        .reference-label { display: block; margin-bottom: 7pt; color: #79513a; font-size: 8pt; font-weight: bold; letter-spacing: 1pt; text-transform: uppercase; }
        .reference { display: block; color: #9a4f20; font-size: 20pt; font-weight: bold; letter-spacing: .7pt; overflow-wrap: anywhere; }
        .details td { width: 50%; padding: 15pt 17pt; vertical-align: top; border: 1px solid #ead8c7; background: #fff; }
        .details tr:first-child td:first-child { border-top-left-radius: 6pt; }
        .details tr:first-child td:last-child { border-top-right-radius: 6pt; }
        .details tr:last-child td:first-child { border-bottom-left-radius: 6pt; }
        .details tr:last-child td:last-child { border-bottom-right-radius: 6pt; }
        .label { display: block; margin-bottom: 6pt; color: #79513a; font-size: 8pt; font-weight: bold; letter-spacing: .7pt; text-transform: uppercase; }
        .value { display: block; color: #38291f; font-size: 13pt; font-weight: bold; line-height: 1.35; overflow-wrap: anywhere; }
        .footer { margin-top: 18pt; }
        .footer td { padding-top: 11pt; border-top: 1px solid #ead8c7; color: #6e625a; font-size: 9pt; line-height: 1.5; }
        .footer td:last-child { text-align: right; }
    </style>
</head>
<body>
    <main class="ticket">
        <table class="header">
            <tr>
                <td>
                    <p class="brand">MCTBS Reservation</p>
                    <p class="brand-name">Motal and Conteros Transient Booking System</p>
                    <h1>Booking Ticket</h1>
                </td>
                <td style="width: 42%; text-align: right">
                    <div class="reference-card">
                        <span class="reference-label">Booking reference</span>
                        <span class="reference">{{ $booking->booking_reference }}</span>
                    </div>
                </td>
            </tr>
        </table>

        <table class="details">
            <tr>
                <td>
                    <span class="label">Guest</span>
                    <span class="value">{{ $booking->displayGuestName() }}</span>
                </td>
                <td>
                    <span class="label">Transient House</span>
                    <span class="value">{{ $booking->unit->house->name }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="label">Unit</span>
                    <span class="value">{{ $booking->unit->unit_type }}</span>
                </td>
                <td>
                    <span class="label">Check-in</span>
                    <span class="value">{{ $booking->check_in->format('M j, Y') }} · {{ \App\Support\UnitTimeFormatter::format((string) $booking->unit->check_in_time) }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="label">Check-out</span>
                    <span class="value">{{ $booking->check_out->format('M j, Y') }} · {{ \App\Support\UnitTimeFormatter::format((string) $booking->unit->check_out_time) }}</span>
                </td>
                <td>
                    <span class="label">Booking status</span>
                    <span class="value">Confirmed reservation</span>
                </td>
            </tr>
        </table>

        <table class="footer">
            <tr>
                <td>Please present this ticket to staff when you arrive. Keep your booking reference available for assistance.</td>
                <td>MCTBS · Guest Reservation</td>
            </tr>
        </table>
    </main>
</body>
</html>
