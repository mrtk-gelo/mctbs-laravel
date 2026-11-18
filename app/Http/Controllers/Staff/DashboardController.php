<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Services\BookingDashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request, BookingDashboardService $dashboard): View
    {
        return view('staff.dashboard', $dashboard->queuesFor($request->user()));
    }
}
