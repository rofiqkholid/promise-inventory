<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Tool Info - {{ $tool->name }}</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-[#F8FAFC] dark:bg-slate-950 text-slate-900 transition-colors duration-300">
    <div class="min-h-screen flex flex-col items-center justify-start p-4 md:p-8 lg:p-12">
        
        <!-- Navigation Header -->
        <div class="w-full max-w-7xl mb-6 flex justify-between items-center bg-white dark:bg-slate-900 px-5 py-4 rounded-xs border border-slate-200 dark:border-slate-800 shadow-sm">
             @auth
             <a href="{{ route('inventory.tool.dashboard') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-primary-600 transition-colors">
                <i class="fa-solid fa-chevron-left text-[10px]"></i> Dashboard
             </a>
             @else
             <a href="{{ route('login', ['redirect' => route('inventory.tool.dashboard')]) }}" class="inline-flex items-center gap-2 text-xs font-semibold text-primary-600 hover:text-primary-700 transition-colors">
                <i class="fa-solid fa-right-to-bracket"></i> Login to System
             </a>
             @endauth

             <div class="flex items-center gap-2">
                <i class="fa-solid fa-circle-user text-slate-400 text-sm"></i>
                <span class="text-xs font-bold text-slate-400">
                    {{ Auth::check() ? Auth::user()->name : 'Guest' }}
                </span>
                <span class="w-1.5 h-1.5 rounded-full {{ Auth::check() ? 'bg-emerald-500' : 'bg-slate-300' }}"></span>
             </div>
        </div>

        <!-- Quick Actions -->
        <div class="w-full max-w-7xl grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            @php
                $txRoute = $tool->category?->moving_type === 'fast'
                    ? route('inventory.tool.fast-stock.index', ['tool_id' => $tool->id, 'action' => 'out'])
                    : route('inventory.tool.slow-batch.index', ['tool_id' => $tool->id]);
                
                $stoUrl = $activeStoId 
                    ? route('inventory.tool.sto.show', ['id' => $activeStoId, 'tool_id' => $tool->id])
                    : route('inventory.tool.sto.index');

                if (Auth::guest()) {
                    $txRoute = route('login', ['redirect' => $txRoute]);
                    $stoUrl = route('login', ['redirect' => $stoUrl]);
                }
            @endphp
            
            <a href="{{ $txRoute }}" 
               class="group h-14 bg-primary-600 text-white rounded-xs flex items-center justify-center gap-3 font-semibold text-xs hover:bg-primary-700 transition-all shadow-sm active:scale-95">
                <i class="fa-solid fa-right-left text-lg text-primary-200"></i>
                <span>Stock Transaction (IN / OUT)</span>
                @guest <i class="fa-solid fa-lock text-[10px] opacity-40"></i> @endguest
            </a>
            
            <a href="{{ $stoUrl }}" 
               class="group h-14 bg-white dark:bg-slate-900 text-slate-900 dark:text-white border border-slate-200 dark:border-slate-800 rounded-xs flex items-center justify-center gap-3 font-semibold text-xs hover:bg-slate-50 dark:hover:bg-slate-800 transition-all shadow-sm active:scale-95">
                <i class="fa-solid fa-clipboard-check text-lg text-emerald-500"></i>
                <span>Stock Take (STO) Entry</span>
                @guest <i class="fa-solid fa-lock text-[10px] opacity-40"></i> @endguest
            </a>
        </div>

        <!-- Detail Content Container -->
        <div class="w-full max-w-7xl flex flex-col gap-4 overflow-visible h-auto">
            
            <!-- Main Header Details with Premium Card Background -->
            <div class="bg-white dark:bg-gray-900 border border-slate-100 dark:border-gray-800 rounded-xs p-4 shadow-xs relative">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 relative z-10">
                    <div>
                        <div class="flex items-center gap-2 mb-1.5">
                            <span class="px-2 py-0.5 bg-primary-50 dark:bg-primary-950/40 text-primary-600 dark:text-primary-400 border border-primary-100 dark:border-primary-900 text-[9px] font-bold uppercase tracking-wider rounded-xs">{{ $tool->category?->name ?? 'Category' }}</span>
                            <span class="px-2 py-0.5 bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-gray-300 border border-slate-200 dark:border-gray-700 text-[9px] font-bold uppercase tracking-wider rounded-xs">{{ strtoupper($tool->category?->moving_type ?? 'FAST MOVING') }}</span>
                        </div>
                        <h3 class="text-xl font-bold text-slate-850 dark:text-white tracking-tight">{{ $tool->name }}</h3>
                        <p class="text-xs text-slate-500 dark:text-gray-400 mt-1">Brand: <span class="font-semibold text-slate-700 dark:text-gray-200">{{ $tool->brand }}</span> | Spec Code: <span class="font-semibold text-slate-700 dark:text-gray-200">{{ $tool->spec_code ?? '-' }}</span></p>
                    </div>
                </div>
            </div>

            <!-- Specs, Drawing & Stock Grid -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                
                <!-- Sketch Drawing (4 Cols) -->
                <div class="md:col-span-4 bg-white dark:bg-gray-900 border border-slate-100 dark:border-gray-800 rounded-xs p-5 shadow-xs flex flex-col justify-between">
                    <div>
                        <h4 class="text-xs font-bold text-slate-700 dark:text-slate-200 uppercase tracking-wider mb-4 pb-2 border-b border-slate-50 dark:border-gray-800">
                            Sketch & Drawing
                        </h4>
                        <div class="h-44 flex items-center justify-center border border-slate-100 dark:border-gray-800 rounded-xs bg-slate-50 dark:bg-slate-800/40 overflow-hidden relative group cursor-pointer" id="dtSketchContainer">
                            @if($tool->sketch?->image_path)
                                <img src="{{ asset('storage/' . $tool->sketch->image_path) }}" alt="{{ $tool->sketch->name }}" class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-300" onclick="window.previewImg('{{ asset('storage/' . $tool->sketch->image_path) }}')">
                            @else
                                <i class="fa-solid fa-image text-3xl text-slate-250 dark:text-gray-700"></i>
                            @endif
                        </div>
                    </div>
                    <p class="text-[9px] text-center text-slate-400 mt-2 truncate">{{ $tool->sketch?->name ?? 'No sketch assigned' }}</p>
                </div>

                <!-- Specifications List (4 Cols) -->
                <div class="md:col-span-4 bg-white dark:bg-gray-900 border border-slate-100 dark:border-gray-800 rounded-xs p-5 shadow-xs flex flex-col justify-between">
                    <div>
                        <h4 class="text-xs font-bold text-slate-700 dark:text-slate-200 uppercase tracking-wider mb-4 pb-2 border-b border-slate-50 dark:border-gray-800 flex items-center gap-1.5">
                            <i class="fa-solid fa-list-check text-primary-500"></i> Specifications
                        </h4>
                        <div class="space-y-2.5 text-xs">
                            <div class="flex justify-between items-center py-1 border-b border-slate-50/50 dark:border-gray-800/40">
                                <span class="text-slate-400">Dimension (ø / T)</span>
                                <span class="font-bold text-slate-700 dark:text-gray-200">{{ $tool->dimension ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between items-center py-1 border-b border-slate-50/50 dark:border-gray-800/40">
                                <span class="text-slate-400">Length (L)</span>
                                <span class="font-bold text-slate-700 dark:text-gray-200">{{ $tool->length ? $tool->length . ' mm' : '-' }}</span>
                            </div>
                            <div class="flex justify-between items-center py-1 border-b border-slate-50/50 dark:border-gray-800/40">
                                <span class="text-slate-400">Material Type</span>
                                <span class="font-bold text-slate-700 dark:text-gray-200">{{ $tool->material_type ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between items-center py-1 border-b border-slate-50/50 dark:border-gray-800/40">
                                <span class="text-slate-400">HRC</span>
                                <span class="font-bold text-slate-700 dark:text-gray-200">{{ $tool->hrc ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between items-center py-1 border-b border-slate-50/50 dark:border-gray-800/40">
                                <span class="text-slate-400">UOM</span>
                                <span class="font-bold text-slate-700 dark:text-gray-200">{{ $tool->uom }}</span>
                            </div>
                            <div class="flex justify-between items-center py-1 border-b border-slate-50/50 dark:border-gray-800/40">
                                <span class="text-slate-400">Safety Min/Max</span>
                                <span class="font-bold text-slate-700 dark:text-gray-200">{{ $tool->qty_min ?? 0 }} / {{ $tool->qty_max ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Inventory Stock (4 Cols) -->
                <div class="md:col-span-4 bg-white dark:bg-gray-900 border border-slate-100 dark:border-gray-800 rounded-xs p-5 shadow-xs flex flex-col justify-between">
                    <div>
                        <h4 class="text-xs font-bold text-slate-700 dark:text-slate-200 uppercase tracking-wider mb-4 pb-2 border-b border-slate-50 dark:border-gray-800">
                            Warehouse Stock
                        </h4>
                        
                        @php
                            $status = 'safe';
                            if ($totalQty < $tool->qty_min) {
                                $status = 'critical';
                            } elseif ($totalQty == $tool->qty_min) {
                                $status = 'warning';
                            } elseif ($tool->qty_max > 0 && $totalQty > $tool->qty_max) {
                                $status = 'over';
                            }

                            $legendBadges = [
                                'safe' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                                'warning' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                                'critical' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400',
                                'over' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400',
                            ];
                            $badgeClass = $legendBadges[$status] ?? $legendBadges['safe'];
                        @endphp

                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-xs bg-slate-50 dark:bg-gray-800 flex items-center justify-center text-slate-650 dark:text-gray-400 font-black">
                                <i class="fa-solid fa-warehouse"></i>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-400 block font-medium uppercase tracking-wider">Total Quantity</span>
                                <div class="flex items-baseline gap-1.5">
                                    <span class="text-2xl font-bold text-slate-800 dark:text-white">{{ $totalQty }}</span>
                                    <span class="text-[10px] text-slate-400 font-semibold">{{ $tool->uom }}</span>
                                </div>
                            </div>
                            <div class="ml-auto">
                                <span class="px-2.5 py-1 {{ $badgeClass }} text-[9px] font-bold uppercase tracking-wider rounded-xs">{{ strtoupper($status) }}</span>
                            </div>
                        </div>

                        <!-- Location breakdowns -->
                        <div class="overflow-y-auto max-h-[140px] custom-scrollbar border border-slate-50 dark:border-gray-800 rounded-xs p-2 bg-slate-50/50 dark:bg-slate-900/40">
                            <div class="space-y-1.5 text-[11px]">
                                @if(empty($stockInfo))
                                    <p class="text-slate-400 text-center py-4 text-xs font-medium">No storage records.</p>
                                @else
                                    @foreach($stockInfo as $item)
                                        @if($tool->category?->moving_type === 'fast')
                                            <div class="flex justify-between items-center py-1.5 border-b border-slate-100/50 dark:border-slate-800 last:border-none">
                                                <span class="text-slate-700 dark:text-slate-350 font-semibold">
                                                    <i class="fa-solid fa-location-dot text-slate-400 mr-1.5"></i> {{ $item['location'] }}
                                                </span>
                                                <span class="font-bold text-slate-800 dark:text-white">{{ $item['qty'] }} {{ $tool->uom }}</span>
                                            </div>
                                        @else
                                            <div class="flex flex-col py-1.5 border-b border-slate-100/50 dark:border-slate-800 last:border-none">
                                                <div class="flex justify-between items-center">
                                                    <span class="text-slate-855 dark:text-slate-200 font-bold"><i class="fa-solid fa-barcode text-[9px] mr-1.5"></i> {{ $item['id_number'] }}</span>
                                                    <span class="font-bold text-slate-855 dark:text-white">{{ $item['qty'] }} {{ $tool->uom }}</span>
                                                </div>
                                                <div class="flex justify-between text-[9px] text-slate-450 mt-0.5">
                                                    <span>Loc: {{ $item['location'] }}</span>
                                                    <span>Condition: {{ $item['physical_rate'] }}</span>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Machining Settings parameters Table -->
            <div class="bg-white dark:bg-gray-900 border border-slate-100 dark:border-gray-800 rounded-xs p-6 shadow-xs">
                <div class="mb-4">
                    <h4 class="text-xs font-bold text-slate-700 dark:text-slate-200 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fa-solid fa-gears text-primary-500"></i> Machining Parameter Settings
                    </h4>
                    <p class="text-[10px] text-gray-500 dark:text-gray-400 mt-1">Recommended machining configuration per category of workpiece material.</p>
                </div>

                <div class="overflow-x-auto border border-slate-100 dark:border-gray-800 rounded-xs">
                    <table class="min-w-full divide-y divide-slate-100 dark:divide-gray-800 text-xs text-left">
                        <thead class="bg-slate-50 dark:bg-slate-800/40 text-[9px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-wider">
                            <tr>
                                <th scope="col" class="px-4 py-3 w-44">Material Category</th>
                                <th scope="col" class="px-4 py-3 text-center w-28">Spindle Speed (n)<br><span class="text-[8px] font-normal lowercase">rev/min</span></th>
                                <th scope="col" class="px-4 py-3 text-center w-28">Table Feed (Vf)<br><span class="text-[8px] font-normal lowercase">mm/min</span></th>
                                <th scope="col" class="px-4 py-3 text-center w-28">Depth of Cut (ap)<br><span class="text-[8px] font-normal lowercase">mm</span></th>
                                <th scope="col" class="px-4 py-3 text-center w-24">Step Over (%)</th>
                                <th scope="col" class="px-3 py-3 text-center w-20">CNC Small<br><span class="text-[8px] font-normal uppercase">plant b</span></th>
                                <th scope="col" class="px-3 py-3 text-center w-28">CNC Hartford<br><span class="text-[8px] font-normal uppercase">plant f</span></th>
                                <th scope="col" class="px-4 py-3 text-center w-24">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-gray-800 bg-white dark:bg-gray-900">
                            @if($tool->settings && $tool->settings->count() > 0)
                                @foreach($tool->settings as $setting)
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50">
                                        <td class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-350">{{ $setting->material_category }}</td>
                                        <td class="px-4 py-3 text-center font-mono">{{ number_format($setting->spindle_speed) }}</td>
                                        <td class="px-4 py-3 text-center font-mono">{{ number_format($setting->table_feed) }}</td>
                                        <td class="px-4 py-3 text-center font-mono">{{ $setting->depth_of_cut ?? '-' }}</td>
                                        <td class="px-4 py-3 text-center font-mono">{{ $setting->step_over ? $setting->step_over . '%' : '-' }}</td>
                                        <td class="px-3 py-3 text-center">
                                            <div class="flex items-center justify-center">
                                                @if($setting->cnc_small_plant_b)
                                                    <i class="fa-solid fa-circle-check text-emerald-500 text-sm"></i>
                                                @else
                                                    <i class="fa-solid fa-circle-xmark text-slate-300 dark:text-slate-700 text-sm"></i>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            <div class="flex items-center justify-center">
                                                @if($setting->cnc_big_hartford_plant_f)
                                                    <i class="fa-solid fa-circle-check text-emerald-500 text-sm"></i>
                                                @else
                                                    <i class="fa-solid fa-circle-xmark text-slate-300 dark:text-slate-700 text-sm"></i>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="px-2 py-0.5 rounded-xs text-[9px] font-bold uppercase tracking-wider {{ $setting->status === 'USE' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400' : 'bg-rose-100 text-rose-700 dark:bg-rose-900/20 dark:text-rose-400' }}">
                                                {{ $setting->status }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="8" class="text-center py-6 text-slate-450 dark:text-slate-500 text-xs font-semibold">
                                        <i class="fa-solid fa-gears text-lg mb-2 block opacity-40"></i>
                                        No machining parameter settings configured for this tool.
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Footer Meta -->
        <div class="w-full max-w-7xl mt-6 px-6 py-5 bg-white dark:bg-slate-900 rounded-xs border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col md:flex-row justify-between items-center gap-4 text-xs font-medium text-slate-400 tracking-wider">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-clock opacity-60"></i>
                Data as of {{ date('d M Y, H:i:s') }}
            </div>
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-shield-circle-check text-primary-500"></i>
                Official Tool Inventory Record
            </div>
        </div>

        <div class="mt-8 text-center">
            <p class="text-[10px] font-medium text-slate-400 tracking-[0.4em]">PROMISE <span class="text-primary-600">INVENTORY</span> SYSTEM</p>
        </div>
    </div>

    <!-- Modal: Image Preview -->
    <div id="modal-preview" class="modal-container hidden fixed inset-0 z-[110] flex items-center justify-center bg-slate-900/60 p-4">
        <div class="relative max-w-4xl w-full h-full flex items-center justify-center p-4">
            <img id="img-full" src="" class="max-w-full max-h-[90vh] object-contain rounded-xs shadow-2xl transition-all duration-300">
            <button class="close-preview absolute top-4 right-4 text-white text-3xl hover:text-red-400 hover:scale-110 active:scale-95 transition-all drop-shadow-lg" title="Close" onclick="document.getElementById('modal-preview').classList.add('hidden')"><i class="fa-solid fa-xmark"></i></button>
        </div>
    </div>

    <script>
        window.previewImg = (src) => {
            document.getElementById('img-full').src = src;
            document.getElementById('modal-preview').classList.remove('hidden');
        };
    </script>
</body>
</html>
