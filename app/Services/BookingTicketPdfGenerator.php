<?php

namespace App\Services;

use App\Models\Booking;
use Barryvdh\DomPDF\Facade\Pdf;

class BookingTicketPdfGenerator
{
    public function generate(Booking $booking): string
    {
        $booking->loadMissing(['unit.house', 'guest']);

        return Pdf::loadView('guest.bookings.ticket', ['booking' => $booking])
            ->setPaper('a4', 'landscape')
            ->output();
    }
}
