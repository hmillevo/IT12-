@extends('layouts.app')

@section('title', 'Monthly Report')

@section('content')
<div class="container mx-auto px-4 py-5">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <a href="{{ route('reports.index') }}" class="bg-blue-600 text-white px-8 py-2 rounded-full hover:bg-blue-700 mb-2 inline-block">
                Back
            </a>
            <h1 class="text-3xl font-bold text-gray-800">Monthly Report</h1>
            <p class="text-gray-600 mt-1">{{ $month->format('F Y') }}</p>
        </div>
    </div>

    <!-- Date Filter -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" action="{{ route('reports.monthly') }}" class="flex items-end gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Select Month</label>
                <input type="month"
                    name="month"
                    value="{{ $month->format('Y-m') }}"
                    class="border border-gray-300 rounded-lg px-4 py-2">
            </div>
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                <i class="fas fa-search"></i> Generate Report
            </button>
            <a href="{{ route('reports.monthly', ['month' => $month->format('Y-m'), 'export' => 'pdf']) }}"
                class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700">
                <i class="fas fa-file-pdf"></i> Export PDF
            </a>
            <a href="{{ route('reports.monthly', ['month' => $month->format('Y-m'), 'export' => 'excel']) }}"
                class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700">
                <i class="fas fa-file-excel"></i> Export Excel
            </a>
        </form>
    </div>

    <!-- Stats Summary -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-gray-500 text-sm">Total Trips</p>
            <p class="text-2xl font-bold text-blue-600">{{ $stats['total_trips'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-gray-500 text-sm">Completed</p>
            <p class="text-2xl font-bold text-green-600">{{ $stats['completed'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-gray-500 text-sm">In Transit</p>
            <p class="text-2xl font-bold text-yellow-600">{{ $stats['in_transit'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-gray-500 text-sm">Scheduled</p>
            <p class="text-2xl font-bold text-blue-600">{{ $stats['scheduled'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-gray-500 text-sm">Cancelled</p>
            <p class="text-2xl font-bold text-red-600">{{ $stats['cancelled'] }}</p>
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

        <!-- Weekly Trips Bar Chart -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Weekly Trip Distribution</h3>
            <div style="height: 300px; position: relative;">
                <canvas id="weeklyBarChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Performance Chart Placeholder -->
    <div class="bg-white rounded-lg shadow p-5 mb-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Monthly Performance</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="text-center p-4 bg-green-50 rounded-lg">
                <p class="text-sm text-gray-600">Completion Rate</p>
                <p class="text-3xl font-bold text-green-600">
                    {{ $stats['total_trips'] > 0 ? round(($stats['completed'] / $stats['total_trips']) * 100, 1) : 0 }}%
                </p>
            </div>
            <div class="text-center p-4 bg-yellow-50 rounded-lg">
                <p class="text-sm text-gray-600">Active Rate</p>
                <p class="text-3xl font-bold text-yellow-600">
                    {{ $stats['total_trips'] > 0 ? round(($stats['in_transit'] / $stats['total_trips']) * 100, 1) : 0 }}%
                </p>
            </div>
            <div class="text-center p-4 bg-red-50 rounded-lg">
                <p class="text-sm text-gray-600">Cancellation Rate</p>
                <p class="text-3xl font-bold text-red-600">
                    {{ $stats['total_trips'] > 0 ? round(($stats['cancelled'] / $stats['total_trips']) * 100, 1) : 0 }}%
                </p>
            </div>
        </div>
    </div>

    <!-- Trips by Week -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800">Trips by Week</h2>
        </div>

        @if($trips->isEmpty())
        <div class="p-8 text-center">
            <i class="fas fa-inbox text-gray-300 text-5xl mb-4"></i>
            <p class="text-gray-500">No trips found for this month</p>
        </div>
        @else
        @foreach($tripsByWeek as $weekNumber => $weekTrips)
        <div class="border-b border-gray-200">
            <div class="bg-gray-50 px-6 py-3">
                <h3 class="font-semibold text-gray-800">
                    Week {{ $weekNumber }}
                    <span class="text-sm text-gray-600 font-normal ml-2">({{ $weekTrips->count() }} trips)</span>
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-100 text-xs uppercase text-gray-600">
                        <tr>
                            <th class="px-4 py-3 text-left">Date</th>
                            <th class="px-4 py-3 text-left">ATW Reference</th>
                            <th class="px-4 py-3 text-left">Client</th>
                            <th class="px-4 py-3 text-left">Driver</th>
                            <th class="px-4 py-3 text-left">Vehicle</th>
                            <th class="px-4 py-3 text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($weekTrips as $trip)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">{{ $trip->scheduled_time->format('M d, Y') }}</td>
                            <td class="px-4 py-3">
                                <a href="{{ route('trips.show', $trip) }}" class="text-blue-600 hover:underline">
                                    {{ $trip->deliveryRequest->atw_reference }}
                                </a>
                            </td>
                            <td class="px-4 py-3">{{ $trip->deliveryRequest->client->name }}</td>
                            <td class="px-4 py-3">{{ $trip->driver->name }}</td>
                            <td class="px-4 py-3">{{ $trip->vehicle->plate_number }}</td>
                            <td class="px-4 py-3">
                                @php
                                $statusColors = [
                                'scheduled' => 'bg-blue-100 text-blue-800',
                                'in-transit' => 'bg-yellow-100 text-yellow-800',
                                'completed' => 'bg-green-100 text-green-800',
                                'cancelled' => 'bg-red-100 text-red-800',
                                ];
                                @endphp
                                <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $statusColors[$trip->status] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ ucfirst($trip->status) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endforeach
        @endif
    </div>
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

        // Weekly Trips Bar Chart
        const weeklyCtx = document.getElementById('weeklyBarChart');
        if (weeklyCtx) {
            new Chart(weeklyCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode(array_column($weeklyData, 'week')) !!},
                    datasets: [{
                        label: 'Trips',
                        data: {!! json_encode(array_column($weeklyData, 'count')) !!},
                        backgroundColor: '#8b5cf6',
                        borderColor: '#7c3aed',
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