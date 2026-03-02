@extends('layouts.app')
@section('title', 'VA/VE Analysis')
@section('page_title', 'Material Efficiency Analysis')
@section('header-title', 'VA/VE Analysis')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 text-gray-900 dark:text-gray-100">
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl tracking-tighter">VA/VE Analysis</h2>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-medium">Compare baseline RFQ data with production revisions to analyze material efficiency.</p>
    </div>

    {{-- Filter Card --}}
    <div class="mb-6 p-5 bg-white dark:bg-gray-800 rounded-lg border border-slate-200 dark:border-gray-700">
        <div class="flex flex-col md:flex-row items-end gap-5">
            <div class="w-full md:w-64">
                <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Customer</label>
                <select id="filterCustomer" class="select2-simple w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                    <option value="">All Customers</option>
                </select>
            </div>
            <div class="w-full md:w-64">
                <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Model</label>
                <select id="filterModel" class="select2-simple w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                    <option value="">All Models</option>
                </select>
            </div>
            <div class="flex items-center gap-3 ml-auto w-full md:w-auto mt-4 md:mt-0">
                <button type="button" id="btnResetFilter" class="flex-1 md:flex-none px-4 py-2.5 text-xs font-bold text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors flex items-center justify-center gap-2 uppercase tracking-wider">
                    <i class="fa-solid fa-rotate-left"></i> Reset
                </button>
                <div class="hidden md:block h-8 w-px bg-gray-100 dark:bg-gray-700 mx-1"></div>
                <button type="button" id="btnExportSummary" class="flex-1 md:flex-none inline-flex items-center justify-center px-6 py-2.5 bg-slate-900 hover:bg-slate-800 text-white text-[10px] font-bold uppercase tracking-widest rounded-md transition-all gap-2">
                    <i class="fa-solid fa-file-excel text-sm"></i> Export Summary
                </button>
            </div>
        </div>
    </div>

    <x-table id="vaveTable">
        <thead>
            <tr>
                <th scope="col" class="px-6 py-3 w-16 text-center text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">No</th>
                <th scope="col" class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400 w-48 min-w-[180px]">Part No</th>
                <th scope="col" class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Part Name</th>
                <th scope="col" class="px-6 py-3 text-center text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Customer</th>
                <th scope="col" class="px-6 py-3 text-center text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Model</th>
                <th scope="col" class="px-6 py-3 text-center text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Baseline (Kg)</th>
                <th scope="col" class="px-6 py-3 text-center text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Latest (Kg)</th>
                <th scope="col" class="px-6 py-3 text-center text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Analysis Status</th>
                <th scope="col" class="px-6 py-3 text-center w-[180px] text-[10px] font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Action</th>
            </tr>
        </thead>
        <tbody></tbody>
    </x-table>
</div>

{{-- RFQ Management Modal --}}
<div id="rfqModal" tabindex="-1" aria-hidden="true" class="hidden fixed inset-0 z-50 justify-center items-center w-full h-full bg-slate-900/50 backdrop-blur-sm">
    <div class="relative p-4 w-full max-w-4xl h-full md:h-auto">
        <div class="relative bg-white rounded-lg border border-slate-200 dark:border-gray-700 dark:bg-gray-800 flex flex-col max-h-[90vh] overflow-hidden">
            <button type="button" class="close-modal-button text-gray-400 absolute top-3 right-3 bg-transparent hover:bg-gray-100 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-700 dark:hover:text-white z-10 transition-colors">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
            
            <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 bg-slate-50/50 dark:bg-slate-900/30">
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
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 p-1 bg-white dark:bg-gray-700/50 rounded-lg border border-slate-200 dark:border-gray-700">
                            <div class="flex-[2] p-3">
                                <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">
                                    Baseline Version
                                </label>
                                <div class="flex gap-2">
                                    <select id="rfq_history_select" class="select2-rfq flex-1 bg-gray-50 border border-gray-300 text-gray-900 text-xs font-semibold rounded-md focus:ring-slate-500 focus:border-slate-500 block p-2.5 h-10 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all">
                                        <option value="">-- Load/Select Version --</option>
                                    </select>
                                    <button type="button" id="btn_new_baseline" class="py-2 px-4 text-xs font-bold text-blue-600 bg-blue-50 border border-blue-200 rounded-md hover:bg-blue-100 focus:outline-none dark:bg-gray-800 dark:text-blue-400 dark:border-gray-600 dark:hover:bg-gray-700 hidden transition-all uppercase tracking-wider" title="Create New Draft">
                                        <i class="fa-solid fa-plus mr-1"></i> New
                                    </button>
                                    <button type="button" id="btn_delete_baseline" class="py-2 px-4 text-xs font-bold text-red-600 bg-red-50 border border-red-200 rounded-md hover:bg-red-100 focus:outline-none dark:bg-gray-800 dark:text-red-400 dark:border-gray-600 dark:hover:bg-gray-700 hidden transition-all uppercase tracking-wider" title="Delete This Baseline">
                                        <i class="fa-solid fa-trash-can mr-1"></i> Delete
                                    </button>
                                </div>
                            </div>
                            <div class="hidden md:block w-px bg-gray-200 h-16 mx-2"></div>
                            <div class="flex-1 p-2 flex flex-col md:items-end justify-center">
                                <span class="text-[9px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1.5">Active Baseline Status</span>
                                <div class="flex flex-col gap-1 w-full md:w-auto min-w-[220px]">
                                    <div id="active_baseline_display" class="text-[10px] font-bold text-slate-700 dark:text-gray-200 px-3 py-1 bg-amber-50 dark:bg-amber-900/10 rounded border border-amber-100 dark:border-amber-800/50 text-center">
                                        -
                                    </div>
                                    <div id="active_weight_display" class="text-[10px] font-bold text-blue-600 dark:text-blue-400 px-3 py-1 bg-blue-50/30 dark:bg-blue-900/10 rounded border border-blue-100/50 dark:border-blue-800/50 text-center">
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
                                    <input type="text" name="rfq_name" id="rfq_name" readonly class="bg-gray-50 border border-slate-200 text-gray-400 text-xs font-semibold rounded-md block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all">
                                    <input type="hidden" name="is_active" value="1">
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Material Spec</label>
                                        <select name="material_spec_id" id="rfq_material_spec_id" class="select2-rfq bg-white border border-slate-200 text-gray-900 text-xs font-semibold rounded-md focus:ring-slate-500 focus:border-slate-500 block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all">
                                            <option value="">Select Spec</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Unit Type</label>
                                        <select name="unit_id" id="rfq_unit_id" class="select2-rfq bg-white border border-slate-200 text-gray-900 text-xs font-semibold rounded-md focus:ring-slate-500 focus:border-slate-500 block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all">
                                            <option value="">Select Unit</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="space-y-2">
                                    <label for="remark" class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Remark / Notes</label>
                                    <textarea name="remark" id="remark" rows="3" class="block p-3 w-full text-xs font-medium text-gray-900 bg-white rounded-md border border-slate-200 focus:ring-slate-500 focus:border-slate-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all placeholder-gray-300" placeholder="Optional notes..."></textarea>
                                </div>
                            </div>

                            {{-- Parameters & Calculations --}}
                            <div class="space-y-6">
                                <div class="p-5 bg-slate-50 dark:bg-gray-700/50 rounded-lg border border-slate-200 dark:border-gray-600 relative">
                                    <h5 class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-widest mb-4 flex items-center absolute -top-3 left-4 bg-white dark:bg-gray-800 px-2 border border-slate-100 dark:border-gray-600 rounded">
                                        <i class="fa-solid fa-ruler-combined mr-2 text-blue-500"></i>
                                        Dimensions (mm)
                                    </h5>
                                    
                                    <div class="grid grid-cols-2 gap-4 mt-2">
                                         <div>
                                            <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Thickness</label>
                                            <input type="number" step="0.01" name="thickness" id="rfq_thickness" class="bg-white border border-slate-200 text-gray-900 text-xs font-semibold rounded-md focus:ring-slate-500 focus:border-slate-500 block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="0.00">
                                        </div>
                                        <div>
                                            <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Width</label>
                                            <input type="number" step="0.01" name="width" id="rfq_width" class="bg-white border border-slate-200 text-gray-900 text-xs font-semibold rounded-md focus:ring-slate-500 focus:border-slate-500 block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="0.00">
                                        </div>
                                        <div id="rfq_length_container">
                                            <label class="block mb-2 text-[10px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em]" id="label_rfq_length">Length</label>
                                            <input type="number" step="0.01" name="length" id="rfq_length" class="bg-white border border-slate-200 text-gray-900 text-xs font-semibold rounded-md focus:ring-slate-500 focus:border-slate-500 block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="0.00">
                                        </div>
                                        <div id="rfq_length2_container" class="hidden">
                                            <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Length 2 (L2)</label>
                                            <input type="number" step="0.01" name="length_2" id="rfq_length_2" class="bg-white border border-slate-200 text-gray-900 text-xs font-semibold rounded-md focus:ring-slate-500 focus:border-slate-500 block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="0.00">
                                        </div>
                                        <div id="rfq_pitch_container" class="hidden">
                                            <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Pitch (mm)</label>
                                            <input type="number" step="0.01" name="pitch" id="rfq_pitch" class="bg-white border border-slate-200 text-gray-900 text-xs font-semibold rounded-md focus:ring-slate-500 focus:border-slate-500 block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="0.00">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Density</label>
                                        <input type="number" step="0.001" name="density" id="rfq_density" value="7.85" class="bg-white border border-slate-200 text-gray-900 text-xs font-semibold rounded-md focus:ring-slate-500 focus:border-slate-500 block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all" placeholder="7.850">
                                    </div>
                                    <div>
                                        <label class="block mb-2 text-[10px] font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wider">Gross Weight (Kg)</label>
                                        <input type="number" step="0.001" name="weight_kg" id="rfq_weight_kg" readonly class="bg-blue-50 border border-blue-100 text-blue-600 text-xs font-bold rounded-md block w-full h-10 px-3 dark:bg-blue-900/20 dark:border-blue-800 dark:text-blue-300 cursor-not-allowed" placeholder="0.000">
                                    </div>
                                    <div>
                                        <label class="block mb-2 text-[10px] font-semibold text-slate-600 dark:text-gray-300 uppercase tracking-wider">Net Weight (Kg)</label>
                                        <input type="number" step="0.001" name="net_weight" id="rfq_net_weight" class="bg-white border border-slate-200 text-gray-900 text-xs font-semibold rounded-md focus:ring-slate-500 focus:border-slate-500 block w-full h-10 px-3 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all" placeholder="0.000">
                                    </div>
                                    <div>
                                        <label class="block mb-2 text-[10px] font-semibold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider">Price (Rp/Kg)</label>
                                        <input type="number" step="0.01" name="material_price" id="rfq_material_price" value="20000" class="bg-emerald-50 border border-emerald-100 text-emerald-700 text-xs font-bold rounded-md focus:ring-emerald-500 focus:border-emerald-500 block w-full h-10 px-3 dark:bg-emerald-900/20 dark:border-emerald-800 dark:text-emerald-200 transition-all" placeholder="0.00">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 px-8 py-5 border-t border-gray-100 dark:border-gray-700 bg-slate-50/50 dark:bg-slate-900/30">
                    <button type="button" class="close-modal-button text-gray-700 bg-white hover:bg-gray-50 focus:ring-4 focus:outline-none focus:ring-gray-100 rounded-md border border-gray-300 text-[10px] font-bold uppercase tracking-wider px-6 py-3 transition-all">
                        Cancel
                    </button>
                    <button type="submit" class="text-white bg-slate-900 hover:bg-slate-800 focus:ring-4 focus:outline-none focus:ring-slate-200 font-bold rounded-md text-[10px] uppercase tracking-widest px-6 py-3 text-center transition-all">
                        <i class="fa-solid fa-save mr-1.5"></i> Save Baseline
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Scripts for RFQ History --}}
{{-- Scripts for RFQ History Moved to @push('scripts') --}}

{{-- Comparison Modal --}}
<div id="comparisonModal" tabindex="-1" aria-hidden="true" class="hidden fixed inset-0 z-50 flex items-center justify-center w-full h-full bg-slate-900/50 backdrop-blur-sm p-4">
    <div class="relative w-full max-w-6xl max-h-[90vh]">
        <div class="relative text-left bg-white rounded-lg border border-slate-200 dark:border-gray-700 dark:bg-gray-800 flex flex-col max-h-[90vh] overflow-hidden">
            <button type="button" class="close-modal-button text-gray-400 absolute top-4 right-4 bg-white dark:bg-gray-700 hover:bg-gray-50 hover:text-gray-900 rounded-md text-sm w-8 h-8 inline-flex items-center justify-center dark:hover:bg-gray-600 dark:hover:text-white z-20 transition-all border border-gray-200 dark:border-gray-600">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
            
            <div class="p-6 border-b border-gray-100 dark:border-gray-700 pr-14 bg-slate-50/50 dark:bg-slate-900/30">
                <h3 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-3 uppercase tracking-tight" id="comparisonTitle">
                    <i class="fa-solid fa-chart-line text-blue-600 dark:text-blue-400"></i>
                    VA/VE Material Analysis
                </h3>
                <p id="comparisonSubtitle" class="text-xs text-gray-500 dark:text-gray-400 mt-1 font-medium ml-8"></p>
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
                <button type="button" class="close-modal-button w-full sm:w-auto px-8 py-3 text-[10px] font-bold text-white bg-slate-900 hover:bg-slate-800 rounded-md transition-all uppercase tracking-widest">
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
                        return `<div class="flex flex-col items-center">
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-400 border border-green-200 dark:border-green-800 uppercase tracking-wider">MERIT</span>
                            <span class="text-[10px] text-green-600 dark:text-green-500 mt-1 font-bold tracking-tight">${r.diff_pct.toFixed(1)}% Improvement</span>
                        </div>`;
                    } else if (d === 'LOSS') {
                        return `<div class="flex flex-col items-center">
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-400 border border-red-200 dark:border-red-800 uppercase tracking-wider">LOSS</span>
                            <span class="text-[10px] text-red-600 dark:text-red-500 mt-1 font-bold tracking-tight">${r.diff_pct.toFixed(1)}% Increase</span>
                        </div>`;
                    } else {
                        return `<span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400 border border-gray-200 dark:border-gray-700 uppercase tracking-wider">${d}</span>`;
                    }
                }
            },
            {
                data: null,
                orderable: false,
                render: row => `
                    <div class="inline-flex rounded-md shadow-sm" role="group">
                        <button class="rfq-button h-8 px-3 inline-flex items-center gap-1.5 text-slate-600 bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-l-md hover:bg-slate-50 dark:hover:bg-slate-900 hover:text-blue-600 transition-all font-bold text-[10px] uppercase tracking-wider" data-id="${row.hash_id}" title="Manage Baseline (RFQ)">
                            <i class="fa-solid fa-pen-to-square"></i> RFQ
                        </button>
                        <button class="compare-button h-8 px-3 inline-flex items-center gap-1.5 text-slate-600 bg-white dark:bg-gray-800 border border-l-0 border-slate-200 dark:border-gray-700 rounded-r-md hover:bg-slate-50 dark:hover:bg-slate-900 hover:text-purple-600 transition-all font-bold text-[10px] uppercase tracking-wider ${!row.has_rfq ? 'opacity-50 cursor-not-allowed' : ''}" data-id="${row.hash_id}" ${!row.has_rfq ? 'disabled' : ''} title="VAVE Analysis Comparison">
                            <i class="fa-solid fa-chart-pie"></i> Analysis
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
        $('#rfq_material_spec_id').val(''); // Clear spec
        $('#rfq_unit_id').val('').trigger('change'); // Clear unit and reset visibility
        $('#rfq_material_price').val('20000');

        // 2. Auto-fill from latest revision if available
        if (window.latestRevision) {
            // Safe check for dropdown data
            if (window.latestRevision.material_spec) {
                $('#rfq_material_spec_id').val(window.latestRevision.material_spec.hash_id);
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
        $('#rfq_material_spec_id').val(specId);

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

    // Handle VA/VE Comparison
    $(document).on('click', '.compare-button', function() {
        const id = $(this).data('id');
        $.get(`{{ url('inventory/vave/comparison') }}/${id}`, function(res) {
            $('#comparisonTitle').text(`VA/VE Material Analysis History`);
            const customer = res.product.customer ? res.product.customer.customer_name : (res.product.customer_code || '-');
            $('#comparisonSubtitle').text(`${res.product.part_no} - ${res.product.part_name} (${customer})`);
            
            const rfqs = res.rfqs || [];
            const revisions = res.revisions || [];
            const benchmarkRfq = rfqs.find(r => r.is_active) || rfqs[0];
            
            if (!benchmarkRfq) {
                $('#comparisonContainer').html('<div class="p-12 text-center text-gray-400"><i class="fa-solid fa-file-circle-exclamation text-4xl mb-4"></i><p>No baseline data found for this product.</p></div>');
                $('#comparisonModal').removeClass('hidden').addClass('flex');
                return;
            }

            // Calculate Summary Metrics
            const latestRev = revisions[0];
            let summaryBar = '';
            
            if (benchmarkRfq && latestRev) {
                const saving = benchmarkRfq.weight_kg - latestRev.weight_kg;
                const savingPct = (saving / benchmarkRfq.weight_kg) * 100;
                const isPos = saving >= 0;
                const colorClass = isPos ? 'text-green-700 bg-green-50 border-green-200' : 'text-red-700 bg-red-50 border-red-200';
                
                summaryBar = `
                    <div class="flex items-center justify-between gap-4 mb-4 px-1">
                        <div class="flex items-center gap-4">
                            <div class="flex items-center gap-3 px-4 py-2 rounded-md border ${colorClass}">
                                <span class="text-[10px] font-bold uppercase tracking-[0.2em] opacity-80">Net Impact</span>
                                <div class="h-4 w-px bg-current opacity-20"></div>
                                <span class="font-bold text-lg tracking-tight">${Math.abs(savingPct).toFixed(2)}%</span>
                                <span class="text-xs font-semibold opacity-80">(${Math.abs(saving).toFixed(3)} kg)</span>
                                <span class="text-[9px] uppercase font-bold px-1.5 py-0.5 rounded bg-white/50 ml-1 tracking-wider">${isPos ? 'MERIT' : 'LOSS'}</span>
                            </div>
                        </div>
                         <div class="flex items-center gap-4">
                              <a href="{{ url('inventory/vave/comparison') }}/${id}/export" class="h-9 px-4 inline-flex items-center gap-2 text-white rounded-md bg-slate-900 hover:bg-slate-800 transition-all font-bold text-[10px] uppercase tracking-wider shadow-md active:scale-[0.98]">
                                <i class="fa-solid fa-file-excel text-sm"></i> Export Excel
                            </a>
                             <label class="inline-flex items-center cursor-pointer group px-2 py-1 rounded-full hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <div class="relative flex items-center">
                                    <input type="checkbox" id="toggleHistory" class="sr-only peer">
                                    <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-slate-900"></div>
                                </div>
                                <span class="ms-2 text-[10px] font-bold text-gray-500 group-hover:text-blue-600 transition-colors uppercase tracking-[0.2em] pointer-events-none">View History</span>
                            </label>
                        </div>
                    </div>
                `;
            }

            // Sticky Calculations
            // Param (w-48/12rem/192px) -> Left: 0
            // Plan (w-48/12rem/192px) -> Left: 192px
            // Actual (w-48/12rem/192px) -> Left: 384px
            // Variance (w-32/8rem/128px) -> Left: 576px

            // Build table HTML
            let html = `
                ${summaryBar}
                <div class="overflow-x-auto custom-scrollbar rounded-lg border border-gray-200 dark:border-gray-700 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.1)] bg-white dark:bg-gray-900">
                    <table id="comparisonTable" class="table-fixed min-w-full text-sm text-left border-collapse whitespace-nowrap">
                        <thead class="text-xs uppercase bg-gray-50 dark:bg-gray-800 sticky top-0 z-30">
                            <tr>
                                <th class="w-[160px] min-w-[160px] max-w-[160px] px-4 py-3 text-gray-500 font-bold bg-gray-50 dark:bg-gray-800 border-b border-r border-gray-200 dark:border-gray-700 sticky left-0 z-40 text-left shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)]">PARAMETER</th>
            `;
            
            // 1. Benchmark (Plan)
            html += `
                <th class="w-[160px] min-w-[160px] max-w-[160px] px-4 py-3 text-center border-b border-r border-gray-200 dark:border-gray-700 bg-blue-50 sticky top-0 z-40" style="left: 160px;">
                    <div class="flex flex-col items-center">
                        <span class="text-[10px] text-blue-600 font-bold tracking-wider">PLAN (BASE)</span>
                        <span class="font-bold text-gray-800 dark:text-gray-200 truncate max-w-[140px]" title="${benchmarkRfq.rfq_name}">${benchmarkRfq.rfq_name}</span>
                    </div>
                </th>
            `;

            // 2. Latest (Actual)
            const latestRevision = revisions.length > 0 ? revisions[0] : null;
            if (latestRevision) {
                html += `
                     <th class="w-[160px] min-w-[160px] max-w-[160px] px-4 py-3 text-center border-b border-r border-gray-200 dark:border-gray-700 bg-emerald-50 sticky top-0 z-40" style="left: 320px;">
                        <div class="flex flex-col items-center">
                            <span class="text-[10px] text-emerald-600 font-bold tracking-wider">ACTUAL (REV)</span>
                            <span class="font-bold text-gray-800 dark:text-gray-200">Rev ${latestRevision.revision}</span>
                        </div>
                    </th>
                `;
            }

            // 3. Variance (Delta)
            html += `
                 <th class="w-[110px] min-w-[110px] max-w-[110px] px-4 py-3 text-center border-b border-r border-gray-200 dark:border-gray-700 bg-gray-100 sticky top-0 z-40 shadow-[4px_0_5px_-2px_rgba(0,0,0,0.1)]" style="left: 480px;">
                    <div class="flex flex-col items-center">
                        <span class="text-[10px] text-gray-500 font-bold tracking-wider">VARIANCE (Δ)</span>
                        <span class="font-bold text-gray-600">Actual - Plan</span>
                    </div>
                </th>
            `;

            // 4. History Headers (Hidden)
             rfqs.forEach(r => {
                if (r.hash_id === benchmarkRfq.hash_id) return;
                html += `
                    <th class="w-[120px] min-w-[120px] px-4 py-3 text-center border-b border-r border-gray-200 dark:border-gray-700 bg-gray-50/50 history-col hidden border-dashed">
                        <div class="flex flex-col items-center opacity-60">
                            <span class="text-[9px] text-gray-400 font-bold">HISTORY (BASE)</span>
                            <span class="font-semibold text-gray-500 text-xs truncate max-w-[100px]">${r.rfq_name}</span>
                        </div>
                    </th>
                `;
            });
            revisions.forEach((rev, idx) => {
                if (idx === 0) return;
                 html += `
                    <th class="w-[120px] min-w-[120px] px-4 py-3 text-center border-b border-r border-gray-200 dark:border-gray-700 bg-gray-50/50 history-col hidden border-dashed">
                        <div class="flex flex-col items-center opacity-60">
                            <span class="text-[9px] text-gray-400 font-bold">HISTORY (REV)</span>
                            <span class="font-semibold text-gray-500 text-xs">Rev ${rev.revision}</span>
                        </div>
                    </th>
                `;
            });

            html += `</tr></thead><tbody class="divide-y divide-gray-200 dark:divide-gray-700">`;

             // Row helper
            const buildRow = (label, valueFormatter, isNumeric = false) => {
                let row = `<tr class="hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-colors duration-150 text-xs text-gray-700 dark:text-gray-300">
                    <td class="w-[160px] min-w-[160px] max-w-[160px] px-4 py-2 font-semibold sticky left-0 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 z-10 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)] transition-colors group-hover:bg-blue-50">${label}</td>`;
                
                // Benchmark Value
                const baseVal = valueFormatter(benchmarkRfq);
                row += `<td class="w-[160px] min-w-[160px] max-w-[160px] px-4 py-2 text-center border-r border-gray-200 dark:border-gray-700 font-medium sticky z-30 bg-white dark:bg-gray-800 transition-colors group-hover:bg-blue-50" style="left: 160px;">${baseVal}</td>`;

                // Latest Value
                let actualVal = '-';
                if (latestRevision) {
                    actualVal = valueFormatter(latestRevision);
                }
                row += `<td class="w-[160px] min-w-[160px] max-w-[160px] px-4 py-2 text-center border-r border-gray-200 dark:border-gray-700 font-medium sticky z-30 bg-white dark:bg-gray-800 transition-colors group-hover:bg-blue-50" style="left: 320px;">${actualVal}</td>`;

                // Variance Logic
                 row += `<td class="w-[110px] min-w-[110px] max-w-[110px] px-4 py-2 text-center border-r border-gray-200 dark:border-gray-700 font-bold variance-cell sticky z-30 bg-gray-50 shadow-[4px_0_5px_-2px_rgba(0,0,0,0.1)] transition-colors group-hover:bg-blue-100" style="left: 480px;">-</td>`;

                // History Columns
                rfqs.forEach(r => {
                     if (r.hash_id === benchmarkRfq.hash_id) return;
                     row += `<td class="w-[120px] min-w-[120px] px-4 py-2 text-center border-r border-gray-200 dark:border-gray-700 text-gray-400 history-col hidden border-dashed">${valueFormatter(r)}</td>`;
                });
                revisions.forEach((rev, idx) => {
                    if (idx === 0) return;
                    row += `<td class="w-[120px] min-w-[120px] px-4 py-2 text-center border-r border-gray-200 dark:border-gray-700 text-gray-400 history-col hidden border-dashed">${valueFormatter(rev)}</td>`;
                });

                return row + `</tr>`;
            };

            // Custom Row Builder for Numerics and Computed Values
             const buildComputedRow = (label, valueGetter, unit = '', precision = 2, invertColor = false) => {
                // Determine Value Function
                const getVal = (item) => {
                    if (typeof valueGetter === 'function') return valueGetter(item);
                    return parseFloat(item[valueGetter] || 0);
                };

                const baseValVal = getVal(benchmarkRfq);
                let row = `<tr class="hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-colors duration-150 text-xs group">
                    <td class="w-[160px] min-w-[160px] max-w-[160px] px-4 py-2.5 font-semibold text-gray-700 dark:text-gray-300 sticky left-0 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 z-10 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)] transition-colors group-hover:bg-blue-50">${label}</td>`;
                
                const baseVal = baseValVal;
                row += `<td class="w-[160px] min-w-[160px] max-w-[160px] px-4 py-2.5 text-center border-r border-gray-200 dark:border-gray-700 font-mono text-gray-600 sticky z-30 bg-white dark:bg-gray-800 transition-colors group-hover:bg-blue-50" style="left: 160px;">${baseVal.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: precision})} ${unit}</td>`;

                let actualVal = 0;
                if (latestRevision) {
                    actualVal = getVal(latestRevision);
                     row += `<td class="w-[160px] min-w-[160px] max-w-[160px] px-4 py-2.5 text-center border-r border-gray-200 dark:border-gray-700 font-mono text-gray-800 font-bold bg-yellow-50 group-hover:bg-yellow-100 sticky z-30 transform-gpu" style="left: 320px;">${actualVal.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: precision})} ${unit}</td>`;
                } else {
                     row += `<td class="w-[160px] min-w-[160px] max-w-[160px] px-4 py-2.5 text-center border-r border-gray-200 dark:border-gray-700 sticky z-30 bg-white dark:bg-gray-800 transition-colors group-hover:bg-blue-50" style="left: 320px;">-</td>`;
                }

                // Variance
                if (latestRevision) {
                    const delta = actualVal - baseVal;
                    const isGood = invertColor ? delta > 0 : delta <= 0;
                    
                    let colorClass = 'text-gray-400';
                    let bgClass = 'bg-gray-50'; // Default Opaque
                    if (Math.abs(delta) > 0.0001) {
                         colorClass = isGood ? 'text-green-600' : 'text-red-600';
                         bgClass = isGood ? 'bg-green-50' : 'bg-red-50';
                    }
                    const sign = delta > 0 ? '+' : '';
                    row += `<td class="w-[110px] min-w-[110px] max-w-[110px] px-4 py-2.5 text-center border-r border-gray-200 dark:border-gray-700 font-mono font-bold ${colorClass} sticky z-30 ${bgClass} shadow-[4px_0_5px_-2px_rgba(0,0,0,0.1)] transition-colors group-hover:bg-blue-100" style="left: 480px;">${sign}${delta.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: precision})} ${unit}</td>`;
                }

                // History
                 rfqs.forEach(r => {
                     if (r.hash_id === benchmarkRfq.hash_id) return;
                     const val = getVal(r);
                     row += `<td class="w-[120px] min-w-[120px] px-4 py-2.5 text-center border-r border-gray-200 dark:border-gray-700 text-gray-400 history-col hidden border-dashed font-mono">${val.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: precision})}</td>`;
                });
                revisions.forEach((rev, idx) => {
                    if (idx === 0) return;
                    const val = getVal(rev);
                    row += `<td class="w-[120px] min-w-[120px] px-4 py-2.5 text-center border-r border-gray-200 dark:border-gray-700 text-gray-400 history-col hidden border-dashed font-mono">${val.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: precision})}</td>`;
                });

                return row + `</tr>`;
            };

            // Section Header Builder
            const buildSectionRow = (title) => {
                let row = `<tr class="bg-gray-100 dark:bg-gray-700 text-xs uppercase tracking-wider font-bold text-gray-500 dark:text-gray-400">
                    <td class="w-[160px] min-w-[160px] max-w-[160px] px-4 py-2 sticky left-0 bg-gray-100 dark:bg-gray-700 border-r border-gray-300 dark:border-gray-600 z-20 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)] pt-4 pb-1">${title}</td>`;
                
                // Spacers for sticky columns
                row += `<td class="w-[160px] min-w-[160px] max-w-[160px] px-4 py-2 bg-gray-100 dark:bg-gray-700 border-r border-gray-300 dark:border-gray-600 sticky z-20 pt-4 pb-1" style="left: 160px;"></td>`;
                row += `<td class="w-[160px] min-w-[160px] max-w-[160px] px-4 py-2 bg-gray-100 dark:bg-gray-700 border-r border-gray-300 dark:border-gray-600 sticky z-20 pt-4 pb-1" style="left: 320px;"></td>`;
                row += `<td class="w-[110px] min-w-[110px] max-w-[110px] px-4 py-2 bg-gray-100 dark:bg-gray-700 border-r border-gray-300 dark:border-gray-600 sticky z-20 shadow-[4px_0_5px_-2px_rgba(0,0,0,0.1)] pt-4 pb-1" style="left: 480px;"></td>`;

                // History Spacers
                const histCount = (rfqs.length - 1) + (revisions.length - 1);
                for(let i=0; i<histCount; i++) {
                    row += `<td class="w-[120px] min-w-[120px] px-4 py-2 bg-gray-100 dark:bg-gray-700 history-col hidden border-dashed pt-4 pb-1"></td>`;
                }
                return row + `</tr>`;
            };

            // Rows
            // Section 1: Specifications
            html += buildSectionRow('Specification');
            html += buildRow('Material Spec', item => item.material_spec ? item.material_spec.spec_name : '-');
            html += buildRow('Unit Type', item => item.unit ? item.unit.name : '-');
            
            // Dimensions (Special case, mixed text)
            // Fix: Clean zeros (0.60 -> 0.6)
            const fmtDim = (i) => {
                const unt = (i.unit ? i.unit.name : '').toLowerCase();
                let d = `${parseFloat(i.thickness)} x ${parseFloat(i.width)}`;
                if (unt.includes('coil')) {
                    d += ` x ${parseFloat(i.pitch)} (P)`;
                } else if (unt.includes('trapezoid')) {
                    d += ` x (${parseFloat(i.length)} + ${parseFloat(i.length_2)})/2`;
                } else {
                    d += ` x ${parseFloat(i.length)}`;
                }
                return d;
            };
            html += buildRow('Dimensions', fmtDim);

            // Numeric Rows
            html += buildComputedRow('Thickness (mm)', 'thickness', '', 2, false); 
            html += buildComputedRow('Width (mm)', 'width', '', 2, false);

            const unitNameCompare = (benchmarkRfq.unit ? benchmarkRfq.unit.name : '').toLowerCase();
            if (unitNameCompare.includes('trapezoid')) {
                html += buildComputedRow('Length 1 (L1)', 'length', 'mm', 2, false);
                html += buildComputedRow('Length 2 (L2)', 'length_2', 'mm', 2, false);
            } else if (unitNameCompare.includes('coil')) {
                html += buildComputedRow('Pitch (mm)', 'pitch', 'mm', 2, false);
            } else {
                html += buildComputedRow('Length (mm)', 'length', 'mm', 2, false);
            }
            
            // Section 2: Weight & Yield
            html += buildSectionRow('Yield & Weight');
            html += buildComputedRow('Density', 'density', '', 3, true);
            html += buildComputedRow('Gross Weight (kg)', 'weight_kg', '', 3, false); // Lower weight is Merit (Green)
            html += buildComputedRow('Net Weight/Part', 'net_weight', '', 3, false);
            
            // Scrap (Loss) Calculation
            const calcScrap = (item) => {
                const gross = parseFloat(item.weight_kg) || 0;
                const net = parseFloat(item.net_weight) || 0;
                return gross - net;
            };
            html += buildComputedRow('Scrap (kg)', calcScrap, 'kg', 3, false); // Lower scrap is Better
            
            // Budomari (Yield).
            const calcBudomari = (item) => {
                const gross = parseFloat(item.weight_kg) || 0;
                const net = parseFloat(item.net_weight) || 0;
                if (gross <= 0 || net <= 0) return 0;
                return (net / gross) * 100;
            };
            html += buildComputedRow('Budomari (%)', calcBudomari, '%', 1, true); // Higher yield is Good (Green)
            
            // New Computed metrics
             // Section 3: Commercial
            html += buildSectionRow('Commercial');
            html += buildComputedRow('Price/kg (IDR)', (item) => parseFloat(item.material_price || 0), '', 0, false);
            
            // Cost = Weight * Price
            html += buildComputedRow('Cost (IDR)', (item) => (parseFloat(item.weight_kg||0) * parseFloat(item.material_price || 0)), '', 0, false); // Lower cost is Good


             // Summary Section (Status & Rate)
             // Status Row
             let statusRow = `<tr class="bg-gray-50/50 hover:bg-blue-100 dark:bg-gray-800 dark:hover:bg-blue-900/40 text-xs border-t-2 border-gray-200 dark:border-gray-600 group">
                <td class="w-[160px] min-w-[160px] max-w-[160px] px-4 py-3 sticky left-0 bg-gray-50 dark:bg-gray-800 border-r border-gray-300 z-10 font-bold uppercase shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)] group-hover:bg-blue-100">Status</td>
                <td class="w-[160px] min-w-[160px] max-w-[160px] px-4 py-3 text-center border-r border-gray-300 bg-gray-50 dark:bg-gray-800 sticky z-30 group-hover:bg-blue-100" style="left: 160px;">-</td>`;
             
             // Rate Row
             let rateRow = `<tr class="bg-white hover:bg-blue-100 dark:bg-gray-800 dark:hover:bg-blue-900/40 text-xs group">
                <td class="w-[160px] min-w-[160px] max-w-[160px] px-4 py-3 sticky left-0 bg-white dark:bg-gray-800 border-r border-gray-300 z-10 font-bold uppercase shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)] group-hover:bg-blue-100">Rate</td>
                <td class="w-[160px] min-w-[160px] max-w-[160px] px-4 py-3 text-center border-r border-gray-300 bg-white dark:bg-gray-800 sticky z-30 group-hover:bg-blue-100" style="left: 160px;">-</td>`;


             if (latestRevision) {
                const saving = benchmarkRfq.weight_kg - latestRevision.weight_kg;
                const savingPct = (saving / benchmarkRfq.weight_kg) * 100; // Positive = Saving
                const isPos = saving >= 0; 
                
                // Status Cells
                const statusText = isPos ? 'MERIT' : 'LOSS';
                const statusColor = isPos ? 'text-green-700 bg-green-50' : 'text-red-700 bg-red-50';
                statusRow += `<td class="w-[160px] min-w-[160px] max-w-[160px] px-4 py-3 text-center border-r border-gray-300 ${statusColor} font-bold sticky z-30 group-hover:bg-opacity-90" style="left: 320px;">${statusText}</td>`;
                // Variance cell for STATUS (redundant)
                statusRow += `<td class="w-[110px] min-w-[110px] max-w-[110px] px-4 py-3 text-center border-r border-gray-300 bg-gray-100 text-gray-400 sticky z-30 shadow-[4px_0_5px_-2px_rgba(0,0,0,0.1)] group-hover:bg-blue-100" style="left: 480px;">-</td>`;

                // Rate Cells
                const rateColor = isPos ? 'text-green-700' : 'text-red-700';
                rateRow += `<td class="w-[160px] min-w-[160px] max-w-[160px] px-4 py-3 text-center border-r border-gray-300 font-bold ${rateColor} sticky z-30 bg-white dark:bg-gray-800 group-hover:bg-blue-100" style="left: 320px;">${Math.abs(savingPct).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 2})}%</td>`;
                // Variance cell for RATE (redundant)
                rateRow += `<td class="w-[110px] min-w-[110px] max-w-[110px] px-4 py-3 text-center border-r border-gray-300 bg-gray-100 text-gray-400 sticky z-30 shadow-[4px_0_5px_-2px_rgba(0,0,0,0.1)] group-hover:bg-blue-100" style="left: 480px;">-</td>`;

             } else {
                 statusRow += `<td class="w-[160px] min-w-[160px] max-w-[160px] px-4 py-3 text-center border-r border-gray-300 sticky z-30 bg-gray-50" style="left: 320px;">-</td><td class="w-[110px] min-w-[110px] max-w-[110px] px-4 py-3 sticky z-30 bg-gray-50" style="left: 480px;">-</td>`;
                 rateRow += `<td class="w-[160px] min-w-[160px] max-w-[160px] px-4 py-3 text-center border-r border-gray-300 sticky z-30 bg-white" style="left: 320px;">-</td><td class="w-[110px] min-w-[110px] max-w-[110px] px-4 py-3 sticky z-30 bg-white" style="left: 480px;">-</td>`;
             }

             // History Impact Placeholders
             const histCount = (rfqs.length - 1) + (revisions.length - 1);
             for(let i=0; i<histCount; i++) {
                 statusRow += `<td class="w-[120px] min-w-[120px] px-4 py-3 text-center bg-gray-50 history-col hidden border-dashed">-</td>`;
                 rateRow += `<td class="w-[120px] min-w-[120px] px-4 py-3 text-center bg-white history-col hidden border-dashed">-</td>`;
             }
             
             statusRow += `</tr>`;
             rateRow += `</tr>`;
             
             html += statusRow + rateRow;


            html += `</tbody></table></div>`;
            
            $('#comparisonContainer').html(html);
            $('#comparisonModal').removeClass('hidden').addClass('flex');

            // Bind Toggles
            $('#toggleHistory').off().on('change', function() {
                if(this.checked) {
                    $('.history-col').removeClass('hidden');
                } else {
                    $('.history-col').addClass('hidden');
                }
            });
        });
    });

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
    .font-mono { font-family: 'JetBrains Mono', 'Fira Code', monospace; }
    .custom-scrollbar::-webkit-scrollbar { height: 8px; width: 8px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 20px; border: 2px solid transparent; background-clip: content-box; }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; }
    
    /* Sticky Column Shadows Enhancement */
    .sticky-shadow { position: relative; }
    .sticky-shadow::after {
        content: ''; position: absolute; top: 0; right: 0; bottom: 0; width: 4px;
        background: linear-gradient(to right, rgba(0,0,0,0.05), transparent);
        pointer-events: none;
    }
    .dark .sticky-shadow::after { background: linear-gradient(to right, rgba(0,0,0,0.2), transparent); }
</style>
@endpush
