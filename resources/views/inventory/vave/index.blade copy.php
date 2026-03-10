@extends('layouts.app')
@section('title', 'VA/VE Analysis')
@section('page_title', 'Material Efficiency Analysis')
@section('header-title', 'VA/VE Analysis')

@section('content')
<div class="text-gray-900 dark:text-gray-100">
    <div class="sm:flex sm:items-center sm:justify-between mb-8">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl tracking-tighter">VA/VE Analysis</h2>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-medium">Compare baseline RFQ data with production revisions to analyze material efficiency.</p>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="mb-6 p-5 bg-white dark:bg-gray-800 rounded-xs border border-slate-200 dark:border-gray-700">
        <div class="flex flex-col md:flex-row items-end gap-5">
            <div class="w-full md:w-64">
                <label class="block mb-2 text-[10px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-widest">Customer</label>
                <select id="filterCustomer" class="select2-simple w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xs focus:ring-primary-500 focus:border-primary-500 block p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">All Customers</option>
                </select>
            </div>
            <div class="w-full md:w-64">
                <label class="block mb-2 text-[10px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-widest">Model</label>
                <select id="filterModel" class="select2-simple w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xs focus:ring-primary-500 focus:border-primary-500 block p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">All Models</option>
                </select>
            </div>
            <div class="flex items-center gap-3 ml-auto w-full md:w-auto mt-4 md:mt-0">
                <button type="button" id="btnResetFilter" class="h-10 px-4 inline-flex items-center justify-center rounded-xs bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 text-slate-500 hover:text-primary-600 hover:border-primary-100 hover:bg-primary-50 transition-all text-[10px] font-bold uppercase tracking-widest active:scale-95">
                    <i class="fa-solid fa-rotate-left mr-2"></i> Reset
                </button>
                <div class="hidden md:block h-8 w-px bg-slate-100 dark:bg-gray-700 mx-1"></div>
                <button type="button" id="btnExportSummary" class="h-10 px-6 inline-flex items-center justify-center bg-primary-600 hover:bg-primary-700 text-white text-[10px] font-bold uppercase tracking-widest rounded-xs transition-all gap-2 active:scale-95">
                    <i class="fa-solid fa-file-excel text-sm"></i> Export Summary
                </button>
            </div>
        </div>
    </div>

    <x-table id="vaveTable">
        <thead>
            <tr>
                <th class="text-center w-16">No</th>
                <th class="text-left w-48 min-w-[180px]">Part No</th>
                <th class="text-left">Part Name</th>
                <th class="text-center">Customer</th>
                <th class="text-center">Model</th>
                <th class="text-center">Baseline (Kg)</th>
                <th class="text-center">Latest (Kg)</th>
                <th class="text-center">Analysis Status</th>
                <th class="text-center w-[180px]">Action</th>
            </tr>
        </thead>
        <tbody></tbody>
    </x-table>
</div>

{{-- RFQ Management Modal --}}
<div id="rfqModal" tabindex="-1" aria-hidden="true" class="hidden fixed inset-0 z-50 justify-center items-center w-full h-full bg-slate-950/60 backdrop-blur-sm transition-all duration-300">
    <div class="relative p-4 w-full max-w-4xl max-h-screen">
        <div class="relative bg-white rounded-xs border border-slate-200 dark:border-gray-700 dark:bg-gray-800 flex flex-col max-h-[90vh] overflow-hidden">
            <button type="button" class="close-modal-button text-slate-400 absolute top-4 right-4 bg-transparent hover:bg-slate-100 dark:hover:bg-gray-700 hover:text-slate-900 dark:hover:text-white rounded-xs text-sm w-9 h-9 flex items-center justify-center z-10 transition-all active:scale-95">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
            
            <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 bg-slate-50/80 dark:bg-slate-900/50">
                <h3 class="text-base font-bold text-slate-900 dark:text-white uppercase tracking-widest" id="rfqModalTitle">Manage Baseline (RFQ)</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 font-medium">Define baseline parameters for VA/VE analysis</p>
            </div>

            <form id="rfqForm" method="POST" class="flex flex-col flex-1 overflow-hidden">
                @csrf
                <input type="hidden" name="product_id" id="rfq_product_id">
                <input type="hidden" name="rfq_id" id="rfq_id">
                
                <div class="flex-1 overflow-y-auto custom-scrollbar">
                    <div class="p-8">
                        {{-- Version Management Toolbar --}}
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 p-1 bg-white dark:bg-gray-700/50 rounded-xs border border-slate-200 dark:border-gray-700">
                            <div class="flex-[2] p-3">
                                <label class="block mb-2 text-[10px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-widest">
                                    Baseline Version
                                </label>
                                <div class="flex gap-2">
                                    <select id="rfq_history_select" class="select2-rfq flex-1 bg-slate-50 border border-slate-200 text-slate-900 text-[11px] font-bold rounded-xs focus:ring-primary-500 focus:border-primary-500 block p-2.5 h-10 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all">
                                        <option value="">-- Load/Select Version --</option>
                                    </select>
                                    <button type="button" id="btn_new_baseline" class="py-2 px-4 text-[10px] font-black text-primary-600 bg-primary-50 border border-primary-200 rounded-xs hover:bg-primary-100 focus:outline-none dark:bg-gray-900 dark:text-primary-400 dark:border-gray-600 dark:hover:bg-gray-700 hidden transition-all uppercase tracking-widest active:scale-95" title="Create New Draft">
                                        <i class="fa-solid fa-plus mr-1"></i> New
                                    </button>
                                    <button type="button" id="btn_delete_baseline" class="py-2 px-4 text-[10px] font-black text-red-600 bg-red-50 border border-red-200 rounded-xs hover:bg-red-100 focus:outline-none dark:bg-gray-900 dark:text-red-400 dark:border-gray-600 dark:hover:bg-gray-700 hidden transition-all uppercase tracking-widest active:scale-95" title="Delete This Baseline">
                                        <i class="fa-solid fa-trash-can mr-1"></i> Delete
                                    </button>
                                </div>
                            </div>
                            <div class="hidden md:block w-px bg-gray-200 h-16 mx-2"></div>
                            <div class="flex-1 p-2 flex flex-col md:items-end justify-center">
                                <span class="text-[9px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1.5">Active Baseline Status</span>
                                <div class="flex flex-col gap-1 w-full md:w-auto min-w-[220px]">
                                    <div id="active_baseline_display" class="text-[10px] font-bold text-slate-700 dark:text-gray-200 px-3 py-1 bg-amber-50 dark:bg-amber-900/10 rounded-xs border border-amber-100 dark:border-amber-800/50 text-center uppercase tracking-widest">
                                        -
                                    </div>
                                    <div id="active_weight_display" class="text-[10px] font-bold text-primary-600 dark:text-primary-400 px-3 py-1 bg-primary-50/30 dark:bg-primary-900/10 rounded-xs border border-primary-100/50 dark:border-primary-800/50 text-center uppercase tracking-widest">
                                        -
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Main Grid --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                            
                            {{-- Identity & Context --}}
                            <div class="space-y-6">
                                <div class="hidden">
                                    <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Baseline Name</label>
                                    <input type="text" name="rfq_name" id="rfq_name" readonly class="bg-slate-50 border border-slate-200 text-slate-400 text-[11px] font-bold rounded-xs block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all uppercase tracking-widest">
                                    <input type="hidden" name="is_active" value="1">
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Material Spec</label>
                                    <select name="material_spec_id" id="rfq_material_spec_id" class="select2-rfq bg-white border border-slate-200 text-slate-900 text-[11px] font-bold rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all">
                                            <option value="">Select Spec</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Unit Type</label>
                                        <select name="unit_id" id="rfq_unit_id" class="select2-rfq bg-white border border-slate-200 text-slate-900 text-[11px] font-bold rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all">
                                            <option value="">Select Unit</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="space-y-2">
                                    <label for="remark" class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Remark / Notes</label>
                                    <textarea name="remark" id="remark" rows="3" class="block p-3 w-full text-xs font-bold text-slate-800 bg-white rounded-xs border border-slate-200 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all placeholder-slate-300" placeholder="Optional notes..."></textarea>
                                </div>
                            </div>

                            {{-- Parameters & Calculations --}}
                            <div class="space-y-6">
                                <div class="p-5 bg-slate-50 dark:bg-gray-900/30 rounded-xs border border-slate-100 dark:border-gray-700 relative">
                                    <h5 class="text-[9px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-[0.2em] mb-4 flex items-center absolute -top-3 left-4 bg-white dark:bg-gray-800 px-3 py-1 border border-slate-100 dark:border-gray-700 rounded-xs shadow-sm">
                                        <i class="fa-solid fa-ruler-combined mr-2 text-primary-500"></i>
                                        Dimensions (mm)
                                    </h5>
                                    
                                    <div class="grid grid-cols-2 gap-4 mt-2">
                                         <div>
                                            <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Thickness</label>
                                            <input type="number" step="0.01" name="thickness" id="rfq_thickness" class="bg-white border border-slate-200 text-slate-900 text-xs font-bold rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="0.00">
                                        </div>
                                        <div>
                                            <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Width</label>
                                            <input type="number" step="0.01" name="width" id="rfq_width" class="bg-white border border-slate-200 text-slate-900 text-xs font-bold rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="0.00">
                                        </div>
                                        <div id="rfq_length_container">
                                            <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-400 uppercase tracking-wider" id="label_rfq_length">Length</label>
                                            <input type="number" step="0.01" name="length" id="rfq_length" class="bg-white border border-slate-200 text-slate-900 text-xs font-bold rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="0.00">
                                        </div>
                                        <div id="rfq_length2_container" class="hidden">
                                            <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Length 2 (L2)</label>
                                            <input type="number" step="0.01" name="length_2" id="rfq_length_2" class="bg-white border border-slate-200 text-slate-900 text-xs font-bold rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="0.00">
                                        </div>
                                        <div id="rfq_pitch_container" class="hidden">
                                            <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Pitch (mm)</label>
                                            <input type="number" step="0.01" name="pitch" id="rfq_pitch" class="bg-white border border-slate-200 text-slate-900 text-xs font-bold rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="0.00">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Density</label>
                                        <input type="number" step="0.001" name="density" id="rfq_density" value="7.85" class="bg-white border border-slate-200 text-slate-900 text-xs font-bold rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all" placeholder="7.850">
                                    </div>
                                    <div>
                                        <label class="block mb-2 text-[10px] font-semibold text-primary-600 dark:text-primary-400 uppercase tracking-wider">Gross Weight (Kg)</label>
                                        <input type="number" step="0.001" name="weight_kg" id="rfq_weight_kg" readonly class="bg-primary-50 border border-primary-100 text-primary-600 text-xs font-black rounded-xs block w-full h-10 px-3 dark:bg-primary-900/20 dark:border-primary-800 dark:text-primary-300 cursor-not-allowed uppercase tracking-tighter" placeholder="0.000">
                                    </div>
                                    <div>
                                        <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Net Weight (Kg)</label>
                                        <input type="number" step="0.001" name="net_weight" id="rfq_net_weight" class="bg-white border border-slate-200 text-slate-900 text-xs font-bold rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all" placeholder="0.000">
                                    </div>
                                    <div>
                                        <label class="block mb-2 text-[10px] font-semibold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider">Price (Rp/Kg)</label>
                                        <input type="number" step="0.01" name="material_price" id="rfq_material_price" value="20000" class="bg-emerald-50 border border-emerald-100 text-emerald-700 text-xs font-black rounded-xs focus:ring-emerald-500 focus:border-emerald-500 block w-full h-10 px-3 dark:bg-emerald-900/20 dark:border-emerald-800 dark:text-emerald-200 transition-all uppercase tracking-tighter" placeholder="0.00">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 px-8 py-6 border-t border-slate-100 dark:border-gray-700 bg-slate-50/50 dark:bg-slate-900/40">
                    <button type="button" class="close-modal-button text-slate-500 bg-white hover:bg-slate-50 focus:outline-none rounded-xs border border-slate-200 text-[10px] font-bold uppercase tracking-widest px-8 py-3 transition-all active:scale-95">
                        Cancel
                    </button>
                    <button type="submit" class="text-white bg-primary-600 hover:bg-primary-700 focus:outline-none font-bold rounded-xs text-[10px] uppercase tracking-widest px-8 py-3 text-center transition-all active:scale-95">
                        <i class="fa-solid fa-save mr-2"></i> Save Baseline
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Scripts for RFQ History --}}
{{-- Scripts for RFQ History Moved to @push('scripts') --}}

{{-- Comparison Modal --}}
<div id="comparisonModal" tabindex="-1" aria-hidden="true" class="hidden fixed inset-0 z-50 flex items-center justify-center w-full h-full bg-slate-950/60 backdrop-blur-sm p-4 transition-all duration-300">
    <div class="relative w-full max-w-6xl max-h-[90vh]">
        <div class="relative text-left bg-white rounded-xs border border-slate-200 dark:border-gray-700 dark:bg-gray-800 flex flex-col max-h-[90vh] overflow-hidden">
            <button type="button" class="close-modal-button text-slate-400 absolute top-4 right-4 bg-white dark:bg-slate-700 hover:bg-slate-50 hover:text-slate-900 rounded-xs text-sm w-9 h-9 inline-flex items-center justify-center dark:hover:bg-gray-600 dark:hover:text-white z-20 transition-all border border-slate-200 dark:border-gray-600 active:scale-95">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
            
            <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 bg-primary-50/80 dark:bg-slate-900/50 relative">
                <div class="absolute left-0 top-0 w-1.5 h-full bg-primary-600"></div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-3 uppercase tracking-widest" id="comparisonTitle">
                    <i class="fa-solid fa-chart-line text-primary-600"></i> VA/VE Material Analysis
                </h3>
                <p id="comparisonSubtitle" class="text-xs text-gray-500 dark:text-gray-400 mt-1 font-medium ml-8"></p>
            </div>

            <div class="px-6 py-3 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row items-center justify-between z-10 relative">
                <div class="flex items-center gap-6">
                    <div class="flex items-center gap-3">
                        <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="fa-solid fa-bullseye text-primary-500"></i> Plan (Base):
                        </label>
                        <select id="selectCompareBase" class="bg-white border border-primary-200 text-primary-800 text-[10px] font-bold rounded-xs focus:ring-primary-500 focus:border-primary-500 block px-3 py-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white outline-none w-56 cursor-pointer hover:bg-primary-50 transition-all uppercase tracking-widest"></select>
                    </div>
                    <div class="w-px h-6 bg-gray-200 dark:bg-gray-700"></div>
                    <div class="flex items-center gap-3">
                        <label class="text-[10px] font-bold text-gray-500 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="fa-solid fa-arrow-trend-up text-emerald-500"></i> Actual (Rev):
                        </label>
                        <select id="selectCompareActual" class="bg-white border border-emerald-200 text-emerald-800 text-[10px] font-bold rounded-xs focus:ring-emerald-500 focus:border-emerald-500 block px-3 py-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white outline-none w-48 cursor-pointer hover:bg-emerald-50 transition-all uppercase tracking-widest"></select>
                    </div>
                </div>
            </div>

            <div class="flex-1 overflow-auto p-6 bg-slate-50/30 dark:bg-gray-900">
                <div id="comparisonContainer">
                    {{-- Table will be injected here --}}
                </div>
            </div>
            
            <div class="px-8 py-5 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-[10px] text-gray-400 font-bold uppercase tracking-[0.2em] flex items-center gap-6">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-green-500 shadow-sm ring-2 ring-green-100 dark:ring-green-900"></span>
                        <span>Merit (Improvement)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-red-500 shadow-sm ring-2 ring-red-100 dark:ring-red-900"></span>
                        <span>Loss (Increase)</span>
                    </div>
                </div>
                <button type="button" class="close-modal-button w-full sm:w-auto px-8 py-3 text-[10px] font-bold text-white bg-primary-600 hover:bg-primary-700 rounded-xs transition-all uppercase tracking-widest">
                    <i class="fa-solid fa-times mr-2"></i> Close Analysis
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(function() {
    const table = window.defaultDataTable('#vaveTable', {
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("inventory.vave.data") }}',
            data: function(d) {
                d.customer_id = $('#filterCustomer').val();
                d.model_id = $('#filterModel').val();
            }
        },
        columns: [
            { data: 'id', className: 'text-center', render: (d, t, r, m) => m.row + 1 },
            { data: 'part_no', className: 'font-medium' },
            { data: 'part_name' },
            { data: 'customer_code', className: 'text-center' },
            { data: 'model_name', className: 'text-center' },
            { 
                data: 'baseline_weight', 
                className: 'text-center font-mono',
                render: d => d ? parseFloat(d).toFixed(3) : '<span class="text-gray-400">-</span>'
            },
            { 
                data: 'latest_weight', 
                className: 'text-center font-mono',
                render: d => d ? parseFloat(d).toFixed(3) : '<span class="text-gray-400">-</span>'
            },
            { 
                data: 'status', 
                className: 'text-center',
                render: (d, t, r) => {
                    if (d === 'MERIT') {
                        return `<div class="flex flex-col items-center gap-1">
                            <span class="px-3 py-1 text-[9px] font-black rounded-xs bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 uppercase tracking-[0.1em]">MERIT</span>
                            <span class="text-[9px] text-emerald-600 dark:text-emerald-500 font-bold tracking-tight italic">${r.diff_pct.toFixed(1)}% Saving</span>
                        </div>`;
                    } else if (d === 'LOSS') {
                        return `<div class="flex flex-col items-center gap-1">
                            <span class="px-3 py-1 text-[9px] font-black rounded-xs bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-400 border border-red-200 dark:border-red-800 uppercase tracking-[0.1em]">LOSS</span>
                            <span class="text-[9px] text-red-600 dark:text-red-500 font-bold tracking-tight italic">${r.diff_pct.toFixed(1)}% Loss</span>
                        </div>`;
                    } else {
                        return `<span class="px-3 py-1 text-[9px] font-black rounded-xs bg-slate-50 text-slate-500 dark:bg-gray-800 dark:text-gray-400 border border-slate-200 dark:border-gray-700 uppercase tracking-[0.1em]">NO DATA</span>`;
                    }
                }
            },
            {
                data: null,
                orderable: false,
                render: row => `
                    <div class="flex items-center justify-center gap-1.5">
                        <button class="rfq-button h-8 px-4 inline-flex items-center gap-2 text-primary-600 bg-primary-50 dark:bg-primary-900/20 dark:text-primary-400 border border-primary-100 dark:border-primary-800 rounded-xs hover:bg-primary-100 dark:hover:bg-primary-900/30 transition-all font-bold text-[10px] uppercase tracking-widest active:scale-95" data-id="${row.hash_id}" title="Manage Baseline (RFQ)">
                            <i class="fa-solid fa-pen-to-square"></i> RFQ
                        </button>
                        <button class="compare-button h-8 px-4 inline-flex items-center gap-2 text-purple-600 bg-purple-50 dark:bg-purple-900/20 dark:text-purple-400 border border-purple-100 dark:border-purple-800 rounded-xs hover:bg-purple-100 dark:hover:bg-purple-900/30 transition-all font-bold text-[10px] uppercase tracking-widest active:scale-95 ${!row.has_rfq ? 'opacity-30 grayscale cursor-not-allowed' : ''}" data-id="${row.hash_id}" ${!row.has_rfq ? 'disabled' : ''} title="VAVE Analysis Comparison">
                            <i class="fa-solid fa-chart-line"></i> Analysis
                        </button>
                    </div>`
            }
        ]
    });

    // Populate Master Filters
    function loadMainFilters() {
        $.get('{{ route("inventory.master.product.getCustomers") }}', function(data) {
            data.forEach(c => {
                $('#filterCustomer').append(`<option value="${c.id}">${c.code}</option>`);
            });
        });

        $('#filterCustomer').on('change', function() {
            const customerId = $(this).val();
            $('#filterModel').empty().append('<option value="">All Models</option>');
            
            $.get('{{ route("inventory.master.product.getModels") }}', { customer_id: customerId }, function(data) {
                data.forEach(m => {
                    $('#filterModel').append(`<option value="${m.id}">${m.name}</option>`);
                });
                table.ajax.reload();
            });
        });

        $('#filterModel').on('change', function() {
            table.ajax.reload();
        });

        $('#btnResetFilter').on('click', function() {
            $('#filterCustomer').val('').trigger('change');
            $('#filterModel').val('').trigger('change');
            table.ajax.reload();
        });
        
        $('.select2-simple').select2({
            width: '100%',
            placeholder: 'Select...',
            allowClear: true
        });

        $('.select2-rfq').select2({
            dropdownParent: $('#rfqModal'),
            width: '100%',
            placeholder: 'Select...',
        });
    }

    loadMainFilters();

    let vaveDropdownData = {};

    // Handle Export Summary
    $('#btnExportSummary').on('click', function() {
        const customerId = $('#filterCustomer').val();
        const modelId = $('#filterModel').val();
        let url = '{{ route("inventory.vave.exportSummary") }}';
        let params = [];
        if (customerId) params.push(`customer_id=${customerId}`);
        if (modelId) params.push(`model_id=${modelId}`);
        if (params.length > 0) url += '?' + params.join('&');
        
        window.location.href = url;
    });

    // Populate Dropdown Data for RFQ Form
    function loadRfqDropdowns() {
        $.get('{{ route("inventory.master.product.dropdownData") }}', function(data) {
            vaveDropdownData = data;
            
            // Clear and populate
            $('#rfq_material_spec_id').empty().append('<option value="">Select Spec</option>');
            $('#rfq_unit_id').empty().append('<option value="">Select Unit</option>');
            
            data.materialSpecs.forEach(ms => {
                $('#rfq_material_spec_id').append(`<option value="${ms.hash_id}">${ms.spec_name}</option>`);
            });
            
            data.units.forEach(u => {
                $('#rfq_unit_id').append(`<option value="${u.hash_id}">${u.code} - ${u.name}</option>`);
            });
        });
    }
    loadRfqDropdowns();

    // Toggle Unit fields visibility for RFQ
    function toggleRfqUnitFields() {
        const unitId = $('#rfq_unit_id').val();
        const selectedUnit = vaveDropdownData.units ? vaveDropdownData.units.find(u => u.hash_id === unitId) : null;
        const unitName = selectedUnit ? selectedUnit.name.toLowerCase() : '';

        // Reset all visibility first
        $('#rfq_length_container').hide();
        $('#rfq_length2_container').hide();
        $('#rfq_pitch_container').hide();

        // Logic Visibility
        if (unitName.includes('sheet')) {
            // Sheet: Show Length, Hide L2 & Pitch
            $('#label_rfq_length').text('Length (mm)');
            $('#rfq_length_container').show();
            $('#rfq_length2_container').hide();
            $('#rfq_pitch_container').hide();
        } else if (unitName.includes('trapezoid')) {
            // Trapezoid: Show Length, Length 2. Hide Pitch.
            $('#label_rfq_length').text('Length 1 (L1)');
            $('#rfq_length_container').show();
            $('#rfq_length2_container').show();
            $('#rfq_pitch_container').hide();
        } else if (unitName.includes('coil')) {
            // Coil: Show Pitch. Hide Length, Length 2.
            $('#rfq_length_container').hide();
            $('#rfq_length2_container').hide();
            $('#rfq_pitch_container').show();
        } else {
            // Default if unknown or empty: Show Length.
            $('#label_rfq_length').text('Length (mm)');
            $('#rfq_length_container').show();
        }

        // Trigger calculation when unit changes
        calculateRfqWeight();
    }

    $('#rfq_unit_id').on('change', function() {
        toggleRfqUnitFields();
    });

    // Auto-calculate RFQ weight
    function calculateRfqWeight() {
        const unitId = $('#rfq_unit_id').val();
        const selectedUnit = vaveDropdownData.units ? vaveDropdownData.units.find(u => u.hash_id === unitId) : null;
        const unitName = selectedUnit ? selectedUnit.name.toLowerCase() : '';

        const t = parseFloat($('#rfq_thickness').val()) || 0;
        const w = parseFloat($('#rfq_width').val()) || 0;
        const density = parseFloat($('#rfq_density').val()) || 0;

        let weight = 0;
        if (unitName.includes('sheet')) {
            const l = parseFloat($('#rfq_length').val()) || 0;
            weight = (t * w * l * density) / 1000000;
        } else if (unitName.includes('coil')) {
            const p = parseFloat($('#rfq_pitch').val()) || 0;
            weight = (t * w * p * density) / 1000000;
        } else if (unitName.includes('trapezoid')) {
            const l = parseFloat($('#rfq_length').val()) || 0;
            const l2 = parseFloat($('#rfq_length_2').val()) || 0;
            const avgL = (l + l2) / 2;
            weight = (t * w * avgL * density) / 1000000;
        } else {
            const l = parseFloat($('#rfq_length').val()) || 0;
            weight = (t * w * l * density) / 1000000;
        }

        $('#rfq_weight_kg').val(weight > 0 ? weight.toFixed(3) : '0.000');
    }

    $('#rfqForm input').on('input change', calculateRfqWeight);


    // Variables
    window.rfqHistory = [];
    window.rfqRevisions = [];
    window.latestRevision = null;

    // Helper to auto-fill specs from latest revision
    function resetAndAutoFillRfqForm() {
        // 1. Clear all inputs first
        $('#rfq_id').val('');
        $('#rfq_thickness').val('');
        $('#rfq_width').val('');
        $('#rfq_length').val('');
        $('#rfq_length_2').val('');
        $('#rfq_pitch').val('');
        $('#rfq_density').val(7.85); 
        $('#rfq_net_weight').val('');
        $('#remark').val('');
        $('#rfq_material_spec_id').val('').trigger('change'); // Clear spec
        $('#rfq_unit_id').val('').trigger('change'); // Clear unit and reset visibility
        $('#rfq_material_price').val('20000');

        // 2. Auto-fill from latest revision if available
        if (window.latestRevision) {
            // Safe check for dropdown data
            if (window.latestRevision.material_spec) {
                $('#rfq_material_spec_id').val(window.latestRevision.material_spec.hash_id).trigger('change');
            }
            if (window.latestRevision.unit) {
                $('#rfq_unit_id').val(window.latestRevision.unit.hash_id).trigger('change');
            }
        }
        
        calculateRfqWeight(); // Should result in 0
    }

    // Helper to load RFQ data to form
    function loadRfqToForm(data) {
        $('#rfq_id').val(data.hash_id); // Set ID for update
        $('#rfq_name').val(data.rfq_name);
        
        // Fix: Use hash_id from relation instead of integer ID which won't match dropdown values
        let specId = (data.material_spec ? data.material_spec.hash_id : '');
        
        // Fallback to latest revision if baseline itself doesn't have it
        if (!specId && window.latestRevision && window.latestRevision.material_spec) {
            specId = window.latestRevision.material_spec.hash_id;
        }
        $('#rfq_material_spec_id').val(specId).trigger('change');

        let unitId = (data.unit ? data.unit.hash_id : '');
        if (!unitId && window.latestRevision && window.latestRevision.unit) {
            unitId = window.latestRevision.unit.hash_id;
        }
        $('#rfq_unit_id').val(unitId).trigger('change');
        
        $('#rfq_thickness').val(parseFloat(data.thickness || 0));
        $('#rfq_width').val(parseFloat(data.width || 0));
        $('#rfq_length').val(parseFloat(data.length || 0));
        $('#rfq_length_2').val(parseFloat(data.length_2 || 0));
        $('#rfq_pitch').val(parseFloat(data.pitch || 0));
        $('#rfq_density').val(parseFloat(data.density || 7.85));
        $('#rfq_net_weight').val(parseFloat(data.net_weight || 0));
        $('#rfq_material_price').val(parseFloat(data.material_price || 20000));
        $('#remark').val(data.remark);
        calculateRfqWeight();
    }

    // Handle RFQ Management
    $(document).on('click', '.rfq-button', function() {
        const id = $(this).data('id');
        $('#rfqForm')[0].reset();
        $('#rfq_product_id').val(id);
        $('#rfq_id').val(''); 
        $('#btn_new_baseline').addClass('hidden');
        $('#btn_delete_baseline').addClass('hidden');
        
        $.get(`{{ url('inventory/vave/rfq') }}/${id}`, function(res) {
            $('#rfqModalTitle').text(`Manage Baseline (RFQ) - ${res.product.part_no}`);
            
            // Store Revisions
            window.rfqRevisions = res.revisions;
            
            // Determines Latest Revision
            if (res.revisions && res.revisions.length > 0) {
                 window.latestRevision = res.revisions[0];
            } else {
                 window.latestRevision = null;
            }

            // Populate History Dropdown
            const histSelect = $('#rfq_history_select').empty().append('<option value="">-- Load Existing Baseline --</option>');
            if(res.rfqHistory && res.rfqHistory.length > 0) {
                 res.rfqHistory.forEach((h, index) => {
                    const activeLabel = h.is_active ? ' (Active)' : '';
                    const isSelected = index === 0 ? 'selected' : '';
                    histSelect.append(`<option value="${h.hash_id}" ${isSelected}>${h.rfq_name || 'Baseline'} - ${parseFloat(h.weight_kg).toFixed(3)}kg${activeLabel}</option>`);
                });
            }
            window.rfqHistory = res.rfqHistory;

            // Load logic
            if (res.rfq) {
                loadRfqToForm(res.rfq);
                $('#btn_new_baseline').removeClass('hidden'); 
                $('#btn_delete_baseline').removeClass('hidden');
            } else {
                // New Baseline
                $('#rfq_name').val('Baseline 1');
                resetAndAutoFillRfqForm();
                $('#btn_delete_baseline').addClass('hidden');
            }

            // Update Active Display (The 'Previous' reference)
            if (res.rfq) {
                $('#active_baseline_display').text(`${res.rfq.rfq_name || 'Active'}`);
                $('#active_weight_display').text(`${parseFloat(res.rfq.weight_kg || 0).toFixed(3)} Kg`);
            } else {
                $('#active_baseline_display').text('No Baseline Set');
                $('#active_weight_display').text('-');
            }
            
            // Ensure UI state matches unit (safeguard)
            toggleRfqUnitFields();
            $('#rfqModal').removeClass('hidden').addClass('flex');
        });
    });

    // Handle History Selection
    $('#rfq_history_select').on('change', function() {
        const id = $(this).val();
        if(!id) return;
        const selected = window.rfqHistory ? window.rfqHistory.find(h => h.hash_id == id) : null;
        if (selected) {
             loadRfqToForm(selected);
             $('#btn_new_baseline').removeClass('hidden');
             $('#btn_delete_baseline').removeClass('hidden');
        }
    });
    
    // Create New Baseline
    $('#btn_new_baseline').on('click', function() {
        const currentData = {};
        // Capture current form state to use as base for new baseline
        currentData.rfq_material_spec_id = $('#rfq_material_spec_id').val();
        currentData.rfq_unit_id = $('#rfq_unit_id').val();
        currentData.thickness = $('#rfq_thickness').val();
        currentData.width = $('#rfq_width').val();
        currentData.length = $('#rfq_length').val();
        currentData.length_2 = $('#rfq_length_2').val();
        currentData.pitch = $('#rfq_pitch').val();
        currentData.density = $('#rfq_density').val();
        currentData.net_weight = $('#rfq_net_weight').val();
        currentData.material_price = $('#rfq_material_price').val();

        $('#btn_new_baseline').addClass('hidden');
        $('#btn_delete_baseline').addClass('hidden');
        $('#rfq_history_select').val('').trigger('change.select2');
        
        const count = (window.rfqHistory ? window.rfqHistory.length : 0) + 1;
        $('#rfq_name').val(`Baseline ${count}`);
        $('#rfq_id').val('');

        // Apply captured data
        $('#rfq_material_spec_id').val(currentData.rfq_material_spec_id).trigger('change');
        $('#rfq_unit_id').val(currentData.rfq_unit_id).trigger('change');
        $('#rfq_thickness').val(currentData.thickness);
        $('#rfq_width').val(currentData.width);
        $('#rfq_length').val(currentData.length);
        $('#rfq_length_2').val(currentData.length_2);
        $('#rfq_pitch').val(currentData.pitch);
        $('#rfq_density').val(currentData.density);
        $('#rfq_net_weight').val(currentData.net_weight);
        $('#rfq_material_price').val(currentData.material_price);
        
        calculateRfqWeight();
    });

    // Handle Delete Baseline
    $('#btn_delete_baseline').on('click', function() {
        const id = $('#rfq_id').val();
        if (!id) return;

        Swal.fire({
            title: 'Are you sure?',
            text: "This baseline will be permanently deleted. If this was the active baseline, the next most recent one will become active.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `{{ url('inventory/vave/rfq') }}/${id}`,
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(res) {
                        if (res.success) {
                            table.ajax.reload();
                            $('#rfqModal').addClass('hidden').removeClass('flex');
                            window.showToast(res.message, 'success');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON.message || 'Error deleting baseline', 'error');
                    }
                });
            }
        });
    });

    // Save RFQ Baseline
    $('#rfqForm').on('submit', function(e) {
        e.preventDefault();
        $.post('{{ route("inventory.vave.storeRfq") }}', $(this).serialize(), function(res) {
            if (res.success) {
                table.ajax.reload();
                $('#rfqModal').addClass('hidden').removeClass('flex');
                window.showToast(res.message, 'success');
            }
        });
    });

    // Global State untuk Comparison Data
    window.compareState = { id: null, rfqs: [], revisions: [] };

    // Handle VA/VE Comparison (Fetch Data & Isi Dropdown)
    $(document).on('click', '.compare-button', function() {
        const id = $(this).data('id');
        window.compareState.id = id;

        $.get(`{{ url('inventory/vave/comparison') }}/${id}`, function(res) {
            $('#comparisonTitle').text(`VA/VE Material Analysis History`);
            const customer = res.product.customer ? res.product.customer.customer_name : (res.product.customer_code || '-');
            $('#comparisonSubtitle').text(`${res.product.part_no} - ${res.product.part_name} (${customer})`);
            
            window.compareState.rfqs = res.rfqs || [];
            window.compareState.revisions = res.revisions || [];
            
            if (window.compareState.rfqs.length === 0) {
                $('#comparisonContainer').html('<div class="p-12 text-center text-gray-400"><i class="fa-solid fa-file-circle-exclamation text-4xl mb-4"></i><p>No baseline data found for this product.</p></div>');
                $('#comparisonModal').removeClass('hidden').addClass('flex');
                return;
            }

            // Isi Opsi Dropdown
            const baseSelect = $('#selectCompareBase').empty();
            const actualSelect = $('#selectCompareActual').empty();

            const defaultBase = window.compareState.rfqs.find(r => r.is_active) || window.compareState.rfqs[0];
            window.compareState.rfqs.forEach(r => {
                const isActive = r.is_active ? ' (Active)' : '';
                baseSelect.append(`<option value="${r.hash_id}" ${r.hash_id === defaultBase.hash_id ? 'selected' : ''}>${r.rfq_name}${isActive}</option>`);
            });

            if (window.compareState.revisions.length > 0) {
                const defaultRev = window.compareState.revisions[0];
                window.compareState.revisions.forEach(rev => {
                    const revCode = rev.revision ? rev.revision.code : '-';
                    const isSelected = rev.revision && defaultRev.revision && rev.revision.code === defaultRev.revision.code;
                    actualSelect.append(`<option value="${revCode}" ${isSelected ? 'selected' : ''}>Rev ${revCode}</option>`);
                });
            } else {
                actualSelect.append(`<option value="">No Revisions Found</option>`);
            }

            renderComparisonTable();
            $('#comparisonModal').removeClass('hidden').addClass('flex');
        });
    });

    // Handle Perubahan Dropdown
    $(document).on('change', '#selectCompareBase, #selectCompareActual', function() {
        renderComparisonTable();
        // Tampilkan ulang history jika toggle sedang nyala
        if ($('#toggleHistory').is(':checked')) { $('.history-col').removeClass('hidden'); }
    });

    // Fungsi Render Ulang Tabel
    function renderComparisonTable() {
        const id = window.compareState.id;
        const rfqs = window.compareState.rfqs;
        const revisions = window.compareState.revisions;

        const selectedBaseHash = $('#selectCompareBase').val();
        const selectedRevId = $('#selectCompareActual').val();

        // Gunakan data berdasarkan pilihan dropdown
        const benchmarkRfq = rfqs.find(r => r.hash_id == selectedBaseHash) || rfqs[0];
        const latestRevision = revisions.find(r => r.revision && r.revision.code == selectedRevId) || null;

        let summaryBar = '';
        if (benchmarkRfq && latestRevision) {
            const saving = benchmarkRfq.weight_kg - latestRevision.weight_kg;
            const savingPct = (saving / benchmarkRfq.weight_kg) * 100;
            
            let statusBadge = 'NO CHANGE';
            let colorClass = 'text-gray-700 bg-gray-50 border-gray-200';
            if (saving > 0.0001) {
                statusBadge = 'MERIT'; colorClass = 'text-green-700 bg-green-50 border-green-200';
            } else if (saving < -0.0001) {
                statusBadge = 'LOSS'; colorClass = 'text-red-700 bg-red-50 border-red-200';
            }
            
            summaryBar = `
                <div class="flex items-center justify-between gap-4 mb-4 px-1">
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-3 px-5 py-2.5 rounded-xs border ${colorClass}">
                            <span class="text-[10px] font-bold uppercase tracking-widest opacity-80">Net Impact</span>
                            <div class="h-4 w-px bg-current opacity-20"></div>
                            <span class="font-bold text-lg tracking-tighter italic">${Math.abs(savingPct).toFixed(2)}%</span>
                            <span class="text-[10px] font-bold opacity-80 uppercase tracking-widest ml-1">(${Math.abs(saving).toFixed(3)} kg)</span>
                            <span class="text-[9px] uppercase font-bold px-2 py-0.5 rounded-xs bg-white/40 ml-2 tracking-widest border border-white/20">${statusBadge}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <a href="{{ url('inventory/vave/comparison') }}/${id}/export" class="h-9 px-4 inline-flex items-center gap-2 text-white rounded-xs bg-primary-600 hover:bg-primary-700 transition-all font-bold text-[10px] uppercase tracking-widest active:scale-[0.98]">
                            <i class="fa-solid fa-file-excel text-sm"></i> Export Excel
                        </a>
                             <label class="inline-flex items-center cursor-pointer group px-3 py-1.5 rounded-xs hover:bg-slate-50 dark:hover:bg-gray-700/50 transition-colors border border-transparent hover:border-slate-100">
                            <div class="relative flex items-center">
                                <input type="checkbox" id="toggleHistory" class="sr-only peer">
                                <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
                            </div>
                            <span class="ms-3 text-[10px] font-black text-slate-500 group-hover:text-primary-600 transition-colors uppercase tracking-[0.2em] pointer-events-none">View History Tracking</span>
                        </label>
                    </div>
                </div>`;
        }

        let html = `
            ${summaryBar}
            <div class="overflow-x-auto custom-scrollbar rounded-xs border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-900 border-separate">
                <table id="comparisonTable" class="table-fixed min-w-full text-sm text-left border-collapse whitespace-nowrap">
                    <thead class="text-[10px] uppercase bg-slate-50 dark:bg-gray-800 sticky top-0 z-30">
                        <tr>
                            <th class="w-[160px] min-w-[160px] max-w-[160px] px-5 py-4 text-slate-400 font-black bg-slate-50 dark:bg-gray-800 border-b border-r border-slate-200 dark:border-gray-700 sticky left-0 z-40 text-left uppercase tracking-widest">PARAMETER</th>
        `;
        
        html += `<th class="w-[200px] min-w-[200px] max-w-[200px] px-5 py-4 text-center border-b border-r border-primary-200 dark:border-gray-700 bg-primary-50/50 dark:bg-primary-900/20 sticky top-0 z-40" style="left: 160px;">
                <div class="flex flex-col items-center">
                    <span class="text-[9px] text-primary-600 dark:text-primary-400 font-black tracking-[0.2em] mb-1">PLAN (BASE)</span>
                    <span class="font-black text-slate-800 dark:text-white truncate max-w-[180px] uppercase tracking-tighter" title="${benchmarkRfq.rfq_name}">${benchmarkRfq.rfq_name}</span>
                </div>
            </th>`;

        if (latestRevision) {
            html += `<th class="w-[200px] min-w-[200px] max-w-[200px] px-5 py-4 text-center border-b border-r border-emerald-200 dark:border-gray-700 bg-emerald-50/50 dark:bg-emerald-900/20 sticky top-0 z-40" style="left: 360px;">
                    <div class="flex flex-col items-center">
                        <span class="text-[9px] text-emerald-600 dark:text-emerald-400 font-black tracking-[0.2em] mb-1">ACTUAL (REV)</span>
                        <span class="font-black text-slate-800 dark:text-white uppercase tracking-tighter">Rev ${latestRevision.revision ? latestRevision.revision.code : '-'}</span>
                    </div>
                </th>`;
        } else {
             html += `<th class="w-[200px] min-w-[200px] px-5 py-4 bg-emerald-50/20 sticky top-0 z-40 border-b border-r border-emerald-100" style="left: 360px;">-</th>`;
        }

        html += `<th class="w-[130px] min-w-[130px] max-w-[130px] px-5 py-4 text-center border-b border-r border-slate-200 dark:border-gray-700 bg-slate-100 sticky top-0 z-40" style="left: 560px;">
                <div class="flex flex-col items-center">
                    <span class="text-[9px] text-slate-500 font-bold tracking-widest mb-1">VARIANCE (Δ)</span>
                    <span class="font-bold text-slate-600 uppercase tracking-tighter italic">Diff ±</span>
                </div>
            </th>`;

        rfqs.forEach(r => {
            if (r.hash_id === benchmarkRfq.hash_id) return;
            html += `<th class="w-[120px] min-w-[120px] px-4 py-3 text-center border-b border-r border-gray-200 dark:border-gray-700 bg-gray-50/50 history-col hidden border-dashed">
                    <div class="flex flex-col items-center opacity-60">
                        <span class="text-[9px] text-gray-400 font-bold">HISTORY (BASE)</span>
                        <span class="font-semibold text-gray-500 text-xs truncate max-w-[100px]">${r.rfq_name}</span>
                    </div>
                </th>`;
        });
        revisions.forEach(rev => {
            if (latestRevision && rev.revision === latestRevision.revision) return;
            html += `<th class="w-[120px] min-w-[120px] px-4 py-3 text-center border-b border-r border-gray-200 dark:border-gray-700 bg-gray-50/50 history-col hidden border-dashed">
                    <div class="flex flex-col items-center opacity-60">
                        <span class="text-[9px] text-gray-400 font-bold">HISTORY (REV)</span>
                        <span class="font-semibold text-gray-500 text-xs">Rev ${rev.revision ? rev.revision.code : '-'}</span>
                    </div>
                </th>`;
        });

        html += `</tr></thead><tbody class="divide-y divide-gray-200 dark:divide-gray-700">`;

        const buildRow = (label, valueFormatter) => {
            let row = `<tr class="hover:bg-primary-100 dark:hover:bg-primary-900/40 transition-colors duration-150 text-xs text-gray-700 dark:text-gray-300">`;
            row += `<td class="w-[160px] min-w-[160px] max-w-[160px] px-4 py-2 font-semibold sticky left-0 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 z-10 transition-colors group-hover:bg-primary-50">${label}</td>`;
            row += `<td class="w-[160px] min-w-[160px] max-w-[160px] px-4 py-2 text-center border-r border-gray-200 dark:border-gray-700 font-medium sticky z-30 bg-white dark:bg-gray-800 transition-colors group-hover:bg-primary-50" style="left: 160px;">${valueFormatter(benchmarkRfq)}</td>`;
            row += `<td class="w-[160px] min-w-[160px] max-w-[160px] px-4 py-2 text-center border-r border-gray-200 dark:border-gray-700 font-medium sticky z-30 bg-white dark:bg-gray-800 transition-colors group-hover:bg-primary-50" style="left: 320px;">${latestRevision ? valueFormatter(latestRevision) : '-'}</td>`;
            row += `<td class="w-[110px] min-w-[110px] max-w-[110px] px-4 py-2 text-center border-r border-gray-200 dark:border-gray-700 font-bold variance-cell sticky z-30 bg-gray-50 transition-colors group-hover:bg-primary-100" style="left: 480px;">-</td>`;
            rfqs.forEach(r => { if (r.hash_id !== benchmarkRfq.hash_id) row += `<td class="w-[120px] min-w-[120px] px-4 py-2 text-center border-r border-gray-200 dark:border-gray-700 text-gray-400 history-col hidden border-dashed">${valueFormatter(r)}</td>`; });
            revisions.forEach(rev => { if (!latestRevision || !rev.revision || !latestRevision.revision || rev.revision.code !== latestRevision.revision.code) row += `<td class="w-[120px] min-w-[120px] px-4 py-2 text-center border-r border-gray-200 dark:border-gray-200 text-gray-400 history-col hidden border-dashed">${valueFormatter(rev)}</td>`; });
            return row + `</tr>`;
        };

        const buildComputedRow = (label, valueGetter, unit = '', precision = 2, invertColor = false) => {
            const getVal = (item) => typeof valueGetter === 'function' ? valueGetter(item) : parseFloat(item[valueGetter] || 0);
            const baseVal = getVal(benchmarkRfq);
            let row = `<tr class="hover:bg-primary-100 dark:hover:bg-primary-900/40 transition-colors duration-150 text-xs group">
                <td class="w-[160px] min-w-[160px] max-w-[160px] px-4 py-2.5 font-semibold text-gray-700 dark:text-gray-300 sticky left-0 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 z-10 transition-colors group-hover:bg-primary-50">${label}</td>
                <td class="w-[160px] min-w-[160px] max-w-[160px] px-4 py-2.5 text-center border-r border-gray-200 dark:border-gray-700 font-mono text-gray-600 sticky z-30 bg-white dark:bg-gray-800 transition-colors group-hover:bg-primary-50" style="left: 160px;">${baseVal.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: precision})} ${unit}</td>`;

            let actualVal = 0;
            if (latestRevision) {
                actualVal = getVal(latestRevision);
                row += `<td class="w-[160px] min-w-[160px] max-w-[160px] px-4 py-2.5 text-center border-r border-gray-200 dark:border-gray-700 font-mono text-gray-800 font-bold bg-yellow-50 group-hover:bg-yellow-100 sticky z-30 transform-gpu" style="left: 320px;">${actualVal.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: precision})} ${unit}</td>`;
                const delta = actualVal - baseVal;
                const isGood = invertColor ? delta > 0 : delta <= 0;
                let cClass = 'text-gray-400', bClass = 'bg-gray-50'; 
                if (Math.abs(delta) > 0.0001) { cClass = isGood ? 'text-green-600' : 'text-red-600'; bClass = isGood ? 'bg-green-50' : 'bg-red-50'; }
                row += `<td class="w-[110px] min-w-[110px] max-w-[110px] px-4 py-2.5 text-center border-r border-gray-200 dark:border-gray-700 font-mono font-bold ${cClass} sticky z-30 ${bClass} transition-colors group-hover:bg-primary-100" style="left: 480px;">${delta > 0 ? '+' : ''}${delta.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: precision})} ${unit}</td>`;
            } else {
                row += `<td class="w-[160px] min-w-[160px] px-4 py-2.5 bg-white border-r border-gray-200 sticky z-30" style="left: 320px;">-</td><td class="w-[110px] min-w-[110px] px-4 py-2.5 bg-gray-50 border-r border-gray-200 sticky z-30" style="left: 480px;">-</td>`;
            }

            rfqs.forEach(r => { if (r.hash_id !== benchmarkRfq.hash_id) row += `<td class="w-[120px] min-w-[120px] px-4 py-2.5 text-center border-r border-gray-200 dark:border-gray-700 text-gray-400 history-col hidden border-dashed font-mono">${getVal(r).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: precision})}</td>`; });
            revisions.forEach(rev => { if (!latestRevision || !rev.revision || !latestRevision.revision || rev.revision.code !== latestRevision.revision.code) row += `<td class="w-[120px] min-w-[120px] px-4 py-2.5 text-center border-r border-gray-200 dark:border-gray-700 text-gray-400 history-col hidden border-dashed font-mono">${getVal(rev).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: precision})}</td>`; });
            return row + `</tr>`;
        };

        const buildSectionRow = (title) => {
            let row = `<tr class="bg-slate-100 dark:bg-gray-800/80 text-[10px] uppercase tracking-[0.2em] font-bold text-slate-500 dark:text-gray-400">
                <td class="w-[160px] min-w-[160px] max-w-[160px] px-5 py-3 sticky left-0 bg-slate-100 dark:bg-gray-800 md:z-20 border-r border-slate-200 dark:border-gray-700">${title}</td>
                <td class="w-[200px] min-w-[200px] max-w-[200px] px-4 py-3 bg-slate-100 dark:bg-gray-800 border-r border-slate-200 dark:border-gray-700 sticky md:z-20" style="left: 160px;"></td>
                <td class="w-[200px] min-w-[200px] max-w-[200px] px-4 py-3 bg-slate-100 dark:bg-gray-800 border-r border-slate-200 dark:border-gray-700 sticky md:z-20" style="left: 360px;"></td>
                <td class="w-[130px] min-w-[130px] max-w-[130px] px-4 py-3 bg-slate-100 dark:bg-gray-800 border-r border-slate-200 dark:border-gray-700 sticky md:z-20" style="left: 560px;"></td>`;
            rfqs.forEach(r => { if (r.hash_id !== benchmarkRfq.hash_id) row += `<td class="w-[120px] min-w-[120px] px-4 py-3 bg-slate-100 dark:bg-gray-800 history-col hidden border-dashed"></td>`; });
            revisions.forEach(rev => { if (!latestRevision || !rev.revision || !latestRevision.revision || rev.revision.code !== latestRevision.revision.code) row += `<td class="w-[120px] min-w-[120px] px-4 py-3 bg-slate-100 dark:bg-gray-800 history-col hidden border-dashed"></td>`; });
            return row + `</tr>`;
        };

        html += buildSectionRow('Specification');
        html += buildRow('Material Spec', item => item.material_spec ? item.material_spec.spec_name : '-');
        html += buildRow('Unit Type', item => item.unit ? item.unit.name : '-');
        html += buildRow('Dimensions', i => {
            const unt = (i.unit ? i.unit.name : '').toLowerCase();
            let d = `${parseFloat(i.thickness)} x ${parseFloat(i.width)}`;
            if (unt.includes('coil')) d += ` x ${parseFloat(i.pitch)} (P)`;
            else if (unt.includes('trapezoid')) d += ` x (${parseFloat(i.length)} + ${parseFloat(i.length_2)})/2`;
            else d += ` x ${parseFloat(i.length)}`;
            return d;
        });

        html += buildComputedRow('Thickness (mm)', 'thickness', '', 2, false); 
        html += buildComputedRow('Width (mm)', 'width', '', 2, false);

        const untCompare = (benchmarkRfq.unit ? benchmarkRfq.unit.name : '').toLowerCase();
        if (untCompare.includes('trapezoid')) {
            html += buildComputedRow('Length 1 (L1)', 'length', 'mm', 2, false);
            html += buildComputedRow('Length 2 (L2)', 'length_2', 'mm', 2, false);
        } else if (untCompare.includes('coil')) {
            html += buildComputedRow('Pitch (mm)', 'pitch', 'mm', 2, false);
        } else {
            html += buildComputedRow('Length (mm)', 'length', 'mm', 2, false);
        }
        
        html += buildSectionRow('Yield & Weight');
        html += buildComputedRow('Density', 'density', '', 3, true);
        html += buildComputedRow('Gross Weight (kg)', 'weight_kg', '', 3, false); 
        html += buildComputedRow('Net Weight/Part', 'net_weight', '', 3, false);
        html += buildComputedRow('Scrap (kg)', i => (parseFloat(i.weight_kg)||0) - (parseFloat(i.net_weight)||0), 'kg', 3, false); 
        html += buildComputedRow('Yield Ratio (%)', i => {
            const g = parseFloat(i.weight_kg)||0, n = parseFloat(i.net_weight)||0;
            return (g>0 && n>0) ? (n/g)*100 : 0;
        }, '%', 1, true); 
        
        html += buildSectionRow('Commercial');
        html += buildComputedRow('Price/kg (IDR)', i => parseFloat(i.material_price || 0), '', 0, false);
        html += buildComputedRow('Cost (IDR)', i => (parseFloat(i.weight_kg||0) * parseFloat(i.material_price || 0)), '', 0, false); 

        let statusRow = `<tr class="bg-gray-50/50 hover:bg-primary-100 dark:bg-gray-800 dark:hover:bg-primary-900/40 text-xs border-t-2 border-gray-200 dark:border-gray-600 group"><td class="w-[160px] min-w-[160px] max-w-[160px] px-4 py-3 sticky left-0 bg-gray-50 dark:bg-gray-800 border-r border-gray-300 z-10 font-bold uppercase group-hover:bg-primary-100">Status</td><td class="w-[160px] min-w-[160px] max-w-[160px] px-4 py-3 text-center border-r border-gray-300 bg-gray-50 dark:bg-gray-800 sticky z-30 group-hover:bg-primary-100" style="left: 160px;">-</td>`;
        let rateRow = `<tr class="bg-white hover:bg-primary-100 dark:bg-gray-800 dark:hover:bg-primary-900/40 text-xs group"><td class="w-[160px] min-w-[160px] max-w-[160px] px-4 py-3 sticky left-0 bg-white dark:bg-gray-800 border-r border-gray-300 z-10 font-bold uppercase group-hover:bg-primary-100">Rate</td><td class="w-[160px] min-w-[160px] max-w-[160px] px-4 py-3 text-center border-r border-gray-300 bg-white dark:bg-gray-800 sticky z-30 group-hover:bg-primary-100" style="left: 160px;">-</td>`;

        if (latestRevision) {
            const saving = benchmarkRfq.weight_kg - latestRevision.weight_kg;
            const savingPct = (saving / benchmarkRfq.weight_kg) * 100;
            let sText = 'NO CHANGE', sColor = 'text-gray-700 font-bold bg-gray-100', rColor = 'text-gray-700'; 
            if (saving > 0.0001) { sText = 'MERIT'; sColor = 'text-green-700 bg-green-50'; rColor = 'text-green-700'; }
            else if (saving < -0.0001) { sText = 'LOSS'; sColor = 'text-red-700 bg-red-50'; rColor = 'text-red-700'; }
            
            statusRow += `<td class="w-[160px] min-w-[160px] px-4 py-3 text-center border-r border-gray-300 ${sColor} font-bold sticky z-30 group-hover:bg-opacity-90" style="left: 320px;">${sText}</td><td class="w-[110px] min-w-[110px] px-4 py-3 text-center border-r border-gray-300 bg-gray-100 text-gray-400 sticky z-30 group-hover:bg-primary-100" style="left: 480px;">-</td>`;
            rateRow += `<td class="w-[160px] min-w-[160px] px-4 py-3 text-center border-r border-gray-300 font-bold ${rColor} sticky z-30 bg-white dark:bg-gray-800 group-hover:bg-primary-100" style="left: 320px;">${Math.abs(savingPct).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 2})}%</td><td class="w-[110px] min-w-[110px] px-4 py-3 text-center border-r border-gray-300 bg-gray-100 text-gray-400 sticky z-30 group-hover:bg-primary-100" style="left: 480px;">-</td>`;
        } else {
            statusRow += `<td class="w-[160px] min-w-[160px] px-4 py-3 bg-gray-50 border-r border-gray-300 sticky z-30" style="left: 320px;">-</td><td class="w-[110px] min-w-[110px] px-4 py-3 bg-gray-100 sticky z-30" style="left: 480px;">-</td>`;
            rateRow += `<td class="w-[160px] min-w-[160px] px-4 py-3 bg-white border-r border-gray-300 sticky z-30" style="left: 320px;">-</td><td class="w-[110px] min-w-[110px] px-4 py-3 bg-gray-100 sticky z-30" style="left: 480px;">-</td>`;
        }

        const buildHistStatus = (item) => {
            const baseW = parseFloat(benchmarkRfq.weight_kg) || 0;
            const itemW = parseFloat(item.weight_kg) || 0;
            if (baseW <= 0 || itemW <= 0) {
                statusRow += `<td class="w-[120px] min-w-[120px] px-4 py-3 text-center bg-gray-50 history-col hidden border-dashed text-gray-400">-</td>`;
                rateRow += `<td class="w-[120px] min-w-[120px] px-4 py-3 text-center bg-white history-col hidden border-dashed text-gray-400">-</td>`;
                return;
            }
            const hSaving = baseW - itemW, hPct = (hSaving / baseW) * 100;
            let hText = 'NO CHANGE', hCol = 'text-gray-400';
            if (hSaving > 0.0001) { hText = 'MERIT'; hCol = 'text-green-600'; }
            else if (hSaving < -0.0001) { hText = 'LOSS'; hCol = 'text-red-600'; }
            statusRow += `<td class="w-[120px] min-w-[120px] px-4 py-3 text-center font-bold ${hCol} bg-gray-50 history-col hidden border-dashed text-[10px] tracking-wider">${hText}</td>`;
            rateRow += `<td class="w-[120px] min-w-[120px] px-4 py-3 text-center font-bold ${hCol} bg-white history-col hidden border-dashed">${Math.abs(hPct).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 2})}%</td>`;
        };

        rfqs.forEach(r => { if (r.hash_id !== benchmarkRfq.hash_id) buildHistStatus(r); });
        revisions.forEach(rev => { if (!latestRevision || !rev.revision || !latestRevision.revision || rev.revision.code !== latestRevision.revision.code) buildHistStatus(rev); });
        
        statusRow += `</tr>`; rateRow += `</tr>`;
        html += statusRow + rateRow + `</tbody></table></div>`;
        
        $('#comparisonContainer').html(html);

        $('#toggleHistory').off().on('change', function() {
            if(this.checked) $('.history-col').removeClass('hidden');
            else $('.history-col').addClass('hidden');
        });
    }

    function renderDetailItem(label, value) {
        return `
            <div>
                <p class="text-[10px] text-gray-400 font-bold uppercase leading-none mb-1">${label}</p>
                <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">${value}</p>
            </div>
        `;
    }

    function getOrdinal(n) {
        let s = ["th", "st", "nd", "rd"], v = n % 100;
        return s[(v - 20) % 10] || s[v] || s[0];
    }

    $('.close-modal-button').on('click', function() {
        $(this).closest('[tabindex="-1"]').addClass('hidden').removeClass('flex');
    });

});
</script>
<style>
    /* Custom Scrollbar Styles */
    .font-mono { font-family: 'JetBrains Mono', 'Fira Code', monospace; }
    .custom-scrollbar::-webkit-scrollbar { height: 8px; width: 8px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 20px; border: 2px solid transparent; background-clip: content-box; }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; }
</style>
@endpush
