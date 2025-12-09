<div class="overflow-x-auto">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase border-l-2 border-[#1E40AF]">ATW Reference</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase border-l-2 border-[#1E40AF]">Container</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase border-l-2 border-[#1E40AF]">Pickup Location</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase border-l-2 border-[#1E40AF]">Delivery Location</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase border-l-2 border-[#1E40AF]">Schedule</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase border-l-2 border-[#1E40AF]">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase border-l-2 border-[#1E40AF]">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($requests as $request)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ $request->client->name }}</div>
                    <div class="text-xs text-gray-500">
                        <i class="fas fa-{{ $request->contact_method === 'mobile' ? 'phone' : ($request->contact_method === 'email' ? 'envelope' : 'comments') }}"></i>
                        {{ ucfirst($request->contact_method) }}
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ $request->atw_reference }}</div>
                    @if($request->atw_verified)
                    <span class="text-xs text-green-600"><i class="fas fa-check-circle"></i> Verified</span>
                    @else
                    <span class="text-xs text-yellow-600"><i class="fas fa-exclamation-circle"></i> Pending</span>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                    <div>{{ $request->container_size }}</div>
                    <div class="text-xs text-gray-500">{{ $request->container_type }}</div>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">
                    <i class="fas fa-map-marker-alt text-green-500"></i>
                    {{ Str::limit($request->pickup_location, 30) }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">
                    <i class="fas fa-flag-checkered text-red-500"></i>
                    {{ Str::limit($request->delivery_location, 30) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                    {{ $request->preferred_schedule->format(config('settings.date_format', 'M d, Y')) }}<br>
                    <span class="text-xs">{{ $request->preferred_schedule->format('h:i A') }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-3 py-1 rounded-full text-xs font-semibold
                        {{ $request->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                        {{ $request->status === 'verified' ? 'bg-green-100 text-green-800' : '' }}
                        {{ $request->status === 'assigned' ? 'bg-blue-100 text-blue-800' : '' }}
                        {{ $request->status === 'completed' ? 'bg-gray-100 text-gray-800' : '' }}">
                        {{ ucfirst($request->status) }}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <div class="flex space-x-2">
                        <button type="button"
                            class="text-blue-600 hover:text-blue-800 view-request-btn"
                            title="View"
                            data-request-id="{{ $request->id }}"
                            data-client-name="{{ $request->client->name }}"
                            data-contact-method="{{ $request->contact_method }}"
                            data-contact-method-label="{{ ucfirst(str_replace('_', ' ', $request->contact_method)) }}"
                            data-atw-reference="{{ $request->atw_reference }}"
                            data-atw-verified="{{ $request->atw_verified ? '1' : '0' }}"
                            data-container-size="{{ $request->container_size }}"
                            data-container-type="{{ ucfirst($request->container_type) }}"
                            data-pickup="{{ $request->pickup_location }}"
                            data-delivery="{{ $request->delivery_location }}"
                            data-schedule-date="{{ $request->preferred_schedule->format(config('settings.date_format', 'M d, Y')) }}"
                            data-schedule-time="{{ $request->preferred_schedule->format('h:i A') }}"
                            data-status="{{ $request->status }}"
                            data-notes="{{ $request->notes ?? '' }}"
                            data-created="{{ $request->created_at->diffForHumans() }}"
                            data-verify-url="{{ $request->status === 'pending' ? route('requests.verify', $request) : '' }}">
                            <i class="fas fa-eye"></i>
                        </button>
                        @if($request->status === 'pending')
                        <form method="POST" action="{{ route('requests.verify', $request) }}" class="inline">
                            @csrf
                            <button type="button" class="text-green-600 hover:text-green-800 verify-request-btn" title="Verify">
                                <i class="fas fa-check"></i>
                            </button>
                        </form>
                        @endif
                        @if($request->status === 'verified')
                        <a href="{{ route('trips.create', $request) }}"
                            class="text-purple-600 hover:text-purple-800"
                            title="Assign Driver">
                            <i class="fas fa-user-plus"></i>
                        </a>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                    No delivery requests found
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($requests->hasPages())
<div class="px-6 py-4 bg-white border-t border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <span class="text-sm text-gray-600">
        Showing {{ $requests->firstItem() }} to {{ $requests->lastItem() }} of {{ $requests->total() }} requests
    </span>
    <div class="flex gap-3">
        @if($requests->onFirstPage())
        <span class="px-4 py-2 rounded-md bg-gray-100 text-gray-400 cursor-not-allowed">Previous</span>
        @else
        <a href="{{ $requests->previousPageUrl() }}" data-pagination="requests"
            class="px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
            Previous
        </a>
        @endif

        @if($requests->hasMorePages())
        <a href="{{ $requests->nextPageUrl() }}" data-pagination="requests"
            class="px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
            Next
        </a>
        @else
        <span class="px-4 py-2 rounded-md bg-gray-100 text-gray-400 cursor-not-allowed">Next</span>
        @endif
    </div>
</div>
@endif