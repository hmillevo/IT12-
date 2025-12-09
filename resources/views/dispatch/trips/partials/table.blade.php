<div>
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase border-l-2 border-[#1E40AF]">ATW Reference</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase border-l-2 border-[#1E40AF]">Driver</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase border-l-2 border-[#1E40AF]">Vehicle</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase border-l-2 border-[#1E40AF]">Route</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase border-l-2 border-[#1E40AF]">Schedule</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase border-l-2 border-[#1E40AF]">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase border-l-2 border-[#1E40AF]">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($trips as $trip)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ $trip->deliveryRequest->client->name }}</div>
                    <div class="text-xs text-gray-500">
                        {{ $trip->deliveryRequest->container_size }}
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-mono text-purple-600">{{ $trip->deliveryRequest->atw_reference }}</div>
                    <div class="text-xs text-gray-500">Trip #{{ $trip->id }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ $trip->driver->name }}</div>
                    <div class="text-xs text-gray-500">
                        <i class="fas fa-phone"></i> {{ $trip->driver->mobile }}
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ $trip->vehicle->plate_number }}</div>
                    <div class="text-xs text-gray-500">{{ $trip->vehicle->vehicle_type }}</div>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">
                    <div class="space-y-1">
                        <div>
                            <i class="fas fa-map-marker-alt text-green-500"></i>
                            {{ Str::limit($trip->deliveryRequest->pickup_location, 25) }}
                        </div>
                        <div>
                            <i class="fas fa-flag-checkered text-red-500"></i>
                            {{ Str::limit($trip->deliveryRequest->delivery_location, 25) }}
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                    <div>{{ $trip->scheduled_time->format(config('settings.date_format', 'M d, Y')) }}</div>
                    <div class="text-xs">{{ $trip->scheduled_time->format('h:i A') }}</div>
                    @if($trip->actual_start_time)
                    <div class="text-xs text-green-600 mt-1">
                        <i class="fas fa-play"></i> Started
                    </div>
                    @endif
                    @if($trip->actual_end_time)
                    <div class="text-xs text-purple-600 mt-1">
                        <i class="fas fa-check"></i> Completed
                    </div>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-3 py-1 rounded-full text-xs font-semibold
                        {{ $trip->status === 'scheduled' ? 'bg-gray-100 text-gray-800' : '' }}
                        {{ $trip->status === 'in-transit' ? 'bg-blue-100 text-blue-800' : '' }}
                        {{ $trip->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                        {{ $trip->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                        {{ ucfirst(str_replace('-', ' ', $trip->status)) }}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <div class="flex space-x-2">
                        <button type="button"
                            class="text-blue-600 hover:text-blue-800 view-trip-btn"
                            title="View"
                            data-trip-id="{{ $trip->id }}"
                            data-client-name="{{ $trip->deliveryRequest->client->name }}"
                            data-atw-reference="{{ $trip->deliveryRequest->atw_reference }}"
                            data-driver-name="{{ $trip->driver->name }}"
                            data-driver-mobile="{{ $trip->driver->mobile }}"
                            data-vehicle-plate="{{ $trip->vehicle->plate_number }}"
                            data-vehicle-type="{{ $trip->vehicle->vehicle_type }}"
                            data-container-size="{{ $trip->deliveryRequest->container_size }}"
                            data-container-type="{{ ucfirst($trip->deliveryRequest->container_type) }}"
                            data-pickup="{{ $trip->deliveryRequest->pickup_location }}"
                            data-delivery="{{ $trip->deliveryRequest->delivery_location }}"
                            data-scheduled-date="{{ $trip->scheduled_time->format(config('settings.date_format', 'M d, Y')) }}"
                            data-scheduled-time="{{ $trip->scheduled_time->format('h:i A') }}"
                            data-route-instructions="{{ $trip->route_instructions ?? '' }}"
                            data-status="{{ $trip->status }}"
                            data-created-at="{{ $trip->created_at->format(config('settings.date_format', 'M d, Y') . ' h:i A') }}"
                            data-start-time="{{ $trip->actual_start_time ? $trip->actual_start_time->format(config('settings.date_format', 'M d, Y') . ' h:i A') : '' }}"
                            data-complete-time="{{ $trip->actual_end_time ? $trip->actual_end_time->format(config('settings.date_format', 'M d, Y') . ' h:i A') : '' }}"
                            data-start-url="{{ $trip->status === 'scheduled' ? route('trips.start', $trip) : '' }}"
                            data-complete-url="{{ $trip->status === 'in-transit' ? route('trips.complete', $trip) : '' }}"
                            data-view-url="{{ route('trips.show', $trip) }}">
                            <i class="fas fa-eye"></i>
                        </button>
                        @if($trip->status === 'scheduled')
                        <form method="POST" action="{{ route('trips.start', $trip) }}" class="inline">
                            @csrf
                            <button type="button" class="text-green-600 hover:text-green-800 start-trip-btn" title="Start">
                                <i class="fas fa-play"></i>
                            </button>
                        </form>
                        @endif
                        @if($trip->status === 'in-transit')
                        <form method="POST" action="{{ route('trips.complete', $trip) }}" class="inline">
                            @csrf
                            <button type="button" class="text-purple-600 hover:text-purple-800 complete-trip-btn" title="Complete">
                                <i class="fas fa-check-circle"></i>
                            </button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                    No trips found
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($trips->hasPages())
<div class="px-6 py-4 bg-white border-t border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <span class="text-sm text-gray-600">
        Showing {{ $trips->firstItem() }} to {{ $trips->lastItem() }} of {{ $trips->total() }} trips
    </span>
    <div class="flex gap-3">
        @if($trips->onFirstPage())
        <span class="px-4 py-2 rounded-md bg-gray-100 text-gray-400 cursor-not-allowed">Previous</span>
        @else
        <a href="{{ $trips->previousPageUrl() }}" data-pagination="trips"
            class="px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
            Previous
        </a>
        @endif

        @if($trips->hasMorePages())
        <a href="{{ $trips->nextPageUrl() }}" data-pagination="trips"
            class="px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
            Next
        </a>
        @else
        <span class="px-4 py-2 rounded-md bg-gray-100 text-gray-400 cursor-not-allowed">Next</span>
        @endif
    </div>
</div>
@endif