<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @page { size: A4 landscape; margin: 0; }
        html, body { width: 841.89pt; height: 595.28pt; margin: 0; padding: 0; font-family: DejaVu Sans, sans-serif; }
        .ticket { position: relative; box-sizing: border-box; width: 841.89pt; height: 595.28pt; overflow: hidden; background: #10263f; color: #fff; }
        .banner { position: relative; width: 841.89pt; height: 150pt; background-image: url('{{ $backgroundImage }}'); background-repeat: no-repeat; background-position: center top; background-size: 100% 200%; }
        .banner-shade { position: absolute; top: 0; left: 0; width: 841.89pt; height: 150pt; background: rgba(9, 24, 42, .72); }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .header-table { position: relative; z-index: 1; height: 150pt; }
        .header-table td { width: 50%; padding: 24pt 46pt; vertical-align: middle; }
        .brand { margin: 0 0 7pt; color: #f8c471; font-size: 11pt; font-weight: bold; letter-spacing: 1.5pt; text-transform: uppercase; }
        h1 { margin: 0; color: #fff; font-size: 28pt; line-height: 1.1; }
        .reference-label { display: block; margin-bottom: 7pt; color: #f8c471; font-size: 9pt; font-weight: bold; letter-spacing: 1pt; text-transform: uppercase; }
        .reference { display: block; color: #fff; font-size: 22pt; font-weight: bold; letter-spacing: 1pt; overflow-wrap: break-word; }
        .details { width: 749.89pt; margin: 24pt 46pt 0; }
        .details td { width: 50%; padding: 15pt 18pt; vertical-align: top; border-bottom: 1px solid rgba(255, 255, 255, .3); }
        .label { display: block; margin-bottom: 6pt; color: #f8c471; font-size: 9pt; font-weight: bold; letter-spacing: .8pt; text-transform: uppercase; }
        .value { display: block; color: #fff; font-size: 15pt; font-weight: bold; overflow-wrap: break-word; }
        .note { margin: 22pt 46pt 0; color: #fff; font-size: 10pt; }
    </style>
</head>
<body>
    <main class="ticket">
        <div class="banner">
            <div class="banner-shade"></div>
            <table class="header-table">
                <tr>
                    <td>
                        <p class="brand">Motal and Conteros Transient Booking System</p>
                        <h1>Booking Ticket</h1>
                    </td>
                    <td style="text-align:right">
                        <span class="reference-label">Booking reference</span>
                        <span class="reference">{{ $booking->booking_reference }}</span>
                    </td>
                </tr>
            </table>
        </div>
        <table class="details">
            <tr>
                <td>
                    <span class="label">Unit</span>
                    <span class="value">{{ $booking->unit->unit_type }}</span>
                </td>
                <td>
                    <span class="label">House</span>
                    <span class="value">{{ $booking->unit->house->name }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="label">Check-in</span>
                    <span class="value">{{ $booking->check_in->format('M j, Y') }} · {{ \App\Support\UnitTimeFormatter::format((string) $booking->unit->check_in_time) }}</span>
                </td>
                <td>
                    <span class="label">Check-out</span>
                    <span class="value">{{ $booking->check_out->format('M j, Y') }} · {{ \App\Support\UnitTimeFormatter::format((string) $booking->unit->check_out_time) }}</span>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <span class="label">Guest</span>
                    <span class="value">{{ $booking->displayGuestName() }}</span>
                </td>
            </tr>
        </table>
        <p class="note">Show this ticket to staff.</p>
    </main>
</body>
</html>
