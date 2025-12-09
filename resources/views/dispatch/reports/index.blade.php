@extends('layouts.app')

@section('content')
<div class="container mx-auto px-5 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-5">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Reports & Analytics</h1>
            <p class="text-gray-600 mt-1">View comprehensive reports and statistics</p>
        </div>
        <!-- Reports Dropdown -->
        <div class="relative group">
            <button class="bg-white text-blue-600 border-2 border-blue-600 px-8 py-2 rounded-full hover:bg-green-500 hover:border-green-500 hover:text-white transition-colors flex items-center">
                <i class="fas fa-chart-line mr-2"></i> View Reports
                <i class="fas fa-chevron-down ml-2 transition-transform group-hover:rotate-180"></i>
            </button>
            
            <!-- Dropdown Menu -->
            <div class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl border border-gray-200 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                <!-- Daily Reports -->
                <div class="p-4 border-b border-gray-100 hover:bg-gray-50 transition">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="bg-blue-100 p-2 rounded-lg">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-800">Daily Reports</h4>
                            <p class="text-xs text-gray-600">View daily trip summaries</p>
                        </div>
                    </div>
                    <a href="{{ route('reports.daily') }}" class="block w-full text-center bg-blue-600 text-white px-3 py-2 rounded hover:bg-blue-700 transition text-sm">
                        View Daily Report
                    </a>
                </div>

                <!-- Weekly Reports -->
                <div class="p-4 border-b border-gray-100 hover:bg-gray-50 transition">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="bg-green-100 p-2 rounded-lg">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-800">Weekly Reports</h4>
                            <p class="text-xs text-gray-600">Analyze weekly performance trends</p>
                        </div>
                    </div>
                    <a href="{{ route('reports.weekly') }}" class="block w-full text-center bg-green-600 text-white px-3 py-2 rounded hover:bg-green-700 transition text-sm">
                        View Weekly Report
                    </a>
                </div>

                <!-- Monthly Reports -->
                <div class="p-4 hover:bg-gray-50 transition">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="bg-purple-100 p-2 rounded-lg">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-800">Monthly Reports</h4>
                            <p class="text-xs text-gray-600">Monthly performance overview</p>
                        </div>
                    </div>
                    <a href="{{ route('reports.monthly') }}" class="block w-full text-center bg-purple-600 text-white px-3 py-2 rounded hover:bg-purple-700 transition text-sm">
                        View Monthly Report
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Today's Trips</p>
                    <p class="text-4xl font-bold text-gray-800">{{ $stats['today_trips'] }}</p>
                </div>
                <div class="bg-blue-500 p-3 rounded-lg">
                    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">This Week</p>
                    <p class="text-4xl font-bold text-gray-800">{{ $stats['week_trips'] }}</p>
                </div>
                <div class="bg-green-500 p-3 rounded-lg">
                    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">This Month</p>
                    <p class="text-4xl font-bold text-gray-800">{{ $stats['month_trips'] }}</p>
                </div>
                <div class="bg-purple-500 p-3 rounded-lg">
                    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-orange-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Active Drivers</p>
                    <p class="text-4xl font-bold text-gray-800">{{ $stats['total_drivers'] }}</p>
                </div>
                <div class="bg-orange-500 p-3 rounded-lg">
                    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-indigo-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Vehicles</p>
                    <p class="text-4xl font-bold text-gray-800">{{ $stats['total_vehicles'] }}</p>
                </div>
                <div class="bg-indigo-500 p-3 rounded-lg">
                    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-pink-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Total Clients</p>
                    <p class="text-4xl font-bold text-gray-800">{{ $stats['total_clients'] }}</p>
                </div>
                <div class="bg-pink-500 p-3 rounded-lg">
                    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Status Distribution Pie Chart -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Trip Status Distribution</h3>
            <div style="height: 300px; position: relative;">
                <canvas id="statusPieChart"></canvas>
            </div>
        </div>

        <!-- Weekly Trips Bar Chart -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Last 7 Days Trips</h3>
            <div style="height: 300px; position: relative;">
                <canvas id="weeklyBarChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Monthly Trends Chart -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Monthly Trends (Last 6 Months)</h3>
        <div style="height: 400px; position: relative;">
            <canvas id="monthlyBarChart"></canvas>
        </div>
    </div>

    <!-- Top Drivers Performance -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Top 5 Drivers Performance</h3>
        <div style="height: 400px; position: relative;">
            <canvas id="driversBarChart"></canvas>
        </div>
    </div>

</div>

@push('scripts')
<script src="{{ asset('js/chart.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Check if Chart.js is loaded
        if (typeof Chart === 'undefined') {
            console.error('Chart.js library failed to load');
            alert('Chart.js library failed to load. Please check your internet connection.');
            return;
        }

        console.log('Chart.js loaded successfully');

        // Status Distribution Pie Chart
        const statusCtx = document.getElementById('statusPieChart');
        if (statusCtx) {
            try {
                    new Chart(statusCtx, {
                        type: 'pie',
                        data: {
                            labels: ['Completed', 'In Transit', 'Scheduled', 'Cancelled'],
                            datasets: [{
                                data: [
                                    {{ $statusData['completed'] ?? 0 }},
                                    {{ $statusData['in_transit'] ?? 0 }},
                                    {{ $statusData['scheduled'] ?? 0 }},
                                    {{ $statusData['cancelled'] ?? 0 }}
                                ],
                                backgroundColor: [
                                    '#10b981',
                                    '#3b82f6',
                                    '#6b7280',
                                    '#ef4444'
                                ],
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
                    console.log('Status pie chart created');
            } catch (error) {
                console.error('Error creating status pie chart:', error);
            }
        }

        // Weekly Trips Bar Chart
        const weeklyCtx = document.getElementById('weeklyBarChart');
        if (weeklyCtx) {
            try {
                    new Chart(weeklyCtx, {
                        type: 'bar',
                        data: {
                            labels: {!! json_encode(array_column($weeklyTripsData, 'date')) !!},
                            datasets: [{
                                label: 'Trips',
                                data: {!! json_encode(array_column($weeklyTripsData, 'count')) !!},
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
                                }
                            },
                            plugins: {
                                legend: {
                                    display: false
                                }
                            }
                        }
                    });
                    console.log('Weekly bar chart created');
            } catch (error) {
                console.error('Error creating weekly bar chart:', error);
            }
        }

        // Monthly Trends Bar Chart
        const monthlyCtx = document.getElementById('monthlyBarChart');
        if (monthlyCtx) {
            try {
                    new Chart(monthlyCtx, {
                        type: 'bar',
                        data: {
                            labels: {!! json_encode(array_column($monthlyTripsData, 'month')) !!},
                            datasets: [{
                                label: 'Trips',
                                data: {!! json_encode(array_column($monthlyTripsData, 'count')) !!},
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
                    console.log('Monthly bar chart created');
            } catch (error) {
                console.error('Error creating monthly bar chart:', error);
            }
        }

        // Top Drivers Bar Chart
        const driversCtx = document.getElementById('driversBarChart');
        if (driversCtx) {
            try {
                    new Chart(driversCtx, {
                        type: 'bar',
                        data: {
                            labels: {!! json_encode(array_column($topDrivers->toArray(), 'name')) !!},
                            datasets: [{
                                label: 'Completed Trips',
                                data: {!! json_encode(array_column($topDrivers->toArray(), 'completed')) !!},
                                backgroundColor: '#10b981',
                                borderColor: '#059669',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            indexAxis: 'y',
                            scales: {
                                x: {
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
                    console.log('Drivers bar chart created');
            } catch (error) {
                console.error('Error creating drivers bar chart:', error);
            }
        }
    });
</script>
@endpush
@endsection