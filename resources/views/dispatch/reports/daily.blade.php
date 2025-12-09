@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <a href="{{ route('reports.index') }}" class="bg-blue-600 text-white px-8 py-2 rounded-full hover:bg-blue-700 mb-2 inline-block">
                Back
            </a>
            <h1 class="text-3xl font-bold text-gray-800">Daily Report</h1>
            <p class="text-gray-600 mt-1">{{ $date->format('l, F d, Y') }}</p>
        </div>
        <div class="flex gap-2">
            <form action="{{ route('reports.export-daily') }}" method="GET" class="inline">
                <input type="hidden" name="date" value="{{ $date->format('Y-m-d') }}">
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                    <i class="fas fa-file-pdf"></i> Export PDF
                </button>
            </form>
            <button onclick="window.print()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                <i class="fas fa-print"></i> Print Report
            </button>
        </div>
    </div>

    <!-- Date Selector -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form action="{{ route('reports.daily') }}" method="GET" class="flex items-end gap-4">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">Select Date</label>
                <input type="date" name="date" value="{{ $date->format('Y-m-d') }}"
                    class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
            </div>
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                View Report
            </button>
        </form>
    </div>

    <!-- Statistics Summary -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-500 text-sm font-medium">Total Trips</p>
            <p class="text-3xl font-bold text-gray-800">{{ $stats['total_trips'] }}</p>
        </div>
        <div class="bg-green-50 rounded-lg shadow p-6">
            <p class="text-green-700 text-sm font-medium">Completed</p>
            <p class="text-3xl font-bold text-green-700">{{ $stats['completed'] }}</p>
        </div>
        <div class="bg-blue-50 rounded-lg shadow p-6">
            <p class="text-blue-700 text-sm font-medium">In Transit</p>
            <p class="text-3xl font-bold text-blue-700">{{ $stats['in_transit'] }}</p>
        </div>
        <div class="bg-gray-50 rounded-lg shadow p-6">
            <p class="text-gray-700 text-sm font-medium">Scheduled</p>
            <p class="text-3xl font-bold text-gray-700">{{ $stats['scheduled'] }}</p>
        </div>
        <div class="bg-red-50 rounded-lg shadow p-6">
            <p class="text-red-700 text-sm font-medium">Cancelled</p>
            <p class="text-3xl font-bold text-red-700">{{ $stats['cancelled'] }}</p>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Status Distribution Pie Chart -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Status Distribution</h3>
            <div style="height: 300px; position: relative;">
                <canvas id="statusPieChart"></canvas>
            </div>
        </div>

        <!-- Hourly Distribution Bar Chart -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Hourly Trip Distribution</h3>
            <div style="height: 300px; position: relative;">
                <canvas id="hourlyBarChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Completion Rate -->
    @php
    $completionRate = $stats['total_trips'] > 0
    ? round(($stats['completed'] / $stats['total_trips']) * 100, 1)
    : 0;
    @endphp
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Completion Rate</h3>
        <div class="flex items-center gap-4">
            <div class="flex-1">
                <div class="bg-gray-200 rounded-full h-4">
                    <div class="bg-green-600 h-4 rounded-full" style="width: {{ $completionRate }}%"></div>
                </div>
            </div>
            <div class="text-2xl font-bold text-green-600">{{ $completionRate }}%</div>
        </div>
    </div>

    <!-- Trips List -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800">Trip Details</h2>
        </div>

        @if($trips->isEmpty())
        <div class="p-12 text-center">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <p class="text-gray-500 text-lg">No trips found for this date</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ATW Ref</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Route</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Driver</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vehicle</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Duration</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($trips as $trip)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                {{ $trip->scheduled_time->format('h:i A') }}
                            </div>
                            @if($trip->actual_start_time)
                            <div class="text-xs text-gray-500">
                                Started: {{ $trip->actual_start_time->format('h:i A') }}
                            </div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">
                                {{ $trip->deliveryRequest->client->name }}
                            </div>
                            @if($trip->deliveryRequest->client->company)
                            <div class="text-xs text-gray-500">
                                {{ $trip->deliveryRequest->client->company }}
                            </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="{{ route('trips.show', $trip) }}"
                                class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                {{ $trip->deliveryRequest->atw_reference }}
                            </a>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">
                                <div class="flex items-center gap-1 mb-1">
                                    <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <circle cx="10" cy="10" r="3" />
                                    </svg>
                                    <span class="text-xs">{{ Str::limit($trip->deliveryRequest->pickup_location, 25) }}</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 2a1 1 0 011 1v6h6a1 1 0 110 2h-6v6a1 1 0 11-2 0v-6H3a1 1 0 110-2h6V3a1 1 0 011-1z" />
                                    </svg>
                                    <span class="text-xs">{{ Str::limit($trip->deliveryRequest->delivery_location, 25) }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $trip->driver->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $trip->vehicle->plate_number }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-3 py-1 text-xs font-semibold rounded-full
                                        {{ $trip->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                           ($trip->status === 'in-transit' ? 'bg-blue-100 text-blue-800' : 
                                           ($trip->status === 'cancelled' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')) }}">
                                {{ ucfirst($trip->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if($trip->actual_start_time && $trip->actual_end_time)
                            {{ $trip->actual_start_time->diffInMinutes($trip->actual_end_time) }} min
                            @else
                            -
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    <!-- Print Styles -->
    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
</div>

@push('scripts')
<script src="{{ asset('js/chart.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Check if Chart.js is loaded
        if (typeof Chart === 'undefined') {
            console.error('Chart.js library failed to load');
            return;
        }

        // Status Distribution Pie Chart
        const statusCtx = document.getElementById('statusPieChart');
        if (statusCtx) {
            new Chart(statusCtx, {
                type: 'pie',
                data: {
                    labels: {!! json_encode($statusChartData['labels']) !!},
                    datasets: [{
                        data: {!! json_encode($statusChartData['data']) !!},
                        backgroundColor: {!! json_encode($statusChartData['colors']) !!},
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    label += context.parsed + ' trips';
                                    return label;
                                }
                            }
                        }
                    }
                }
            });
        }

        // Hourly Distribution Bar Chart
        const hourlyCtx = document.getElementById('hourlyBarChart');
        if (hourlyCtx) {
            new Chart(hourlyCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode(array_column($hourlyData, 'hour')) !!},
                    datasets: [{
                        label: 'Trips',
                        data: {!! json_encode(array_column($hourlyData, 'count')) !!},
                        backgroundColor: '#3b82f6',
                        borderColor: '#2563eb',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        },
                        x: {
                            ticks: {
                                maxRotation: 45,
                                minRotation: 45
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }
    });
</script>
@endpush
@endsection