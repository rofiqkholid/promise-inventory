@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="max-w-5xl mx-auto" x-data="{ activeTab: 'personal' }">
    <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">Account Settings</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage your personal information and security preferences.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Sidebar Navigation -->
        <div class="lg:col-span-3 space-y-4">
            <div class="bg-white dark:bg-gray-800 rounded-xs border border-slate-200 dark:border-gray-700 overflow-hidden">
                <nav class="p-2 flex flex-col gap-1">
                    <button @click="activeTab = 'personal'" 
                        :class="activeTab === 'personal' ? 'text-primary-600 bg-primary-50 dark:bg-primary-900/20 border-primary-100 dark:border-primary-800' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 border-transparent'"
                        class="flex items-center gap-3 px-4 py-2.5 text-sm font-bold rounded-xs transition-all border text-left w-full">
                        <i class="fa-solid fa-user-circle text-lg"></i>
                        <span>Personal Info</span>
                    </button>
                    <button @click="activeTab = 'security'" 
                        :class="activeTab === 'security' ? 'text-primary-600 bg-primary-50 dark:bg-primary-900/20 border-primary-100 dark:border-primary-800' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 border-transparent'"
                        class="flex items-center gap-3 px-4 py-2.5 text-sm font-bold rounded-xs transition-all border text-left w-full">
                        <i class="fa-solid fa-lock text-lg"></i>
                        <span>Security</span>
                    </button>
                </nav>
            </div>

            <!-- Role Summary Card (Flat style, no gradient, no shadow) -->
            <div class="bg-slate-50 dark:bg-gray-800 rounded-xs border border-slate-200 dark:border-gray-700 p-4">
                <h4 class="font-bold text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-widest mb-3">Your Assigned Roles</h4>
                <div class="flex flex-wrap gap-1.5">
                    @foreach($user->roles as $role)
                        <span class="px-2 py-0.5 bg-white dark:bg-gray-700 border border-slate-200 dark:border-gray-600 text-slate-700 dark:text-gray-300 text-[10px] font-bold uppercase tracking-wide rounded-xs">
                            {{ $role->name }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="lg:col-span-9">
            <!-- Personal Info Section -->
            <div x-show="activeTab === 'personal'" x-cloak class="bg-white dark:bg-gray-800 rounded-xs border border-slate-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-gray-700 bg-slate-50/50 dark:bg-gray-700/30 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-gray-900 dark:text-white">General Information</h3>
                        <p class="text-[11px] text-gray-500 font-medium uppercase tracking-wider mt-0.5">Basic identity and contact details.</p>
                    </div>
                </div>
                
                <form id="profileUpdateForm" class="p-6 space-y-6">
                    @csrf
                    
                    <!-- Avatar Section -->
                    <div class="flex items-center gap-4 mb-6">
                        <div class="h-16 w-16 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 flex items-center justify-center font-bold text-xl border-2 border-primary-200 dark:border-primary-800 uppercase">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-900 dark:text-white">{{ $user->name }}</h4>
                            <p class="text-xs text-gray-500">{{ $user->email }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Full Name</label>
                            <input type="text" name="name" value="{{ $user->name }}" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900/50 border border-slate-200 dark:border-gray-700 rounded-xs text-sm font-bold focus:bg-white focus:border-primary-500 transition-all outline-none" required>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">NIK / ID Card</label>
                            <input type="text" name="nik" value="{{ $user->nik }}" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900/50 border border-slate-200 dark:border-gray-700 rounded-xs text-sm font-bold focus:bg-white focus:border-primary-500 transition-all outline-none" required>
                        </div>
                    </div>
                    
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Email Address</label>
                        <input type="email" name="email" value="{{ $user->email }}" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900/50 border border-slate-200 dark:border-gray-700 rounded-xs text-sm font-bold focus:bg-white focus:border-primary-500 transition-all outline-none" required>
                    </div>

                    <div class="pt-6 border-t border-slate-100 dark:border-gray-700 flex justify-end">
                        <button type="submit" class="inline-flex items-center justify-center px-6 py-2.5 bg-primary-600 text-white text-xs font-bold uppercase tracking-widest rounded-xs hover:bg-primary-700 transition-all active:scale-95 gap-2 h-10">
                           Save Information
                        </button>
                    </div>
                </form>
            </div>

            <!-- Password Security Section -->
            <div x-show="activeTab === 'security'" x-cloak class="bg-white dark:bg-gray-800 rounded-xs border border-slate-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-gray-700 bg-slate-50/50 dark:bg-gray-700/30">
                    <h3 class="font-bold text-gray-900 dark:text-white">Security Settings</h3>
                    <p class="text-[11px] text-gray-500 font-medium uppercase tracking-wider mt-0.5">Manage your password and account protection.</p>
                </div>
                
                <form id="passwordUpdateForm" class="p-6 space-y-6">
                    @csrf
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Current Password</label>
                        <input type="password" name="current_password" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900/50 border border-slate-200 dark:border-gray-700 rounded-xs text-sm font-bold focus:bg-white focus:border-primary-500 transition-all outline-none" required>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">New Password</label>
                            <input type="password" name="password" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900/50 border border-slate-200 dark:border-gray-700 rounded-xs text-sm font-bold focus:bg-white focus:border-primary-500 transition-all outline-none" required>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-gray-900/50 border border-slate-200 dark:border-gray-700 rounded-xs text-sm font-bold focus:bg-white focus:border-primary-500 transition-all outline-none" required>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-slate-100 dark:border-gray-700 flex justify-end">
                        <button type="submit" class="inline-flex items-center justify-center px-6 py-2.5 bg-slate-900 dark:bg-white text-white dark:text-slate-900 text-xs font-bold uppercase tracking-widest rounded-xs hover:bg-slate-800 dark:hover:bg-slate-100 transition-all active:scale-95 gap-2 h-10">
                           Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Handle profile update
    $('#profileUpdateForm').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const originalHtml = submitBtn.html();

        submitBtn.prop('disabled', true).html('<i class="fa-solid fa-circle-notch fa-spin"></i> Saving...');

        $.ajax({
            url: "{{ route('profile.update') }}",
            type: 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Profile Updated',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 1500,
                        position: 'top-end',
                        toast: true
                    });
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Something went wrong.';
                Swal.fire({
                    icon: 'error',
                    title: 'Update Failed',
                    text: message
                });
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalHtml);
            }
        });
    });

    // Handle password update
    $('#passwordUpdateForm').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const originalHtml = submitBtn.html();

        submitBtn.prop('disabled', true).html('<i class="fa-solid fa-circle-notch fa-spin"></i> Updating...');

        $.ajax({
            url: "{{ route('profile.updatePassword') }}",
            type: 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Password Changed!',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 1500,
                        position: 'top-end',
                        toast: true
                    });
                    form[0].reset();
                }
            },
            error: function(xhr) {
                let message = 'Something went wrong.';
                if (xhr.responseJSON?.errors) {
                    message = Object.values(xhr.responseJSON.errors).flat().join('\n');
                } else if (xhr.responseJSON?.message) {
                    message = xhr.responseJSON.message;
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Failed to Change Password',
                    text: message
                });
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalHtml);
            }
        });
    });
});
</script>
@endpush
