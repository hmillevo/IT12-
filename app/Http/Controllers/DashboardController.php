<?php

namespace App\Http\Controllers;

use App\Models\DeliveryRequest;
use App\Models\Trip;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        $stats = [
            'pending_requests' => DeliveryRequest::where('status', 'pending')->count(),
            'active_trips' => Trip::where('status', 'in-transit')->count(),
            'available_drivers' => Driver::where('status', 'available')->count(),
            'available_vehicles' => Vehicle::where('status', 'available')->count(),
            'today_trips' => Trip::whereDate('scheduled_time', $today)->count(),
            'completed_today' => Trip::whereDate('actual_end_time', $today)
                ->where('status', 'completed')->count()
        ];

        $recentRequests = DeliveryRequest::with('client')
            ->orderBy('created_at', 'desc')
            ->simplePaginate(3, ['*'], 'recent_page')
            ->withPath(route('dashboard.recent-requests'));


        $activeTrips = Trip::with(['deliveryRequest.client', 'driver', 'vehicle'])
            ->where('status', 'in-transit')
            ->orderBy('scheduled_time', 'desc')
            ->simplePaginate(3, ['*'], 'active_page')
            ->withPath(route('dashboard.active-trips'));

        $todaySchedule = Trip::with(['deliveryRequest.client', 'driver', 'vehicle'])
            ->whereDate('scheduled_time', $today)
            ->orderBy('scheduled_time')
            ->simplePaginate(3, ['*'], 'schedule_page')
            ->withPath(route('dashboard.today-schedule'));

        return view('dispatch.dashboard', compact('stats', 'recentRequests', 'activeTrips', 'todaySchedule'));
    }

    public function recentRequests(Request $request)
    {
        $recentRequests = DeliveryRequest::with('client')
            ->orderBy('created_at', 'desc')
            ->simplePaginate(3, ['*'], 'recent_page')
            ->withPath(route('dashboard.recent-requests'));

        if ($request->ajax()) {
            $html = view('dispatch.dashboard.partials.recent-requests', compact('recentRequests'))->render();

            return response()->json(['html' => $html]);
        }

        return redirect()->route('dashboard');
    }

    public function activeTrips(Request $request)
    {
        $activeTrips = Trip::with(['deliveryRequest.client', 'driver', 'vehicle'])
            ->where('status', 'in-transit')
            ->orderBy('scheduled_time', 'desc')
            ->simplePaginate(5, ['*'], 'active_page')
            ->withPath(route('dashboard.active-trips'));

        if ($request->ajax()) {
            $html = view('dispatch.dashboard.partials.active-trips', compact('activeTrips'))->render();

            return response()->json(['html' => $html]);
        }

        return redirect()->route('dashboard');
    }

    public function todaySchedule(Request $request)
    {
        $today = Carbon::today();

        $todaySchedule = Trip::with(['deliveryRequest.client', 'driver', 'vehicle'])
            ->whereDate('scheduled_time', $today)
            ->orderBy('scheduled_time')
            ->simplePaginate(3, ['*'], 'schedule_page')
            ->withPath(route('dashboard.today-schedule'));

        if ($request->ajax()) {
            $html = view('dispatch.dashboard.partials.today-schedule', compact('todaySchedule'))->render();

            return response()->json(['html' => $html]);
        }

        return redirect()->route('dashboard');
    }

    public function getStats()
    {
        $stats = [
            'pending_requests' => DeliveryRequest::where('status', 'pending')->count(),
            'active_trips' => Trip::where('status', 'in-transit')->count(),
            'available_drivers' => Driver::where('status', 'available')->count(),
            'available_vehicles' => Vehicle::where('status', 'available')->count(),
            'today_trips' => Trip::whereDate('scheduled_time', \Carbon\Carbon::today())->count(),
            'completed_today' => Trip::whereDate('actual_end_time', \Carbon\Carbon::today())
                ->where('status', 'completed')->count()
        ];

        return response()->json(['success' => true, 'stats' => $stats]);
    }

    public function globalSearch(Request $request)
    {
        $query = $request->input('q');

        $results = [
            'requests' => DeliveryRequest::where('atw_reference', 'LIKE', "%{$query}%")
                ->orWhereHas('client', function ($q) use ($query) {
                    $q->where('name', 'LIKE', "%{$query}%");
                })
                ->with('client')
                ->limit(5)
                ->get(),

            'trips' => Trip::whereHas('deliveryRequest', function ($q) use ($query) {
                $q->where('atw_reference', 'LIKE', "%{$query}%");
            })
                ->with(['deliveryRequest.client', 'driver', 'vehicle'])
                ->limit(5)
                ->get(),

            'drivers' => Driver::where('name', 'LIKE', "%{$query}%")
                ->orWhere('mobile', 'LIKE', "%{$query}%")
                ->limit(5)
                ->get(),

            'vehicles' => Vehicle::where('plate_number', 'LIKE', "%{$query}%")
                ->limit(5)
                ->get(),

            'clients' => Client::where('name', 'LIKE', "%{$query}%")
                ->orWhere('company', 'LIKE', "%{$query}%")
                ->limit(5)
                ->get(),
        ];

        return view('dispatch.search.results', compact('results', 'query'));
    }

    public function settings()
    {
        return view('dispatch.settings.index');
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'items_per_page' => 'required|integer|min:5|max:100',
            'app_name' => 'required|string|max:255',
            'timezone' => 'required|string|max:50',
            'date_format' => 'required|string|max:20',
        ], [
            'items_per_page.min' => 'Items per page must be at least 5.',
            'items_per_page.max' => 'Items per page cannot exceed 100.',
            'items_per_page.integer' => 'Items per page must be a whole number.',
        ]);

        // Update .env file
        $envPath = base_path('.env');
        if (file_exists($envPath)) {
            $envContent = file_get_contents($envPath);

            // Update or add ITEMS_PER_PAGE
            if (strpos($envContent, 'ITEMS_PER_PAGE=') !== false) {
                $envContent = preg_replace('/ITEMS_PER_PAGE=.*/', 'ITEMS_PER_PAGE=' . $validated['items_per_page'], $envContent);
            } else {
                $envContent .= "\nITEMS_PER_PAGE=" . $validated['items_per_page'];
            }

            // Update or add APP_NAME
            if (strpos($envContent, 'APP_NAME=') !== false) {
                $envContent = preg_replace('/APP_NAME=.*/', 'APP_NAME="' . $validated['app_name'] . '"', $envContent);
            } else {
                $envContent .= "\nAPP_NAME=\"" . $validated['app_name'] . "\"";
            }

            // Update or add APP_TIMEZONE
            if (strpos($envContent, 'APP_TIMEZONE=') !== false) {
                $envContent = preg_replace('/APP_TIMEZONE=.*/', 'APP_TIMEZONE=' . $validated['timezone'], $envContent);
            } else {
                $envContent .= "\nAPP_TIMEZONE=" . $validated['timezone'];
            }

            // Update or add APP_DATE_FORMAT
            if (isset($validated['date_format'])) {
                $dateFormat = $validated['date_format'];
                // Add quotes if value contains spaces
                if (strpos($dateFormat, ' ') !== false) {
                    $dateFormat = '"' . $dateFormat . '"';
                }
                if (strpos($envContent, 'APP_DATE_FORMAT=') !== false) {
                    $envContent = preg_replace('/APP_DATE_FORMAT=.*/', 'APP_DATE_FORMAT=' . $dateFormat, $envContent);
                } else {
                    $envContent .= "\nAPP_DATE_FORMAT=" . $dateFormat;
                }
            }

            file_put_contents($envPath, $envContent);
        }

        // Also update runtime configuration
        config([
            'app.name' => $validated['app_name'],
            'app.timezone' => $validated['timezone'],
            'settings.items_per_page' => $validated['items_per_page'],
            'settings.pagination' => [
                'delivery_requests' => $validated['items_per_page'],
                'trips' => $validated['items_per_page'],
                'drivers' => $validated['items_per_page'],
                'vehicles' => $validated['items_per_page'],
                'clients' => $validated['items_per_page'],
            ],
            'settings.date_format' => $validated['date_format'],
        ]);

        date_default_timezone_set($validated['timezone']);

        return redirect()->route('utils.settings')->with('success', 'Settings updated successfully. Application name, timezone, and date format will take effect shortly.');
    }

    public function clearCache()
    {

        return redirect()->route('utils.settings')->with('success', 'Cache cleared successfully.');
    }

    public function backupDatabase()
    {
        // Implement database backup logic
        return redirect()->route('utils.settings')->with('info', 'Database backup feature coming soon.');
    }

    public function exportAllData() {}
}
