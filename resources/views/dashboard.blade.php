@extends('layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')
    
    <!-- Welcome Section -->
    <div class="mb-8">
        <h3 class="text-2xl font-bold text-slate-800">Welcome back, {{ Auth::user()->name }}!</h3>
        <p class="text-slate-500">Here's what's happening with your inventory today.</p>
    </div>

    @if (session('success'))
    <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4 flex items-start gap-3">
        <i class="fa-solid fa-circle-check text-green-600 mt-1"></i>
        <div>
            <h3 class="font-semibold text-green-800">Success</h3>
            <p class="text-sm text-green-700">{{ session('success') }}</p>
        </div>
    </div>
    @endif

    <!-- Dashboard Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        
        <!-- Stats Card: Total Items -->
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500 mb-1">Total Items</p>
                    <p class="text-3xl font-bold text-slate-800">1,234</p>
                </div>
                <div class="h-12 w-12 bg-blue-50 rounded-lg flex items-center justify-center border border-blue-100">
                    <i class="fa-solid fa-box text-blue-600 text-xl"></i>
                </div>
            </div>
            <div class="flex items-center gap-2 mt-4 text-xs">
                <span class="text-green-600 font-medium flex items-center gap-1">
                    <i class="fa-solid fa-arrow-trend-up"></i> 12%
                </span>
                <span class="text-slate-400">from last month</span>
            </div>
        </div>

        <!-- Stats Card: Active Tasks -->
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500 mb-1">Active Tasks</p>
                    <p class="text-3xl font-bold text-slate-800">42</p>
                </div>
                <div class="h-12 w-12 bg-cyan-50 rounded-lg flex items-center justify-center border border-cyan-100">
                    <i class="fa-solid fa-list-check text-cyan-600 text-xl"></i>
                </div>
            </div>
            <div class="flex items-center gap-2 mt-4 text-xs">
                <span class="text-orange-600 font-medium">3 due today</span>
            </div>
        </div>

        <!-- Stats Card: Users -->
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500 mb-1">Users</p>
                    <p class="text-3xl font-bold text-slate-800">128</p>
                </div>
                <div class="h-12 w-12 bg-emerald-50 rounded-lg flex items-center justify-center border border-emerald-100">
                    <i class="fa-solid fa-users text-emerald-600 text-xl"></i>
                </div>
            </div>
            <div class="flex items-center gap-2 mt-4 text-xs">
                <span class="text-green-600 font-medium flex items-center gap-1">
                    <i class="fa-solid fa-plus"></i> 8
                </span>
                <span class="text-slate-400">new this month</span>
            </div>
        </div>

        <!-- Stats Card: Completion -->
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500 mb-1">Completion Rate</p>
                    <p class="text-3xl font-bold text-slate-800">92%</p>
                </div>
                <div class="h-12 w-12 bg-orange-50 rounded-lg flex items-center justify-center border border-orange-100">
                    <i class="fa-solid fa-chart-line text-orange-600 text-xl"></i>
                </div>
            </div>
             <div class="flex items-center gap-2 mt-4 text-xs">
                <span class="text-green-600 font-medium flex items-center gap-1">
                    <i class="fa-solid fa-arrow-up"></i> 5%
                </span>
                <span class="text-slate-400">from last month</span>
            </div>
        </div>
    </div>

    <!-- Recent Activity & Quick Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Recent Activity -->
        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 shadow-sm flex flex-col">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <h2 class="text-lg font-bold text-slate-800">Recent Activity</h2>
                <a href="#" class="text-sm text-blue-600 hover:text-blue-700 font-medium">View All</a>
            </div>
            <div class="p-5 space-y-5">
                
                <div class="flex items-start gap-4">
                    <div class="h-10 w-10 bg-blue-50 rounded-full flex items-center justify-center flex-shrink-0 border border-blue-100">
                        <i class="fa-solid fa-pen-to-square text-blue-600 text-sm"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-slate-800">Updated inventory item</p>
                        <p class="text-xs text-slate-500 mt-0.5">Part No: 123-456 was updated by Admin.</p>
                        <p class="text-xs text-slate-400 mt-1">2 hours ago</p>
                    </div>
                </div>

                <div class="flex items-start gap-4">
                    <div class="h-10 w-10 bg-emerald-50 rounded-full flex items-center justify-center flex-shrink-0 border border-emerald-100">
                        <i class="fa-solid fa-plus text-emerald-600 text-sm"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-slate-800">Created new task</p>
                        <p class="text-xs text-slate-500 mt-0.5">New stock check schedule created.</p>
                        <p class="text-xs text-slate-400 mt-1">5 hours ago</p>
                    </div>
                </div>

                <div class="flex items-start gap-4">
                    <div class="h-10 w-10 bg-orange-50 rounded-full flex items-center justify-center flex-shrink-0 border border-orange-100">
                        <i class="fa-solid fa-file-pdf text-orange-600 text-sm"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-slate-800">Generated report</p>
                        <p class="text-xs text-slate-500 mt-0.5">Monthly stock report generated automatically.</p>
                        <p class="text-xs text-slate-400 mt-1">1 day ago</p>
                    </div>
                </div>

            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm h-fit">
            <div class="p-5 border-b border-slate-100">
                <h2 class="text-lg font-bold text-slate-800">Quick Actions</h2>
            </div>
            <div class="p-5 space-y-3">
                <button class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 rounded-lg transition-colors flex items-center justify-center gap-2 shadow-sm shadow-blue-200">
                    <i class="fa-solid fa-plus"></i> Add Item
                </button>
                <button class="w-full bg-cyan-600 hover:bg-cyan-700 text-white font-medium py-2.5 rounded-lg transition-colors flex items-center justify-center gap-2 shadow-sm shadow-cyan-200">
                    <i class="fa-solid fa-list-check"></i> New Task
                </button>
                <div class="pt-2"></div>
                <button class="w-full border border-slate-200 hover:bg-slate-50 text-slate-700 font-medium py-2.5 rounded-lg transition-colors">
                    View Reports
                </button>
                <button class="w-full border border-slate-200 hover:bg-slate-50 text-slate-700 font-medium py-2.5 rounded-lg transition-colors">
                    Manage Users
                </button>
            </div>
        </div>
    </div>
@endsection
