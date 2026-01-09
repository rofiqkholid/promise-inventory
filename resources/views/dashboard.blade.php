@extends('layouts.app')

@section('title', 'Inventory Dashboard')

@section('content')
<div class="bg-[#e9ecef] min-h-screen p-4 font-sans text-slate-700">
    {{-- STATS SECTION --}}
    <div class="grid grid-cols-6 gap-3 mb-6">
        @php
            $stats = [
                ['val' => '89,997,200', 'label' => 'TOTAL STOCK VALUE', 'icon' => 'fa-hand-holding-dollar'],
                ['val' => '19,921', 'label' => 'TOTAL MATERIAL IN', 'icon' => 'fa-cart-flatbed-suitcase'],
                ['val' => '10,964', 'label' => 'TOTAL MATERIAL OUT', 'icon' => 'fa-cart-flatbed-suitcase'],
                ['val' => '1,651', 'label' => 'TOTAL MATERIAL OUT PP', 'icon' => 'fa-cart-flatbed-suitcase'],
                ['val' => '3,963', 'label' => 'TOTAL MATERIAL OUT EVENT', 'icon' => 'fa-cart-flatbed-suitcase'],
                ['val' => '5,110', 'label' => 'TOTAL MATERIAL OUT TRIAL', 'icon' => 'fa-cart-flatbed-suitcase'],
            ];
        @endphp

        @foreach($stats as $stat)
        <div class="bg-white rounded-2xl border border-slate-400 p-3 flex flex-col items-center justify-center text-center shadow-sm">
            <div class="text-[#34a4d4] mb-1 text-2xl">
                <i class="fa-solid {{ $stat['icon'] }}"></i>
            </div>
            <h4 class="text-xl font-bold text-[#34a4d4] leading-none">{{ $stat['val'] }}</h4>
            <p class="text-[9px] font-bold text-slate-500 mt-2 leading-tight uppercase tracking-tighter">{{ $stat['label'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- FILTER SECTION --}}
    <div class="bg-white border border-slate-400 rounded-lg p-4 mb-6 shadow-sm">
        
        {{-- Header Filter --}}
        <div class="flex items-center gap-1.5 mb-4 border-b border-slate-100 pb-2">
            <div class="bg-black text-white rounded h-4 w-4 flex items-center justify-center">
                <i class="fa-solid fa-list text-[8px]"></i>
            </div>
            <span class="font-bold text-xs uppercase">Filter Data</span>
        </div>
      <form id="filterForm">      
        {{-- Grid Input Filter (Manual Columns) --}}
        <div class="grid grid-cols-5 gap-4">
            {{-- 1. Filter Month --}}
           <div class="flex flex-col">
    <label class="text-[10px] font-bold text-slate-700 mb-1 uppercase tracking-tight">Months Trendline</label>
    <div class="relative w-full">
        <input type="text" 
               id="month_year_picker"
               class="w-full border border-slate-300 rounded px-2 text-[10px] h-[28px] leading-none focus:outline-none focus:border-blue-500 placeholder-slate-400 bg-white cursor-pointer" 
               placeholder="Select Month..." 
               name="month_year"
               readonly>
        
        <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none text-slate-400">
            <i class="fa-regular fa-calendar text-xs"></i>
        </div>
    </div>
</div>

            {{-- 2. Filter Model --}}
            <div class="flex flex-col">
                <label class="text-[10px] font-bold text-slate-700 mb-1 uppercase tracking-tight">Model</label>
                <select class="select2-filter w-full hidden" name="model[]" multiple="multiple">
                    {{-- NANTI: Loop data Model dari Database di sini --}}
                    {{-- @foreach($models as $model) --}}
                    {{--    <option value="{{ $model->id }}">{{ $model->name }}</option> --}}
                    {{-- @endforeach --}}
                </select>
            </div>

            {{-- 3. Filter Customer --}}
            <div class="flex flex-col">
                <label class="text-[10px] font-bold text-slate-700 mb-1 uppercase tracking-tight">Customer</label>
                <select class="select2-filter w-full hidden" name="customer[]" multiple="multiple">
                    {{-- NANTI: Loop data Customer dari Database di sini --}}
                </select>
            </div>

            {{-- 4. Filter Status Balance --}}
            <div class="flex flex-col">
                <label class="text-[10px] font-bold text-slate-700 mb-1 uppercase tracking-tight">Status Balance</label>
                <select class="select2-filter w-full hidden" name="status_balance[]" multiple="multiple">
                    {{-- Opsi statis bisa tetap ditulis manual jika tidak dari DB --}}
                    <option value="Critical">Critical</option>
                    <option value="Over">Over</option>
                    <option value="Safe">Safe</option>
                </select>
            </div>

            {{-- 5. Filter Status Usage --}}
            <div class="flex flex-col">
                <label class="text-[10px] font-bold text-slate-700 mb-1 uppercase tracking-tight">Status Usage</label>
                <select class="select2-filter w-full hidden" name="status_usage[]" multiple="multiple">
                     {{-- Opsi statis --}}
                     <option value="Over">Over</option>
                     <option value="Safe">Safe</option>
                </select>
            </div>

        </div>

        {{-- Footer Tombol --}}
        <div class="flex justify-end gap-2 mt-5 pt-3 border-t border-slate-100">
            <button type="button" id="btnReset" class="bg-slate-200 hover:bg-slate-300 text-slate-700 rounded px-4 py-1.5 flex items-center gap-2 transition shadow-sm border border-slate-400">
                <i class="fa-solid fa-rotate-left text-xs"></i>
                <span class="text-[10px] font-bold uppercase">Reset</span>
            </button>
            <button type="button" id="btnApply" class="bg-blue-600 hover:bg-blue-700 text-white rounded px-4 py-1.5 flex items-center gap-2 transition shadow-sm border border-blue-800">
                <i class="fa-solid fa-filter text-xs"></i>
                <span class="text-[10px] font-bold uppercase">Apply Filter</span>
            </button>
        </div>
        </form>
    </div>

    {{-- CHARTS & TABLES (Bagian Bawah Tetap Sama) --}}
    <div class="grid grid-cols-12 gap-6">
        <div class="col-span-8 grid grid-cols-2 gap-x-6 gap-y-4">
            @foreach(['Material Stock Status', 'Material Usage Status by Models', 'Transaction Trendline', 'Material Usage Status by Makers'] as $chart)
            <div>
                <div class="flex items-center gap-1.5 mb-2">
                    <div class="bg-black text-white rounded h-4 w-4 flex items-center justify-center">
                        <i class="fa-solid fa-chart-column text-[8px]"></i>
                    </div>
                    <span class="font-bold text-[11px]">{{ $chart }}</span>
                </div>
                <div class="bg-white rounded border border-slate-400 p-2 h-48">
                    {{-- Placeholder Chart --}}
                    <div class="w-full h-full flex items-end justify-around border-b border-l border-slate-200 px-1 pb-1 gap-1">
                        @for($i=0; $i<10; $i++)
                            <div class="bg-blue-600 w-2" style="height: {{ rand(20, 90) }}%"></div>
                            <div class="bg-orange-500 w-2" style="height: {{ rand(10, 40) }}%"></div>
                        @endfor
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="col-span-4 space-y-4">
            @foreach(['Details of Material Balance Status', 'Details of Material Usage Status'] as $tableTitle)
            <div>
                <div class="flex items-center gap-1.5 mb-2">
                    <div class="bg-black text-white rounded h-4 w-4 flex items-center justify-center">
                        <i class="fa-solid fa-table text-[8px]"></i>
                    </div>
                    <span class="font-bold text-[10px] uppercase">{{ $tableTitle }}</span>
                </div>
                <div class="bg-white border border-slate-400 rounded overflow-hidden">
                    <table class="w-full text-[9px]">
                        <thead class="bg-slate-100 border-b border-slate-400">
                            <tr>
                                <th class="p-1 text-left border-r border-slate-200">Item No</th>
                                <th class="p-1 text-left border-r border-slate-200">Costumer</th>
                                <th class="p-1 text-left">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @for($i=0; $i<3; $i++)
                            <tr>
                                <td class="p-1 border-r border-slate-100">22644W030P-R</td>
                                <td class="p-1 border-r border-slate-100 uppercase">MMKI</td>
                                <td class="p-1 font-bold text-green-600">Safe</td>
                            </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>
<script>
   
    $(document).ready(function() {
    // Inisialisasi Flatpickr Month Picker
    const monthPicker = flatpickr("#month_year_picker", {
            disableMobile: "true",
            plugins: [
                new monthSelectPlugin({
                    shorthand: true,
                    dateFormat: "Y-m",
                    altFormat: "F Y",
                    theme: "light"
                })
            ]
        });

    // Inisialisasi Select2 yang sudah ada
    $('.select2-filter').select2({
        placeholder: 'Select...',
        allowClear: true,
        width: '100%'
    });

    // 2. Inisialisasi Select2
    $('.select2-filter').select2({
        placeholder: 'Select...',
        allowClear: true,
        width: '100%'
    });

    // 3. Logika Tombol APPLY FILTER
   $('#btnApply').on('click', function() {
    const formData = $('#filterForm').serialize();
    
    // window.location.pathname akan mengambil URL dashboard yang sedang aktif
    window.location.href = window.location.pathname + "?" + formData;
});

    // 4. Logika Tombol RESET
    $('#btnReset').on('click', function() {
        // Reset Input Biasa
        $('#filterForm')[0].reset();

        // Reset Select2 (wajib dipanggil agar tampilan visualnya kembali kosong)
        $('.select2-filter').val(null).trigger('change');

        // Reset Flatpickr
        monthPicker.clear();

        console.log("Filter dibersihkan");
    });
});
</script>
@endpush