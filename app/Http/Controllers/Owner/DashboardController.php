<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Services\BookingDashboardService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(BookingDashboardService $dashboard): View
    {
        $owner = Auth::user();

        return view('owner.dashboard', $dashboard->queuesFor($owner));
    }
}
