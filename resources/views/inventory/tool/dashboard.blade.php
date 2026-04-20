@extends('layouts.app')

@section('title', 'Tool Inventory Dashboard')

@section('content')
<div class="text-gray-900 dark:text-gray-100">
    <div class="sm:flex sm:items-center sm:justify-between mb-8">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl tracking-tighter">Tool Dashboard</h2>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-medium">Overview of tool inventory and usage stats.</p>
        </div>
    </div>

    {{-- Placeholder Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white dark:bg-gray-800 p-6 rounded-xs border border-slate-200 dark:border-gray-700">
            <p class="text-[10px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-widest mb-1">Total Categories</p>
            <p class="text-3xl font-bold text-primary-600">--</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-6 rounded-xs border border-slate-200 dark:border-gray-700">
            <p class="text-[10px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-widest mb-1">Total Tools</p>
            <p class="text-3xl font-bold text-primary-600">--</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-6 rounded-xs border border-slate-200 dark:border-gray-700">
            <p class="text-[10px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-widest mb-1">Low Stock Tools</p>
            <p class="text-3xl font-bold text-red-600">--</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-6 rounded-xs border border-slate-200 dark:border-gray-700">
            <p class="text-[10px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-widest mb-1">Current Value</p>
            <p class="text-3xl font-bold text-green-600">IDR 0</p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 p-8 rounded-xs border border-slate-200 dark:border-gray-700 text-center">
        <div class="flex flex-col items-center justify-center gap-4">
            <div class="w-16 h-16 bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center text-primary-600">
                <i class="fa-solid fa-chart-line text-2xl"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Tool Dashboard is Coming Soon</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 max-w-sm mx-auto">We are currently integrating the analytics for tool inventory. You can manage tool master data in the meantime.</p>
            </div>
        </div>
    </div>
</div>
@endsection
