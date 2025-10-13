<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Property;
use App\Models\Application; // 👈 swap in Application
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $todayStart = Carbon::today();
        $todayEnd   = Carbon::today()->endOfDay();

        $metrics = [
            'total_users'         => User::count(),
            'active_properties'   => Property::where('status', 'active')->count(),
            'pending_properties'  => Property::where('status', 'pending')->count(),
            // Keep the name "bookings_today" so your Blade doesn't change
            'bookings_today'      => Application::whereBetween('created_at', [$todayStart, $todayEnd])->count(),
        ];

        $pendingProperties = Property::with('user')
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        // Keep the name "recentBookings" so your Blade doesn't change
        $recentBookings = Application::with(['applicant', 'property'])
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('metrics', 'pendingProperties', 'recentBookings'));
    }
}
