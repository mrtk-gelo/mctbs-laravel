<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Services\BookingDashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request, BookingDashboardService $dashboard): View
    {
        $reviewBookingId = $request->query('review');
        $focusBookingId = is_string($reviewBookingId) && ctype_digit($reviewBookingId)
            ? (int) $reviewBookingId
            : null;

        return view('owner.dashboard', $dashboard->queuesFor($request->user(), $focusBookingId));
    }
}
