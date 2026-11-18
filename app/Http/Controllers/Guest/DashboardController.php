<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Support\BookingStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $bookings = Booking::with(['unit.house', 'discounts.guest'])
            ->where('user_id', Auth::id())
            ->latest('created_at')
            ->get();

        return view('guest.dashboard', [
            'pending' => $bookings->where('status', BookingStatus::PENDING),
            'accepted' => $bookings->whereIn('status', [BookingStatus::ACCEPTED, BookingStatus::CONFIRMED, BookingStatus::CHECKED_IN]),
            'completed' => $bookings->whereIn('status', [
                ...BookingStatus::completedStatuses(),
                BookingStatus::CANCELLED,
                BookingStatus::REJECTED,
            ]),
        ]);
    }
}
