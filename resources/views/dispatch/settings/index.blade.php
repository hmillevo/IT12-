@extends('layouts.app')

@section('title', 'System Settings')

@section('content')
<div class="container mx-auto px-4">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">
                <i class="fas fa-cogs text-blue-600"></i> System Settings
            </h1>
        </div>

        <!-- Success Message -->
        @if(session('success'))
        <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- Sidebar Navigation -->
            <div class="md:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-4">
                    <nav class="space-y-2">
                        <a href="#general" class="flex items-center px-4 py-2 text-gray-700 bg-blue-50 rounded-lg setting-tab active">
                            <i class="fas fa-sliders-h mr-3"></i>
                            General
                        </a>
                        <a href="#notifications" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-50 rounded-lg setting-tab">
                            <i class="fas fa-bell mr-3"></i>
                            Notifications
                        </a>
                        <a href="#dispatch" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-50 rounded-lg setting-tab">
                            <i class="fas fa-truck mr-3"></i>
                            Dispatch
                        </a>
                        <a href="#system" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-50 rounded-lg setting-tab">
                            <i class="fas fa-server mr-3"></i>
                            System
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="md:col-span-3">
                <form action="{{ route('utils.update-settings') }}" method="POST">
                    @csrf

                    <!-- General Settings -->
                    <div id="general" class="setting-content bg-white rounded-lg shadow-md p-6 mb-6">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">
                            General Settings
                        </h2>

                        <div class="space-y-4">
                            <div>
                                <label for="app_name" class="block text-sm font-medium text-gray-700 mb-1">
                                    Application Name
                                </label>
                                <input type="text" 
                                    id="app_name" 
                                    name="app_name" 
                                    value="{{ old('app_name', config('app.name', 'Dispatch System')) }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('app_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="timezone" class="block text-sm font-medium text-gray-700 mb-1">
                                    Timezone
                                </label>
                                <select id="timezone" 
                                    name="timezone" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    @php
                                        $currentTimezone = old('timezone', config('app.timezone', 'UTC'));
                                    @endphp
                                    <option value="Asia/Manila" {{ $currentTimezone === 'Asia/Manila' ? 'selected' : '' }}>Asia/Manila (GMT+8)</option>
                                    <option value="UTC" {{ $currentTimezone === 'UTC' ? 'selected' : '' }}>UTC</option>
                                    <option value="America/New_York" {{ $currentTimezone === 'America/New_York' ? 'selected' : '' }}>America/New York</option>
                                    <option value="Europe/London" {{ $currentTimezone === 'Europe/London' ? 'selected' : '' }}>Europe/London</option>
                                </select>
                                @error('timezone')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="date_format" class="block text-sm font-medium text-gray-700 mb-1">
                                    Date Format
                                </label>
                                <select id="date_format" 
                                    name="date_format" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    @php
                                        $currentDateFormat = old('date_format', config('settings.date_format', 'm/d/Y'));
                                    @endphp
                                    <option value="Y-m-d" {{ $currentDateFormat === 'Y-m-d' ? 'selected' : '' }}>YYYY-MM-DD (2025-01-15)</option>
                                    <option value="m/d/Y" {{ $currentDateFormat === 'm/d/Y' ? 'selected' : '' }}>MM/DD/YYYY (01/15/2025)</option>
                                    <option value="d/m/Y" {{ $currentDateFormat === 'd/m/Y' ? 'selected' : '' }}>DD/MM/YYYY (15/01/2025)</option>
                                    <option value="F j, Y" {{ $currentDateFormat === 'F j, Y' ? 'selected' : '' }}>Month Day, Year (January 15, 2025)</option>
                                </select>
                                @error('date_format')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="items_per_page" class="block text-sm font-medium text-gray-700 mb-1">
                                    Items Per Page
                                </label>
                                <input type="number" 
                                    id="items_per_page" 
                                    name="items_per_page" 
                                    value="{{ old('items_per_page', config('settings.items_per_page', 10)) }}"
                                    min="5"
                                    max="100"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('items_per_page')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Notification Settings -->
                    <div id="notifications" class="setting-content bg-white rounded-lg shadow-md p-6 mb-6 hidden">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">
                            Notification Settings
                        </h2>

                        <div class="space-y-4">
                            <div class="flex items-center justify-between py-3 border-b">
                                <div>
                                    <h3 class="font-medium text-gray-800">Email Notifications</h3>
                                    <p class="text-sm text-gray-600">Receive email updates for important events</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="email_notifications" value="1" class="sr-only peer" checked>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>

                            <div class="flex items-center justify-between py-3 border-b">
                                <div>
                                    <h3 class="font-medium text-gray-800">Trip Updates</h3>
                                    <p class="text-sm text-gray-600">Get notified when trip status changes</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="trip_notifications" value="1" class="sr-only peer" checked>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>

                            <div class="flex items-center justify-between py-3 border-b">
                                <div>
                                    <h3 class="font-medium text-gray-800">Request Notifications</h3>
                                    <p class="text-sm text-gray-600">Alert for new delivery requests</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="request_notifications" value="1" class="sr-only peer" checked>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>

                            <div class="flex items-center justify-between py-3">
                                <div>
                                    <h3 class="font-medium text-gray-800">System Alerts</h3>
                                    <p class="text-sm text-gray-600">Important system notifications and alerts</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="system_notifications" value="1" class="sr-only peer" checked>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Dispatch Settings -->
                    <div id="dispatch" class="setting-content bg-white rounded-lg shadow-md p-6 mb-6 hidden">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">
                            Dispatch Settings
                        </h2>

                        <div class="space-y-4">
                            <div>
                                <label for="auto_assign" class="block text-sm font-medium text-gray-700 mb-1">
                                    Auto-Assignment
                                </label>
                                <select id="auto_assign" 
                                    name="auto_assign" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="enabled">Enabled</option>
                                    <option value="disabled" selected>Disabled</option>
                                </select>
                                <p class="mt-1 text-xs text-gray-500">Automatically assign trips to available drivers</p>
                            </div>

                            <div>
                                <label for="default_trip_duration" class="block text-sm font-medium text-gray-700 mb-1">
                                    Default Trip Duration (hours)
                                </label>
                                <input type="number" 
                                    id="default_trip_duration" 
                                    name="default_trip_duration" 
                                    value="4"
                                    min="1"
                                    max="24"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div>
                                <label for="max_daily_trips" class="block text-sm font-medium text-gray-700 mb-1">
                                    Max Daily Trips Per Driver
                                </label>
                                <input type="number" 
                                    id="max_daily_trips" 
                                    name="max_daily_trips" 
                                    value="3"
                                    min="1"
                                    max="10"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div class="flex items-center justify-between py-3 border-t">
                                <div>
                                    <h3 class="font-medium text-gray-800">Require ATW Verification</h3>
                                    <p class="text-sm text-gray-600">Requests must be verified before assignment</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="require_atw_verification" value="1" class="sr-only peer" checked>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- System Settings -->
                    <div id="system" class="setting-content bg-white rounded-lg shadow-md p-6 mb-6 hidden">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">
                            System Settings
                        </h2>

                        <div class="space-y-4">
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <h3 class="font-medium text-blue-800 mb-2">System Information</h3>
                                <div class="text-sm text-blue-700 space-y-1">
                                    <p><strong>Version:</strong> 1.0.0</p>
                                    <p><strong>Environment:</strong> {{ app()->environment() }}</p>
                                    <p><strong>PHP Version:</strong> {{ phpversion() }}</p>
                                    <p><strong>Laravel Version:</strong> {{ app()->version() }}</p>
                                </div>
                            </div>

                            <div class="flex items-center justify-between py-3 border-b">
                                <div>
                                    <h3 class="font-medium text-gray-800">Maintenance Mode</h3>
                                    <p class="text-sm text-gray-600">Put system in maintenance mode</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="maintenance_mode" value="1" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>

                            <div class="flex items-center justify-between py-3 border-b">
                                <div>
                                    <h3 class="font-medium text-gray-800">Debug Mode</h3>
                                    <p class="text-sm text-gray-600">Enable detailed error messages</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="debug_mode" value="1" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>

                            <div class="pt-4 space-y-2">
                                <button type="button" class="w-full px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600">
                                    <i class="fas fa-sync-alt"></i> Clear Cache
                                </button>
                                <button type="button" class="w-full px-4 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600">
                                    <i class="fas fa-database"></i> Backup Database
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Save Button -->
                    <div class="flex justify-end">
                        <button type="submit" id="saveSettingsBtn" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <i class="fas fa-save"></i> Save All Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="confirmationModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Save Changes?</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500">
                    Are you sure you want to save these settings?
                </p>
            </div>
            <div class="flex gap-3 items-center px-4 py-3">
                <button id="cancelSaveBtn" class="flex-1 px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300">
                    Cancel
                </button>
                <button id="confirmSaveBtn" class="flex-1 px-4 py-2 bg-blue-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                    Confirm
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tabs = document.querySelectorAll('.setting-tab');
        const contents = document.querySelectorAll('.setting-content');

        tabs.forEach(tab => {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href').substring(1);

                // Remove active class from all tabs
                tabs.forEach(t => {
                    t.classList.remove('bg-blue-50');
                    t.classList.add('hover:bg-gray-50');
                });

                // Add active class to clicked tab
                this.classList.add('bg-blue-50');
                this.classList.remove('hover:bg-gray-50');

                // Hide all contents
                contents.forEach(content => {
                    content.classList.add('hidden');
                });

                // Show target content
                document.getElementById(targetId).classList.remove('hidden');
            });
        });

        // Confirmation dialog for save settings
        const saveSettingsBtn = document.getElementById('saveSettingsBtn');
        const settingsForm = document.querySelector('form[action="{{ route('utils.update-settings') }}"]');
        const confirmationModal = document.getElementById('confirmationModal');
        const confirmSaveBtn = document.getElementById('confirmSaveBtn');
        const cancelSaveBtn = document.getElementById('cancelSaveBtn');

        saveSettingsBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Show custom confirmation modal
            confirmationModal.classList.remove('hidden');
        });

        confirmSaveBtn.addEventListener('click', function() {
            // Hide modal and submit form
            confirmationModal.classList.add('hidden');
            settingsForm.submit();
        });

        cancelSaveBtn.addEventListener('click', function() {
            // Hide modal and do nothing
            confirmationModal.classList.add('hidden');
        });

        // Close modal when clicking outside
        confirmationModal.addEventListener('click', function(e) {
            if (e.target === confirmationModal) {
                confirmationModal.classList.add('hidden');
            }
        });
    });
</script>
@endpush
@endsection
