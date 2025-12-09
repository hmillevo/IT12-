<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\DeliveryRequest;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\Client;
use App\Models\ClientNotification;
use App\Services\DispatchService;
use Illuminate\Http\Request;

class TripController extends Controller
{
    protected $dispatchService;

    public function __construct(DispatchService $dispatchService)
    {
        $this->dispatchService = $dispatchService;
    }

    public function index(Request $request)
    {
        $allowedStatuses = ['scheduled', 'in-transit', 'completed', 'cancelled'];
        $activeStatus = $request->query('status', 'all');

        $tripsQuery = Trip::with(['deliveryRequest.client', 'driver', 'vehicle']);

        // Search functionality
        $search = $request->query('search');
        if ($search) {
            $tripsQuery->where(function ($query) use ($search) {
                $query->whereHas('deliveryRequest', function ($q) use ($search) {
                    $q->where('atw_reference', 'like', "%{$search}%")
                        ->orWhere('pickup_location', 'like', "%{$search}%")
                        ->orWhere('delivery_location', 'like', "%{$search}%")
                        ->orWhereHas('client', function ($clientQuery) use ($search) {
                            $clientQuery->where('name', 'like', "%{$search}%");
                        });
                })
                    ->orWhereHas('driver', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('vehicle', function ($q) use ($search) {
                        $q->where('plate_number', 'like', "%{$search}%");
                    });
            });
        }

        if ($activeStatus !== 'all') {
            if (in_array($activeStatus, $allowedStatuses, true)) {
                $tripsQuery->where('status', $activeStatus);
            } else {
                $activeStatus = 'all';
            }
        }

        if ($activeStatus === 'all') {
            $tripsQuery->orderByRaw("CASE WHEN status = 'scheduled' THEN 0 WHEN status = 'in-transit' THEN 1 WHEN status = 'completed' THEN 2 ELSE 3 END");
        }

        $trips = $tripsQuery
            ->orderByDesc('scheduled_time')
            ->orderByDesc('created_at')
            ->paginate(config('settings.pagination.trips', 10));

        $trips->withPath(route('trips.index'));
        if ($activeStatus !== 'all') {
            $trips->appends(['status' => $activeStatus]);
        }
        if ($search) {
            $trips->appends(['search' => $search]);
        }

        $statusCounts = Trip::select('status')
            ->selectRaw('COUNT(*) as aggregate')
            ->whereIn('status', $allowedStatuses)
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $counts = [
            'all' => Trip::count(),
        ];

        foreach ($allowedStatuses as $status) {
            $counts[$status] = $statusCounts[$status] ?? 0;
        }

        if ($request->ajax()) {
            $html = view('dispatch.trips.partials.table', compact('trips'))->render();

            return response()->json([
                'html' => $html,
                'status' => $activeStatus,
                'counts' => $counts,
            ]);
        }

        return view('dispatch.trips.index', compact('trips', 'activeStatus', 'counts'));
    }

    public function create(DeliveryRequest $deliveryRequest)
    {
        // Verify delivery request can be assigned
        if (!in_array($deliveryRequest->status, ['pending', 'verified'])) {
            return redirect()
                ->route('trips.show', $deliveryRequest->id)
                ->with('error', 'This request cannot be assigned. Status: ' . $deliveryRequest->status);
        }

        // Get TRULY available drivers and vehicles
        $drivers = $this->dispatchService->getAvailableDrivers();
        $vehicles = $this->dispatchService->getAvailableVehicles();

        // Check if resources are available
        if ($drivers->isEmpty()) {
            return redirect()
                ->route('requests.show', $deliveryRequest)
                ->with('error', 'No available drivers. Please make sure drivers are set to "available" status.');
        }

        if ($vehicles->isEmpty()) {
            return redirect()
                ->route('requests.show', $deliveryRequest)
                ->with('error', 'No available vehicles. Please make sure vehicles are set to "available" status.');
        }

        return view('dispatch.trips.create', compact('deliveryRequest', 'drivers', 'vehicles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'delivery_request_id' => 'required|exists:delivery_requests,id',
            'driver_id' => 'required|exists:drivers,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'scheduled_time' => 'required|date',
            'route_instructions' => 'nullable|string'
        ]);

        try {
            // Use dispatch service to create trip and update all statuses
            $trip = $this->dispatchService->assignTrip(
                $validated['delivery_request_id'],
                $validated['driver_id'],
                $validated['vehicle_id'],
                $validated['scheduled_time'],
                $validated['route_instructions'] ?? null
            );

            // Send assignment notification to client
            $this->sendClientNotification($trip, 'scheduled', 'Your delivery has been scheduled.');

            return redirect()
                ->route('trips.show', $trip)
                ->with('success', 'Trip assigned successfully. Driver has been notified.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function show(Trip $trip)
    {
        $trip->load(['deliveryRequest.client', 'driver', 'vehicle', 'updates']);
        return view('dispatch.trips.show', compact('trip'));
    }

    public function updateStatus(Request $request, Trip $trip)
    {
        $validated = $request->validate([
            'status' => 'required|in:scheduled,in-transit,completed,cancelled',
            'update_message' => 'nullable|string',
            'location' => 'nullable|string'
        ]);

        $oldStatus = $trip->status;

        try {
            $this->dispatchService->updateTripStatus(
                $trip,
                $validated['status'],
                $validated['update_message'] ?? null,
                $validated['location'] ?? null
            );

            // Send notifications based on status changes
            if ($oldStatus !== $validated['status']) {
                $this->handleStatusNotification($trip, $validated['status']);
            }

            return redirect()
                ->back()
                ->with('success', 'Trip status updated successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    public function addUpdate(Request $request, Trip $trip)
    {
        $validated = $request->validate([
            'update_type' => 'required|in:status,location,delay,incident,completed,arrived',
            'message' => 'required|string',
            'location' => 'nullable|string'
        ]);

        $trip->updates()->create([
            'update_type' => $validated['update_type'],
            'message' => $validated['message'],
            'location' => $validated['location'] ?? null,
            'reported_by' => 'dispatcher'
        ]);

        // Send notification for important updates
        if (in_array($validated['update_type'], ['delay', 'incident', 'completed', 'arrived'])) {
            $this->sendClientNotification($trip, $validated['update_type'], $validated['message']);
        }

        return redirect()->back()->with('success', 'Update added successfully.');
    }

    public function edit(Trip $trip)
    {
        // For editing, show available resources PLUS currently assigned ones
        $drivers = $this->dispatchService->getAvailableDrivers();
        $vehicles = $this->dispatchService->getAvailableVehicles();

        // Add current driver if not in available list
        if (!$drivers->contains('id', $trip->driver_id)) {
            $drivers->push($trip->driver);
        }

        // Add current vehicle if not in available list
        if (!$vehicles->contains('id', $trip->vehicle_id)) {
            $vehicles->push($trip->vehicle);
        }

        return view('dispatch.trips.edit', compact('trip', 'drivers', 'vehicles'));
    }

    public function update(Request $validateRequest, Trip $trip)
    {
        $validated = $validateRequest->validate([
            'driver_id' => 'sometimes|exists:drivers,id',
            'vehicle_id' => 'sometimes|exists:vehicles,id',
            'scheduled_time' => 'sometimes|date',
            'route_instructions' => 'nullable|string'
        ]);

        try {
            // If driver or vehicle changed, use reassignment logic
            if (isset($validated['driver_id']) || isset($validated['vehicle_id'])) {
                $this->dispatchService->reassignTrip(
                    $trip,
                    $validated['driver_id'] ?? null,
                    $validated['vehicle_id'] ?? null
                );
            }

            // Update other fields
            $trip->update(array_filter($validated, function ($key) {
                return in_array($key, ['scheduled_time', 'route_instructions']);
            }, ARRAY_FILTER_USE_KEY));

            return redirect()
                ->route('trips.show', $trip)
                ->with('success', 'Trip updated successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function destroy(Trip $trip)
    {
        if ($trip->status === 'in-transit') {
            return redirect()
                ->back()
                ->with('error', 'Cannot delete trip that is in transit.');
        }

        // Free up resources before deleting
        if ($trip->status !== 'completed' && $trip->status !== 'cancelled') {
            Driver::where('id', $trip->driver_id)->update(['status' => 'available']);
            Vehicle::where('id', $trip->vehicle_id)->update(['status' => 'available']);

            // Reset delivery request status
            DeliveryRequest::where('id', $trip->delivery_request_id)
                ->update(['status' => 'verified']);
        }

        $trip->delete();

        return redirect()
            ->route('trips.index')
            ->with('success', 'Trip deleted successfully.');
    }

    public function startTrip(Trip $trip)
    {
        try {
            $this->dispatchService->updateTripStatus(
                $trip,
                'in-transit',
                'Trip started by dispatcher'
            );

            // Notify client that trip has started
            $this->sendClientNotification($trip, 'in-transit', 'Your delivery is now in transit.');

            return redirect()
                ->back()
                ->with('success', 'Trip started successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    public function completeTrip(Request $request, Trip $trip)
    {
        try {
            $updateMessage = $request->input('update_message');
            $message = $updateMessage ?: 'Trip completed successfully';

            $this->dispatchService->updateTripStatus(
                $trip,
                'completed',
                $message
            );

            // Notify client of completion
            $this->sendClientNotification($trip, 'completed', 'Your delivery has been completed successfully.');

            // Redirect to trips index, showing completed trips or the referrer page
            $redirectUrl = $request->header('Referer') ?: route('trips.index', ['status' => 'completed']);

            return redirect($redirectUrl)
                ->with('success', 'Trip completed successfully.');
        } catch (\Exception $e) {
            $redirectUrl = $request->header('Referer') ?: route('trips.index');

            return redirect($redirectUrl)
                ->with('error', $e->getMessage());
        }
    }

    public function markArrived(Trip $trip)
    {
        $trip->updates()->create([
            'update_type' => 'arrived',
            'message' => 'Container has arrived at destination',
            'location' => $trip->deliveryRequest->delivery_location,
            'reported_by' => 'dispatcher'
        ]);

        // Send arrival notification
        $this->sendClientNotification(
            $trip,
            'arrived',
            "Your container has arrived at {$trip->deliveryRequest->delivery_location}. ATW Reference: {$trip->deliveryRequest->atw_reference}"
        );

        return redirect()
            ->back()
            ->with('success', 'Arrival notification sent to client.');
    }

    public function cancelTrip(Trip $trip)
    {
        try {
            $this->dispatchService->updateTripStatus(
                $trip,
                'cancelled',
                'Trip cancelled by dispatcher'
            );

            // Notify client of cancellation
            $this->sendClientNotification($trip, 'cancelled', 'Your delivery has been cancelled.');

            return redirect()
                ->back()
                ->with('success', 'Trip cancelled.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    // Helper method to send client notifications
    private function sendClientNotification(Trip $trip, string $type, string $message)
    {
        $client = $trip->deliveryRequest->client;

        ClientNotification::create([
            'trip_id' => $trip->id,
            'client_id' => $client->id,
            'notification_type' => $type,
            'message' => $message,
            'method' => $trip->deliveryRequest->contact_method ?? 'sms',
            'sent' => false
        ]);

        // Log the notification
        \Log::info("Notification created for client: {$client->name}", [
            'trip_id' => $trip->id,
            'type' => $type,
            'message' => $message
        ]);
    }

    // Helper method to handle status-based notifications
    private function handleStatusNotification(Trip $trip, string $newStatus)
    {
        $messages = [
            'scheduled' => 'Your delivery has been scheduled.',
            'in-transit' => 'Your delivery is now in transit.',
            'completed' => 'Your delivery has been completed successfully.',
            'cancelled' => 'Your delivery has been cancelled.'
        ];

        if (isset($messages[$newStatus])) {
            $this->sendClientNotification($trip, $newStatus, $messages[$newStatus]);
        }
    }

    public function getUpdates(Trip $trip)
    {
        $updates = $trip->updates()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'updates' => $updates
        ]);
    }

    public function getCurrentLocation(Trip $trip)
    {
        $locationUpdate = $trip->updates()
            ->where('update_type', 'location')
            ->latest()
            ->first();

        return response()->json([
            'success' => true,
            'location' => $locationUpdate ? $locationUpdate->location : null
        ]);
    }

    public function todayTrips()
    {
        $trips = Trip::with(['deliveryRequest.client', 'driver', 'vehicle'])
            ->whereDate('scheduled_time', \Carbon\Carbon::today())
            ->orderBy('scheduled_time')
            ->get();

        $activeTrips = Trip::with(['deliveryRequest.client', 'driver', 'vehicle'])
            ->whereIn('status', ['scheduled', 'in-transit'])
            ->orderBy('scheduled_time')
            ->limit(5)
            ->get();

        return view('dispatch.trips.today', compact('trips'));
    }

    public function upcomingTrips()
    {
        $trips = Trip::with(['deliveryRequest.client', 'driver', 'vehicle'])
            ->where('scheduled_time', '>', now())
            ->where('status', 'scheduled')
            ->orderBy('scheduled_time')
            ->get();

        return view('dispatch.trips.upcoming', compact('trips'));
    }

    public function completedTrips()
    {
        $trips = Trip::with(['deliveryRequest.client', 'driver', 'vehicle'])
            ->where('status', 'completed')
            ->orderBy('actual_end_time', 'desc')
            ->paginate(20);

        return view('dispatch.trips.completed', compact('trips'));
    }

    public function printTripSheet(Trip $trip)
    {
        $trip->load(['deliveryRequest.client', 'driver', 'vehicle', 'updates']);
        return view('dispatch.trips.print', compact('trip'));
    }

    public function trackTrip(Trip $trip)
    {
        $trip->load(['deliveryRequest.client', 'driver', 'vehicle', 'updates']);
        return view('dispatch.trips.track', compact('trip'));
    }

    public function getTripStatus(Trip $trip)
    {
        return response()->json([
            'success' => true,
            'status' => $trip->status,
            'last_update' => $trip->updates()->latest()->first()
        ]);
    }

    // Additional Filter Methods
    public function filterByStatus($status)
    {
        $trips = Trip::with(['deliveryRequest.client', 'driver', 'vehicle'])
            ->where('status', $status)
            ->orderBy('scheduled_time', 'desc')
            ->paginate(config('settings.pagination.trips', 10));

        return view('dispatch.trips.index', compact('trips'));
    }

    public function filterByDriver(Driver $driver)
    {
        $trips = Trip::with(['deliveryRequest.client', 'driver', 'vehicle'])
            ->where('driver_id', $driver->id)
            ->orderBy('scheduled_time', 'desc')
            ->paginate(config('settings.pagination.trips', 10));

        return view('dispatch.trips.index', compact('trips', 'driver'));
    }

    public function filterByVehicle(Vehicle $vehicle)
    {
        $trips = Trip::with(['deliveryRequest.client', 'driver', 'vehicle'])
            ->where('vehicle_id', $vehicle->id)
            ->orderBy('scheduled_time', 'desc')
            ->paginate(config('settings.pagination.trips', 10));

        return view('dispatch.trips.index', compact('trips', 'vehicle'));
    }

    public function filterByClient(Client $client)
    {
        $trips = Trip::with(['deliveryRequest.client', 'driver', 'vehicle'])
            ->whereHas('deliveryRequest', function ($query) use ($client) {
                $query->where('client_id', $client->id);
            })
            ->orderBy('scheduled_time', 'desc')
            ->paginate(config('settings.pagination.trips', 10));

        return view('dispatch.trips.index', compact('trips', 'client'));
    }

    public function activeTrips()
    {
        $trips = Trip::with(['deliveryRequest.client', 'driver', 'vehicle'])
            ->whereIn('status', ['scheduled', 'in-transit'])
            ->orderBy('scheduled_time')
            ->get();

        return view('dispatch.trips.active', compact('trips'));
    }

    public function search(Request $request)
    {
        $query = $request->input('q');

        $trips = Trip::with(['deliveryRequest.client', 'driver', 'vehicle'])
            ->whereHas('deliveryRequest', function ($q) use ($query) {
                $q->where('atw_reference', 'LIKE', "%{$query}%")
                    ->orWhereHas('client', function ($q2) use ($query) {
                        $q2->where('name', 'LIKE', "%{$query}%");
                    });
            })
            ->orWhereHas('driver', function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%");
            })
            ->orWhereHas('vehicle', function ($q) use ($query) {
                $q->where('plate_number', 'LIKE', "%{$query}%");
            })
            ->orderBy('scheduled_time', 'desc')
            ->paginate(20);

        return view('dispatch.trips.search', compact('trips', 'query'));
    }


    public function getActiveTrips()
    {
        $trips = Trip::with(['deliveryRequest.client', 'driver', 'vehicle'])
            ->whereIn('status', ['scheduled', 'in-transit'])
            ->orderBy('scheduled_time')
            ->get();

        return response()->json([
            'success' => true,
            'trips' => $trips
        ]);
    }

    public function exportExcel()
    {
        $trips = Trip::with(['deliveryRequest.client', 'driver', 'vehicle'])->get();

        $filename = 'trips_' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($trips) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Trip ID',
                'Client',
                'ATW Reference',
                'Driver',
                'Vehicle',
                'Pickup',
                'Delivery',
                'Scheduled Time',
                'Status',
                'Start Time',
                'End Time'
            ]);

            foreach ($trips as $trip) {
                fputcsv($file, [
                    $trip->id,
                    $trip->deliveryRequest->client->name,
                    $trip->deliveryRequest->atw_reference,
                    $trip->driver->name,
                    $trip->vehicle->plate_number,
                    $trip->deliveryRequest->pickup_location,
                    $trip->deliveryRequest->delivery_location,
                    $trip->scheduled_time->format('Y-m-d H:i'),
                    $trip->status,
                    $trip->actual_start_time ? $trip->actual_start_time->format('Y-m-d H:i') : '',
                    $trip->actual_end_time ? $trip->actual_end_time->format('Y-m-d H:i') : '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf()
    {
        return redirect()->back()->with('info', 'PDF export feature requires DomPDF package.');
    }
}
