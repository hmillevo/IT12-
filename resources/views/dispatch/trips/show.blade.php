@extends('layouts.app')

@section('title', 'Trip Details')

@section('content')
<div class="container mx-auto px-4 max-w-6xl">
    <div class="mb-6">
        <a href="{{ route('trips.index') }}" class="inline-flex items-center gap-2 px-4 py-2 font-medium text-white bg-[#2563EB] rounded-full hover:bg-blue-700 transition-colors">
            <i class="fas fa-arrow-left"></i>
            <span>Back to Trips</span>
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Details -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">Trip #{{ $trip->id }}</h1>
                        <p class="text-gray-600 text-sm mt-1">Created {{ $trip->created_at->diffForHumans() }}</p>
                    </div>
                    <span class="px-4 py-2 rounded-full text-sm font-semibold
                        {{ $trip->status === 'scheduled' ? 'bg-gray-100 text-gray-800' : '' }}
                        {{ $trip->status === 'in-transit' ? 'bg-blue-100 text-blue-800' : '' }}
                        {{ $trip->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                        {{ $trip->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                        {{ ucfirst(str_replace('-', ' ', $trip->status)) }}
                    </span>
                </div>

                <!-- Client Information -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">
                        <i class="fas fa-user text-blue-600"></i> Client Information
                    </h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Client Name</p>
                            <p class="font-semibold">{{ $trip->deliveryRequest->client->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">ATW Reference</p>
                            <p class="font-semibold font-mono text-purple-600">{{ $trip->deliveryRequest->atw_reference }}</p>
                        </div>
                    </div>
                </div>

                <!-- Assigned Resources -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">
                        <i class="fas fa-users text-blue-600"></i> Assigned Resources
                    </h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-4 bg-blue-50 rounded-lg">
                            <p class="text-sm text-gray-600 mb-2">
                                <i class="fas fa-user text-blue-500"></i> Driver
                            </p>
                            <p class="font-semibold">{{ $trip->driver->name }}</p>
                            <p class="text-xs text-gray-500 mt-1">
                                <i class="fas fa-phone"></i> {{ $trip->driver->mobile }}
                            </p>
                            <p class="text-xs text-gray-500">
                                <i class="fas fa-id-card"></i> {{ $trip->driver->license_number }}
                            </p>
                        </div>
                        <div class="p-4 bg-purple-50 rounded-lg">
                            <p class="text-sm text-gray-600 mb-2">
                                <i class="fas fa-truck text-purple-500"></i> Vehicle
                            </p>
                            <p class="font-semibold">{{ $trip->vehicle->plate_number }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $trip->vehicle->vehicle_type }}</p>
                            <p class="text-xs text-gray-500">{{ $trip->vehicle->trailer_type }}</p>
                        </div>
                    </div>
                </div>

                <!-- Delivery Details -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">
                        <i class="fas fa-box text-blue-600"></i> Delivery Details
                    </h3>
                    <div class="space-y-3">
                        <div class="flex items-start">
                            <div class="w-32 text-sm text-gray-600">Container:</div>
                            <div class="flex-1 font-semibold">
                                {{ $trip->deliveryRequest->container_size }} - {{ ucfirst($trip->deliveryRequest->container_type) }}
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="w-32 text-sm text-gray-600">Pickup:</div>
                            <div class="flex-1">
                                <i class="fas fa-map-marker-alt text-green-500"></i>
                                {{ $trip->deliveryRequest->pickup_location }}
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="w-32 text-sm text-gray-600">Delivery:</div>
                            <div class="flex-1">
                                <i class="fas fa-flag-checkered text-red-500"></i>
                                {{ $trip->deliveryRequest->delivery_location }}
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="w-32 text-sm text-gray-600">Scheduled:</div>
                            <div class="flex-1 font-semibold">
                                <i class="far fa-calendar-alt"></i>
                                {{ $trip->scheduled_time->format(config('settings.date_format', 'M d, Y')) }} at {{ $trip->scheduled_time->format('h:i A') }}
                            </div>
                        </div>
                        @if($trip->route_instructions)
                        <div class="flex items-start">
                            <div class="w-32 text-sm text-gray-600">Route:</div>
                            <div class="flex-1">{{ $trip->route_instructions }}</div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Timing Information -->
                @if($trip->actual_start_time || $trip->actual_end_time)
                <div class="border-t pt-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">
                        <i class="fas fa-clock text-blue-600"></i> Timing
                    </h3>
                    <div class="space-y-2">
                        @if($trip->actual_start_time)
                        <div class="flex items-center text-sm">
                            <i class="fas fa-play text-green-500 mr-2"></i>
                            <span class="text-gray-600 mr-2">Started:</span>
                            <span class="font-semibold">{{ $trip->actual_start_time->format(config('settings.date_format', 'M d, Y') . ' h:i A') }}</span>
                        </div>
                        @endif
                        @if($trip->actual_end_time)
                        <div class="flex items-center text-sm">
                            <i class="fas fa-check-circle text-purple-500 mr-2"></i>
                            <span class="text-gray-600 mr-2">Completed:</span>
                            <span class="font-semibold">{{ $trip->actual_end_time->format(config('settings.date_format', 'M d, Y') . ' h:i A') }}</span>
                        </div>
                        @if($trip->actual_start_time && $trip->actual_end_time)
                        <div class="flex items-center text-sm">
                            <i class="fas fa-hourglass-half text-blue-500 mr-2"></i>
                            <span class="text-gray-600 mr-2">Duration:</span>
                            <span class="font-semibold">{{ $trip->actual_start_time->diffForHumans($trip->actual_end_time, true) }}</span>
                        </div>
                        @endif
                        @endif
                    </div>
                </div>
                @endif

                <!-- Trip Updates -->
                @if($trip->updates->count() > 0)
                <div class="border-t pt-6 mt-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">
                        <i class="fas fa-history text-blue-600"></i> Trip Updates
                    </h3>
                    <div class="space-y-3">
                        @foreach($trip->updates as $update)
                        <div class="p-3 bg-gray-50 rounded-lg border-l-4 border-blue-500">
                            <div class="flex justify-between items-start">
                                <p class="text-sm">{{ $update->message }}</p>
                                <span class="text-xs text-gray-500 whitespace-nowrap ml-2">
                                    {{ $update->created_at->format(config('settings.date_format', 'M d, Y') . ' h:i A') }}
                                </span>
                            </div>
                            @if($update->location)
                            <p class="text-xs text-gray-600 mt-1">
                                <i class="fas fa-map-marker-alt"></i> {{ $update->location }}
                            </p>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Actions Sidebar -->
        <div>
            <div class="bg-white rounded-lg shadow-md p-6 sticky top-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Actions</h3>

                @if($trip->status === 'scheduled')
                <form method="POST" action="{{ route('trips.start', $trip) }}" class="mb-3">
                    @csrf
                    <button type="submit"
                        class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
                        onclick="return confirm('Start this trip?')">
                        <i class="fas fa-play"></i> Start Trip
                    </button>
                </form>
                @endif

                @if($trip->status === 'in-transit')
                <button type="button" id="open-complete-modal"
                    class="w-full px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors mb-3">
                    <i class="fas fa-check-circle"></i> Complete Trip
                </button>
                @endif

                <a href="{{ route('requests.show', $trip->deliveryRequest) }}"
                    class="block w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-center mb-3 transition-colors">
                    <i class="fas fa-file-alt"></i> View Request
                </a>

                <!-- Timeline -->
                <div class="mt-6 pt-6 border-t">
                    <h4 class="font-semibold text-gray-800 mb-3">Timeline</h4>
                    <div class="space-y-3">
                        <div class="flex items-start">
                            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-calendar-plus text-blue-600 text-xs"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium">Trip Created</p>
                                <p class="text-xs text-gray-500">{{ $trip->created_at->format(config('settings.date_format', 'M d, Y') . ' h:i A') }}</p>
                            </div>
                        </div>

                        @if($trip->actual_start_time)
                        <div class="flex items-start">
                            <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-play text-green-600 text-xs"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium">Trip Started</p>
                                <p class="text-xs text-gray-500">{{ $trip->actual_start_time->format(config('settings.date_format', 'M d, Y') . ' h:i A') }}</p>
                            </div>
                        </div>
                        @endif

                        @if($trip->actual_end_time)
                        <div class="flex items-start">
                            <div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-check-circle text-purple-600 text-xs"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium">Trip Completed</p>
                                <p class="text-xs text-gray-500">{{ $trip->actual_end_time->format(config('settings.date_format', 'M d, Y') . ' h:i A') }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Complete Trip Modal -->
@if($trip->status === 'in-transit')
<div id="complete-trip-modal" class="fixed inset-0 z-50 hidden bg-black/40 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6">
        <div class="mb-4">
            <h3 class="text-xl font-semibold text-gray-800 mb-2">Complete Trip</h3>
            <p class="text-sm text-gray-600">Are you sure you want to complete this trip?</p>
        </div>

        <form method="POST" action="{{ route('trips.complete', $trip) }}" id="complete-trip-form">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Completion Notes (optional)
                </label>
                <textarea
                    name="update_message"
                    rows="3"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    placeholder="Add any notes about the delivery completion..."></textarea>
            </div>

            <div class="flex gap-3">
                <button type="button" id="cancel-complete"
                    class="flex-1 px-4 py-2 rounded-full bg-red-500 text-white font-semibold hover:bg-red-600 transition-colors">
                    Cancel
                </button>
                <button type="submit"
                    class="flex-1 px-4 py-2 rounded-full bg-[#2563EB] text-white font-semibold hover:bg-blue-700 transition-colors">
                    Confirm
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const initCompleteModal = () => {
        const openButton = document.getElementById('open-complete-modal');
        const modal = document.getElementById('complete-trip-modal');
        const cancelButton = document.getElementById('cancel-complete');

        if (!openButton || !modal || !cancelButton) return;

        const showModal = () => {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        };

        const hideModal = () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        };

        openButton.addEventListener('click', showModal);
        cancelButton.addEventListener('click', hideModal);

        modal.addEventListener('click', (event) => {
            if (event.target === modal) hideModal();
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                hideModal();
            }
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCompleteModal);
    } else {
        initCompleteModal();
    }
</script>
@endif
@endsection