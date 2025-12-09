<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        
        $query = Client::withCount(['deliveryRequests', 'deliveryRequests as completed_requests' => function ($query) {
            $query->where('status', 'completed');
        }]);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%");
            });
        }

        $clients = $query->orderBy('name')
            ->paginate(config('settings.pagination.clients', 10))
            ->withPath(route('clients.index'));

        $stats = [
            'total_clients' => Client::count(),
            'active_clients' => Client::whereHas('deliveryRequests')->count(),
        ];

        if ($request->ajax()) {
            $html = view('dispatch.clients.partials.table', compact('clients'))->render();
            return response()->json(['html' => $html]);
        }

        return view('dispatch.clients.index', compact('clients', 'stats'));
    }

    public function create()
    {
        return view('dispatch.clients.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:clients,email',
            'mobile' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
        ]);

        $client = Client::create($validated);

        return redirect()
            ->route('clients.show', $client)
            ->with('success', 'Client added successfully.');
    }

    public function show(Client $client)
    {
        $client->load('deliveryRequests');

        $recentRequests = $this->buildRecentRequestsPaginator($client);

        $stats = [
            'total_requests' => $client->deliveryRequests()->count(),
            'pending_requests' => $client->deliveryRequests()->where('status', 'pending')->count(),
            'completed_requests' => $client->deliveryRequests()->where('status', 'completed')->count(),
            'active_trips' => $client->deliveryRequests()
                ->whereHas('trip', function ($query) {
                    $query->where('status', 'in-transit');
                })->count(),
        ];

        return view('dispatch.clients.show', compact('client', 'stats', 'recentRequests'));
    }

    public function recentRequests(Request $request, Client $client)
    {
        $recentRequests = $this->buildRecentRequestsPaginator($client);

        if ($request->ajax()) {
            $html = view('dispatch.clients.partials.recent-requests', compact('recentRequests'))->render();

            return response()->json(['html' => $html]);
        }

        return redirect()->route('clients.show', $client);
    }

    protected function buildRecentRequestsPaginator(Client $client)
    {
        return $client->deliveryRequests()
            ->with('trip')
            ->orderBy('created_at', 'desc')
            ->simplePaginate(3, ['*'], 'client_recent_page')
            ->withPath(route('clients.recent-requests', $client));
    }

    public function edit(Client $client)
    {
        return view('dispatch.clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:clients,email,' . $client->id,
            'mobile' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
        ]);

        $client->update($validated);

        return redirect()
            ->route('clients.show', $client)
            ->with('success', 'Client updated successfully.');
    }

    public function destroy(Client $client)
    {
        // Check if client has any requests
        if ($client->deliveryRequests()->exists()) {
            return redirect()
                ->back()
                ->with('error', 'Cannot delete client with existing delivery requests.');
        }

        $client->delete();

        return redirect()
            ->route('clients.index')
            ->with('success', 'Client deleted successfully.');
    }

    public function clientRequests(Client $client)
    {
        $requests = $client->deliveryRequests()
            ->with('trip')
            ->orderBy('created_at', 'desc')
            ->paginate(config('settings.pagination.delivery_requests', 10));

        return view('dispatch.clients.requests', compact('client', 'requests'));
    }

    public function clientTrips(Client $client)
    {
        $trips = $client->deliveryRequests()
            ->with(['trip.driver', 'trip.vehicle'])
            ->whereHas('trip')
            ->orderBy('created_at', 'desc')
            ->paginate(config('settings.pagination.trips', 10));

        return view('dispatch.clients.trips', compact('client', 'trips'));
    }

    public function activity(Client $client)
    {
        $recentActivity = $client->deliveryRequests()
            ->with(['trip.driver', 'trip.vehicle', 'trip.updates'])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return view('dispatch.clients.activity', compact('client', 'recentActivity'));
    }

    public function search(Request $request)
    {
        $query = $request->input('q');

        $clients = Client::where('name', 'LIKE', "%{$query}%")
            ->orWhere('email', 'LIKE', "%{$query}%")
            ->orWhere('mobile', 'LIKE', "%{$query}%")
            ->orWhere('company', 'LIKE', "%{$query}%")
            ->withCount('deliveryRequests')
            ->get();

        return view('dispatch.clients.search', compact('clients', 'query'));
    }

    public function exportExcel()
    {
        $clients = Client::withCount('deliveryRequests')->get();

        // You can use Laravel Excel package here
        // For now, return a simple CSV
        $filename = 'clients_' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($clients) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Name', 'Email', 'Mobile', 'Company', 'Total Requests']);

            foreach ($clients as $client) {
                fputcsv($file, [
                    $client->name,
                    $client->email,
                    $client->mobile,
                    $client->company,
                    $client->delivery_requests_count,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
