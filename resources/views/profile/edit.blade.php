@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')
<div class="container mx-auto px-4">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <a href="{{ route('profile.index') }}" class="bg-blue-600 text-white px-8 py-2 rounded-full hover:bg-blue-700 mb-2 inline-block">
                Back to Profile
            </a>
            <h1 class="text-3xl font-bold text-gray-800">
                <i class="fas fa-user-edit text-blue-600"></i> Edit Profile
            </h1>
        </div>

        <!-- Messages -->
        @if(session('success'))
        <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
        @endif

        @if($errors->any())
        <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Sidebar -->
            <div class="md:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="text-center mb-6">
                        <div class="h-32 w-32 mx-auto rounded-full bg-blue-100 flex items-center justify-center mb-4">
                            <span class="text-5xl font-bold text-blue-600">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </span>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800">{{ Auth::user()->name }}</h3>
                        <p class="text-sm text-gray-600">{{ Auth::user()->email }}</p>
                    </div>

                    <nav class="space-y-2">
                        <a href="#profile-info" class="flex items-center px-4 py-2 text-gray-700 bg-blue-50 rounded-lg">
                            <i class="fas fa-user mr-3"></i>
                            Profile Information
                        </a>
                        <a href="#change-password" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-50 rounded-lg">
                            <i class="fas fa-lock mr-3"></i>
                            Change Password
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="md:col-span-2 space-y-6">
                <!-- Profile Information Form -->
                <div id="profile-info" class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">
                        Profile Information
                    </h2>

                    <form id="profileForm" action="{{ route('profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="space-y-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                    Full Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                    id="name" 
                                    name="name" 
                                    value="{{ old('name', Auth::user()->name) }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    required>
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                                    Email Address <span class="text-red-500">*</span>
                                </label>
                                <input type="email" 
                                    id="email" 
                                    name="email" 
                                    value="{{ old('email', Auth::user()->email) }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    required>
                            </div>

                            <div>
                                <label for="mobile" class="block text-sm font-medium text-gray-700 mb-1">
                                    Mobile Number <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                    id="mobile" 
                                    name="mobile" 
                                    value="{{ old('mobile', Auth::user()->mobile ?? '') }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="09XX-XXX-XXXX"
                                    required>
                            </div>

                            <div class="flex justify-end pt-4">
                                <button type="submit" id="saveChangesBtn" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                    <i class="fas fa-save"></i> Save Changes
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Change Password Form - Full Width -->
        <div class="max-w-4xl mx-auto mt-6">
            <div id="change-password" class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">
                        Change Password
                    </h2>

                    <form id="passwordForm" action="{{ route('profile.update-password') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div class="flex flex-col justify-end">
                                    <div class="flex items-center gap-2">
                                        <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">
                                            Current Password <span class="text-red-500">*</span>
                                        </label>
                                        <button type="button" 
                                            class="text-gray-500 hover:text-gray-700 focus:outline-none p-1"
                                            onclick="toggleAllPasswords()">
                                            <i class="fas fa-eye text-sm" id="password-toggle-icon"></i>
                                        </button>
                                    </div>
                                    <input type="password" 
                                        id="current_password" 
                                        name="current_password" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        required>
                                </div>

                                <div class="flex flex-col justify-end">
                                    <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">
                                        New Password <span class="text-red-500">*</span>
                                    </label>
                                    <input type="password" 
                                        id="new_password" 
                                        name="new_password" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        required>
                                </div>

                                <div class="flex flex-col justify-end">
                                    <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                                        Confirm New Password <span class="text-red-500">*</span>
                                    </label>
                                    <input type="password" 
                                        id="new_password_confirmation" 
                                        name="new_password_confirmation" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        required>
                                </div>

                                <div class="flex items-end">
                                    <button type="submit" id="updatePasswordBtn" class="w-full px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                        <i class="fas fa-key"></i> Update Password
                                    </button>
                                </div>
                            </div>

                            <div>
                                <p class="text-xs text-gray-500">
                                    Password must be at least 8 characters long
                                </p>
                            </div>

                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <h4 class="text-sm font-semibold text-blue-800 mb-2">Password Requirements:</h4>
                                <ul class="text-xs text-blue-700 space-y-1">
                                    <li><i class="fas fa-check-circle"></i> Minimum 8 characters</li>
                                    <li><i class="fas fa-check-circle"></i> At least one uppercase letter</li>
                                    <li><i class="fas fa-check-circle"></i> At least one lowercase letter</li>
                                    <li><i class="fas fa-check-circle"></i> At least one number</li>
                                </ul>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom Confirmation Modal -->
<div id="confirmModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg shadow-xl p-6x`x` max-w-sm w-full mx-4 transform transition-all">
        <div class="flex items-center mb-4">
            <div class="bg-yellow-100 p-2 rounded-full mr-3">
                <i class="fas fa-exclamation-triangle text-yellow-600"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-800" id="confirmTitle">Confirm Action</h3>
        </div>
        <p class="text-gray-600 mb-6" id="confirmMessage">Are you sure you want to proceed?</p>
        <div class="flex justify-center space-x-3">
            <button id="confirmCancel" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                Cancel
            </button>
            <button id="confirmOk" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                Confirm
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let currentForm = null;
    
    // Profile form confirmation
    document.getElementById('profileForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Check if form is valid using browser's native validation
        if (this.checkValidity()) {
            currentForm = this;
            showConfirmModal('Are you sure you want to save changes?');
        } else {
            // Let browser show native validation messages
            this.reportValidity();
        }
    });
    
    // Password form confirmation
    document.getElementById('passwordForm').addEventListener('submit', function(e) {
        e.preventDefault();
        currentForm = this;
        showConfirmModal('Are you sure you want to update your password?');
    });
    
    function showConfirmModal(message) {
        document.getElementById('confirmMessage').textContent = message;
        document.getElementById('confirmModal').classList.remove('hidden');
    }
    
    function hideConfirmModal() {
        document.getElementById('confirmModal').classList.add('hidden');
        currentForm = null;
    }
    
    // Confirm button click
    document.getElementById('confirmOk').addEventListener('click', function() {
        if (currentForm) {
            currentForm.submit();
        }
        hideConfirmModal();
    });
    
    // Cancel button click
    document.getElementById('confirmCancel').addEventListener('click', function() {
        hideConfirmModal();
    });
    
    // Close modal on background click
    document.getElementById('confirmModal').addEventListener('click', function(e) {
        if (e.target === this) {
            hideConfirmModal();
        }
    });
    
    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            hideConfirmModal();
        }
    });
    // Toggle all password fields at once
    function toggleAllPasswords() {
        const passwordFields = ['current_password', 'new_password', 'new_password_confirmation'];
        const icon = document.getElementById('password-toggle-icon');
        
        // Check current state (if any field is text, consider it's showing)
        const isShowing = document.getElementById('current_password').type === 'text';
        
        passwordFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (isShowing) {
                field.type = 'password';
            } else {
                field.type = 'text';
            }
        });
        
        // Update icon only
        if (isShowing) {
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        } else {
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        }
    }
</script>
@endpush
@endsection
