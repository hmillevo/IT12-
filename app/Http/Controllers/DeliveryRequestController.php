<?php

namespace App\Http\Controllers;

use App\Models\DeliveryRequest;
use App\Models\Client;
use App\Services\CalendarificService;
use App\Services\DispatchService;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DeliveryRequestController extends Controller
{
    public function __construct(
        private readonly CalendarificService $calendarific,
        private readonly DispatchService $dispatchService
    ) {}

    public function index(Request $request)
    {
        $allowedStatuses = ['pending', 'verified', 'assigned'];
        $activeStatus = $request->query('status', 'all');

        $requestsQuery = DeliveryRequest::with('client');
        // Search functionality
        $search = $request->query('search');
        if ($search) {
            $requestsQuery->where(function ($query) use ($search) {
                $query->where('atw_reference', 'like', "%{$search}%")
                    ->orWhere('pickup_location', 'like', "%{$search}%")
                    ->orWhere('delivery_location', 'like', "%{$search}%")
                    ->orWhereHas('client', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }


        if ($activeStatus !== 'all') {
            if (in_array($activeStatus, $allowedStatuses, true)) {
                $requestsQuery->where('status', $activeStatus);
            } else {
                $activeStatus = 'all';
            }
        }

        if ($activeStatus === 'all') {
            $requestsQuery->orderByRaw("CASE WHEN status = 'pending' THEN 0 WHEN status = 'verified' THEN 1 WHEN status = 'assigned' THEN 2 ELSE 3 END");
        }

        $requests = $requestsQuery
            ->orderByDesc('preferred_schedule')
            ->orderByDesc('created_at')
            ->paginate(config('settings.pagination.delivery_requests', 10));

        $requests->withPath(route('requests.index'));
        if ($activeStatus !== 'all') {
            $requests->appends(['status' => $activeStatus]);
        }
        if ($search) {
            $requests->appends(['search' => $search]);
        }

        $statusCounts = DeliveryRequest::select('status')
            ->selectRaw('COUNT(*) as aggregate')
            ->whereIn('status', $allowedStatuses)
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $counts = [
            'all' => DeliveryRequest::count(),
        ];

        foreach ($allowedStatuses as $status) {
            $counts[$status] = $statusCounts[$status] ?? 0;
        }

        if ($request->ajax()) {
            $html = view('dispatch.requests.partials.table', compact('requests'))->render();

            return response()->json([
                'html' => $html,
                'status' => $activeStatus,
                'counts' => $counts,
            ]);
        }

        return view('dispatch.requests.index', compact('requests', 'activeStatus', 'counts'));
    }

    public function create()
    {
        $clients = Client::all();

        $holidays = collect();
        $calendarificError = null;

        if (!config('services.calendarific.key')) {
            $calendarificError = 'Set the Calendarific API key to load national holidays.';
        } else {
            try {
                $holidays = $this->calendarific
                    ->holidays('PH', (int) now()->year, 'national')
                    ->map(function (array $holiday) {
                        $dateIso = data_get($holiday, 'date.iso');
                        $date = $dateIso ? Carbon::parse($dateIso) : null;

                        return [
                            'name' => data_get($holiday, 'name'),
                            'date_iso' => $dateIso,
                            'date' => $date?->toDateString(),
                            'date_display' => $date?->format('M d, Y'),
                            'type' => data_get($holiday, 'type.0'),
                            'description' => data_get($holiday, 'description'),
                            'is_future' => $date?->isToday() || $date?->isFuture(),
                        ];
                    })
                    ->filter(fn($holiday) => $holiday['is_future'] ?? false)
                    ->sortBy('date_iso')
                    ->values()
                    ->take(5);
            } catch (RequestException $exception) {
                report($exception);
                $calendarificError = 'Unable to load holidays right now.';
            }
        }

        return view('dispatch.requests.create', compact('clients', 'holidays', 'calendarificError'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'contact_method' => 'required|in:mobile,email,group_chat',
            'atw_reference' => 'required|string',
            'pickup_location' => 'required|string',
            'delivery_location' => 'required|string',
            'container_size' => 'required|string',
            'container_type' => 'required|string',
            'preferred_schedule' => 'required|date|after_or_equal:today',
            'notes' => 'nullable|string',
            'auto_assign' => 'nullable|boolean', // New field for immediate assignment
        ]);

        $deliveryRequest = DeliveryRequest::create(array_filter($validated, function ($key) {
            return $key !== 'auto_assign';
        }, ARRAY_FILTER_USE_KEY));

        // Check if auto-assignment is requested
        if ($request->input('auto_assign', false)) {
            return $this->attemptAutoAssignment($deliveryRequest);
        }

        return redirect()
            ->route('requests.show', $deliveryRequest)
            ->with('success', 'Delivery request created successfully. You can verify and assign it when ready.');
    }

    public function show(DeliveryRequest $request)
    {
        $request->load(['client', 'trip.driver', 'trip.vehicle']);
        return view('dispatch.requests.show', compact('request'));
    }

    public function verify(DeliveryRequest $request)
    {
        $request->update([
            'atw_verified' => true,
            'status' => 'verified'
        ]);

        return redirect()
            ->back()
            ->with('success', 'ATW verified successfully. You can now assign this request to a trip.');
    }

    /**
     * Verify AND automatically assign the request to a trip
     */
    public function verifyAndAssign(DeliveryRequest $request)
    {
        // First verify
        $request->update([
            'atw_verified' => true,
            'status' => 'verified'
        ]);

        // Then attempt auto-assignment
        return $this->attemptAutoAssignment($request);
    }

    /**
     * Auto-assign delivery request to available resources
     */
    public function autoAssign(DeliveryRequest $request)
    {
        if (!in_array($request->status, ['pending', 'verified'])) {
            return redirect()
                ->back()
                ->with('error', 'This request cannot be auto-assigned. Status: ' . $request->status);
        }

        // Verify if not already verified
        if (!$request->atw_verified) {
            $request->update([
                'atw_verified' => true,
                'status' => 'verified'
            ]);
        }

        return $this->attemptAutoAssignment($request);
    }

    /**
     * Helper method to attempt automatic trip assignment
     */
    private function attemptAutoAssignment(DeliveryRequest $deliveryRequest)
    {
        try {
            // Get available resources
            $drivers = $this->dispatchService->getAvailableDrivers();
            $vehicles = $this->dispatchService->getAvailableVehicles();

            // Check if resources are available
            if ($drivers->isEmpty()) {
                return redirect()
                    ->route('requests.show', $deliveryRequest)
                    ->with('warning', 'Delivery request created, but no drivers are currently available. Please assign manually when resources become available.');
            }

            if ($vehicles->isEmpty()) {
                return redirect()
                    ->route('requests.show', $deliveryRequest)
                    ->with('warning', 'Delivery request created, but no vehicles are currently available. Please assign manually when resources become available.');
            }

            // Auto-select first available driver and vehicle
            $selectedDriver = $drivers->first();
            $selectedVehicle = $vehicles->first();

            // Create trip using dispatch service
            $trip = $this->dispatchService->assignTrip(
                $deliveryRequest->id,
                $selectedDriver->id,
                $selectedVehicle->id,
                $deliveryRequest->preferred_schedule,
                "Auto-assigned by system"
            );

            // Send notification
            $this->sendClientNotification($trip, 'scheduled', 'Your delivery has been scheduled.');

            return redirect()
                ->route('trips.show', $trip)
                ->with('success', "Delivery request assigned successfully! Driver: {$selectedDriver->name}, Vehicle: {$selectedVehicle->plate_number}");
        } catch (\Exception $e) {
            return redirect()
                ->route('requests.show', $deliveryRequest)
                ->with('error', 'Auto-assignment failed: ' . $e->getMessage() . '. Please assign manually.');
        }
    }

    /**
     * Helper to send client notification
     */
    private function sendClientNotification($trip, string $type, string $message)
    {
        $client = $trip->deliveryRequest->client;

        \App\Models\ClientNotification::create([
            'trip_id' => $trip->id,
            'client_id' => $client->id,
            'notification_type' => $type,
            'message' => $message,
            'method' => $trip->deliveryRequest->contact_method ?? 'sms',
            'sent' => false
        ]);

        \Log::info("Notification created for client: {$client->name}", [
            'trip_id' => $trip->id,
            'type' => $type,
            'message' => $message
        ]);
    }

    public function edit(DeliveryRequest $request)
    {
        $clients = \App\Models\Client::orderBy('name')->get();
        return view('dispatch.requests.edit', compact('request', 'clients'));
    }

    public function update(Request $validateRequest, DeliveryRequest $request)
    {
        $validated = $validateRequest->validate([
            'client_id' => 'required|exists:clients,id',
            'contact_method' => 'required|in:mobile,email,group_chat',
            'atw_reference' => 'required|string',
            'pickup_location' => 'required|string',
            'delivery_location' => 'required|string',
            'container_size' => 'required|string',
            'container_type' => 'required|string',
            'preferred_schedule' => 'required|date',
            'notes' => 'nullable|string'
        ]);

        $request->update($validated);

        return redirect()
            ->route('requests.show', $request)
            ->with('success', 'Request updated successfully.');
    }

    public function destroy(DeliveryRequest $request)
    {
        // Check if request has a trip assigned
        if ($request->trip && $request->trip->status !== 'cancelled') {
            return redirect()
                ->back()
                ->with('error', 'Cannot delete request with active trip.');
        }

        $request->delete();

        return redirect()
            ->route('requests.index')
            ->with('success', 'Request deleted successfully.');
    }

    public function cancel(DeliveryRequest $request)
    {
        $request->update(['status' => 'cancelled']);

        // Cancel associated trip if exists
        if ($request->trip) {
            $request->trip->update(['status' => 'cancelled']);

            // Free up resources
            \App\Models\Driver::where('id', $request->trip->driver_id)
                ->update(['status' => 'available']);
            \App\Models\Vehicle::where('id', $request->trip->vehicle_id)
                ->update(['status' => 'available']);
        }

        return redirect()
            ->back()
            ->with('success', 'Request cancelled successfully.');
    }

    public function filterByStatus($status)
    {
        $requests = DeliveryRequest::with('client')
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->paginate(config('settings.pagination.delivery_requests', 10));

        return view('dispatch.requests.index', compact('requests'));
    }

    public function search(Request $request)
    {
        $query = $request->input('q');

        $requests = DeliveryRequest::with('client')
            ->where('atw_reference', 'LIKE', "%{$query}%")
            ->orWhere('pickup_location', 'LIKE', "%{$query}%")
            ->orWhere('delivery_location', 'LIKE', "%{$query}%")
            ->orWhereHas('client', function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%");
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('dispatch.requests.search', compact('requests', 'query'));
    }

    public function exportExcel()
    {
        $requests = DeliveryRequest::with('client')->get();

        $filename = 'delivery_requests_' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($requests) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'ID',
                'Client',
                'ATW Reference',
                'Contact Method',
                'Pickup Location',
                'Delivery Location',
                'Container Size',
                'Container Type',
                'Preferred Schedule',
                'Status',
                'Created At'
            ]);

            foreach ($requests as $request) {
                fputcsv($file, [
                    $request->id,
                    $request->client->name,
                    $request->atw_reference,
                    $request->contact_method,
                    $request->pickup_location,
                    $request->delivery_location,
                    $request->container_size,
                    $request->container_type,
                    $request->preferred_schedule->format('Y-m-d H:i'),
                    $request->status,
                    $request->created_at->format('Y-m-d H:i'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf()
    {
        $requests = DeliveryRequest::with('client')->get();
        return redirect()->back()->with('info', 'PDF export feature requires DomPDF package.');
    }

    public function importForm()
    {
        return view('dispatch.requests.import');
    }

    public function importRequests(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt|max:2048'
        ]);

        return redirect()
            ->route('requests.index')
            ->with('info', 'Import feature coming soon.');
    }
}
