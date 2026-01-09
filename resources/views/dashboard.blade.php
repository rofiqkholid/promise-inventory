@extends('layouts.app')

@section('title', 'Inventory Dashboard')

@section('content')
<div class="bg-[#e9ecef] min-h-screen p-4 font-sans text-slate-700">
    
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

    <div class="flex items-center gap-1.5 mb-2">
        <div class="bg-black text-white rounded h-4 w-4 flex items-center justify-center">
            <i class="fa-solid fa-list text-[8px]"></i>
        </div>
        <span class="font-bold text-xs uppercase">Filter Data</span>
    </div>
    
    <div class="grid grid-cols-5 gap-3 mb-6">
        @php
            $filters = [
                'Months Trendline' => ['Oct', 'Nov', 'Dec'],
                'Model' => ['5P45', 'D37D', 'VL20'],
                'Costumer' => ['IAMI', 'MMKI', 'SIM'],
                'Status Balance' => ['Critical', 'Over', 'Safe'],
                'Status Usage' => ['Over', 'Safe']
            ];
        @endphp
        @foreach($filters as $title => $options)
        <div class="bg-white border border-slate-400 rounded p-1.5">
            <div class="flex justify-between items-center mb-1.5 border-b border-slate-100 pb-1">
                <span class="text-[10px] font-bold text-slate-700">{{ $title }}</span>
                <div class="flex gap-1 text-slate-300 text-[9px]">
                    <i class="fa-solid fa-list-check"></i>
                    <i class="fa-solid fa-filter"></i>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-1">
                @foreach(array_slice($options, 0, 3) as $opt)
                    <div class="bg-[#c6d9f1] text-[9px] px-1 py-0.5 rounded border border-[#8db3e2] text-slate-800 truncate">{{ $opt }}</div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>

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
            
            <div>
                <div class="flex items-center gap-1.5 mb-2">
                    <div class="bg-black text-white rounded h-4 w-4 flex items-center justify-center">
                        <i class="fa-solid fa-table text-[8px]"></i>
                    </div>
                    <span class="font-bold text-[10px] uppercase">Details of Material Balance Status</span>
                </div>
                <div class="bg-white border border-slate-400 rounded overflow-hidden">
                    <table class="w-full text-[9px]">
                        <thead class="bg-slate-100 border-b border-slate-400">
                            <tr>
                                <th class="p-1 text-left border-r border-slate-200">Item No</th>
                                <th class="p-1 text-left border-r border-slate-200">Costumer</th>
                                <th class="p-1 text-left border-r border-slate-200">Model</th>
                                <th class="p-1 text-left">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @for($i=0; $i<5; $i++)
                            <tr>
                                <td class="p-1 border-r border-slate-100">22644W030P-R</td>
                                <td class="p-1 border-r border-slate-100 uppercase">MMKI</td>
                                <td class="p-1 border-r border-slate-100 uppercase">5J45</td>
                                <td class="p-1 font-bold text-green-600">Safe</td>
                            </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
            </div>

            <div>
                <div class="flex items-center gap-1.5 mb-2">
                    <div class="bg-black text-white rounded h-4 w-4 flex items-center justify-center">
                        <i class="fa-solid fa-table text-[8px]"></i>
                    </div>
                    <span class="font-bold text-[10px] uppercase">Details of Material Usage Status</span>
                </div>
                <div class="bg-white border border-slate-400 rounded overflow-hidden">
                    <table class="w-full text-[9px]">
                        <tbody class="divide-y divide-slate-200">
                            @for($i=0; $i<4; $i++)
                            <tr>
                                <td class="p-1 border-r border-slate-100 w-1/2">22644W030P-R</td>
                                <td class="p-1 border-r border-slate-100 w-1/4 uppercase">MMKI</td>
                                <td class="p-1 font-bold text-green-600">Safe</td>
                            </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
            </div>

            <div>
                <div class="flex items-center gap-1.5 mb-2">
                    <div class="bg-black text-white rounded h-4 w-4 flex items-center justify-center">
                        <i class="fa-solid fa-clock-rotate-left text-[8px]"></i>
                    </div>
                    <span class="font-bold text-[10px] uppercase">Transaction History</span>
                </div>
                <div class="bg-white border border-slate-400 rounded overflow-hidden">
                    <table class="w-full text-[9px]">
                        <thead class="bg-[#bfdbfe] border-b border-slate-400 font-bold">
                            <tr>
                                <th class="p-1 text-left">Item No</th>
                                <th class="p-1 text-center">Qty</th>
                                <th class="p-1 text-left">Category</th>
                                <th class="p-1 text-left">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @for($i=0; $i<3; $i++)
                            <tr class="border-b border-slate-100">
                                <td class="p-1 text-blue-700">8975861544/89...</td>
                                <td class="p-1 text-center font-bold text-red-600">-20</td>
                                <td class="p-1">Out Trial</td>
                                <td class="p-1 text-[8px]">12/4/2025</td>
                            </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection