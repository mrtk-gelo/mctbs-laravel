
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">

    <style>
        @page {
            size: A4 landscape;
            margin: 30pt 34pt;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 9pt;
            color: #243247;
            background: #ffffff;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .top-rule {
            height: 5pt;
            background: #b66b3d;
        }

        .header {
            margin-bottom: 20pt;
        }

        .header td {
            vertical-align: middle;
        }

        .brand-kicker {
            color: #a45d32;
            font-size: 8pt;
            font-weight: bold;
            letter-spacing: 1.5pt;
            text-transform: uppercase;
        }

        .brand-name {
            color: #34445a;
            font-size: 14pt;
            font-weight: bold;
        }

        .document-title {
            margin-top: 12pt;
            color: #17283e;
            font-size: 25pt;
        }

        .document-subtitle {
            color: #718096;
            font-size: 8.5pt;
        }

        .reference-cell {
            width: 36%;
            padding-left: 16pt;
        }

        .reference-card {
            padding: 13pt 14pt;
            background: #fffaf5;
            border: 1px solid #ead9ca;
            border-top: 4pt solid #b66b3d;
        }

        .reference-label {
            display: block;
            margin-bottom: 7pt;
            color: #8c654c;
            font-size: 7pt;
            font-weight: bold;
            letter-spacing: 1.2pt;
            text-transform: uppercase;
        }

        .reference {
            display: block;
            color: #9d542c;
            font-size: 17pt;
            font-weight: bold;
            line-height: 1.25;
        }

        .reference-note {
            display: block;
            margin-top: 5pt;
            color: #8b7769;
            font-size: 7pt;
        }

        .section-heading {
            margin-bottom: 8pt;
            color: #34445a;
            font-size: 8pt;
            font-weight: bold;
            letter-spacing: 1.1pt;
            text-transform: uppercase;
        }

        .details td {
            width: 50%;
            padding: 13pt 15pt;
            vertical-align: top;
            border: 1px solid #e5e9ee;
            background: #ffffff;
        }

        .details tr:nth-child(odd) td {
            background: #fbfcfd;
        }

        .field-label {
            display: block;
            margin-bottom: 6pt;
            color: #8b6a55;
            font-size: 7pt;
            font-weight: bold;
            letter-spacing: 1pt;
            text-transform: uppercase;
        }

        .field-value {
            display: block;
            color: #26364b;
            font-size: 12pt;
            font-weight: bold;
            line-height: 1.35;
        }

        .field-meta {
            display: block;
            margin-top: 4pt;
            color: #718096;
            font-size: 8pt;
        }

        .status {
            display: inline-block;
            padding: 5pt 8pt;
            background: #edf6f0;
            color: #286344;
            border: 1px solid #d3e8da;
            font-size: 8pt;
            font-weight: bold;
        }

        .footer {
            margin-top: 17pt;
        }

        .footer td {
            padding-top: 10pt;
            border-top: 1px solid #e5e9ee;
            color: #748091;
            font-size: 7.5pt;
            line-height: 1.5;
            vertical-align: top;
        }

        .footer-brand {
            text-align: right;
            color: #8b6a55;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <div class="top-rule"></div>

    <table class="header">
        <tr>
            <td style="width: 64%;">
                <p class="brand-kicker">MCTBS Reservation</p>

                <p class="brand-name">
                    Motal and Conteros Transient Booking System
                </p>

                <h1 class="document-title">Booking Ticket</h1>

                <p class="document-subtitle">
                    Reservation confirmation and guest check-in details
                </p>
            </td>

            <td class="reference-cell">
                <div class="reference-card">
                    <span class="reference-label">
                        Booking reference
                    </span>

                    <span class="reference">
                        {{ $booking->booking_reference }}
                    </span>

                    <span class="reference-note">
                        Please present this reference upon arrival.
                    </span>
                </div>
            </td>
        </tr>
    </table>

    <p class="section-heading">Reservation Details</p>

    <table class="details">
        <tr>
            <td>
                <span class="field-label">Guest Name</span>

                <span class="field-value">
                    {{ $booking->displayGuestName() }}
                </span>

                @if ($booking->displayGuestEmail())
                    <span class="field-meta">
                        {{ $booking->displayGuestEmail() }}
                    </span>
                @endif
            </td>

            <td>
                <span class="field-label">Transient House</span>

                <span class="field-value">
                    {{ $booking->unit->house->name }}
                </span>

                @if ($booking->unit->house->address || $booking->unit->house->city)
                    <span class="field-meta">
                        {{ collect([
                            $booking->unit->house->address,
                            $booking->unit->house->city
                        ])->filter()->implode(', ') }}
                    </span>
                @endif
            </td>
        </tr>

        <tr>
            <td>
                <span class="field-label">Accommodation / Unit</span>

                <span class="field-value">
                    {{ $booking->unit->unit_type }}
                </span>
            </td>

            <td>
                <span class="field-label">Booking Status</span>

                <span class="status">
                    Confirmed reservation
                </span>
            </td>
        </tr>

        <tr>
            <td>
                <span class="field-label">Check-in</span>

                <span class="field-value">
                    {{ $booking->check_in->format('M j, Y') }}
                </span>

                <span class="field-meta">
                    {{ \App\Support\UnitTimeFormatter::format((string) $booking->unit->check_in_time) }}
                </span>
            </td>

            <td>
                <span class="field-label">Check-out</span>

                <span class="field-value">
                    {{ $booking->check_out->format('M j, Y') }}
                </span>

                <span class="field-meta">
                    {{ \App\Support\UnitTimeFormatter::format((string) $booking->unit->check_out_time) }}
                </span>
            </td>
        </tr>
    </table>

    <table class="footer">
        <tr>
            <td>
                <strong>Important:</strong>
                Please present this ticket to the property staff
                when you arrive. Keep your booking reference
                available if you need assistance.
            </td>

            <td class="footer-brand" style="width: 30%;">
                MCTBS | GUEST RESERVATION
            </td>
        </tr>
    </table>

</body>
</html>
