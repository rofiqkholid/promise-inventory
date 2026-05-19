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
    <div class="min-h-screen flex flex-col items-center justify-start p-4 md:p-10 lg:p-20">
        
        <!-- Navigation Header -->
        <div class="w-full max-w-2xl mb-6 flex justify-between items-center bg-white dark:bg-slate-900 px-5 py-4 rounded-xs border border-slate-200 dark:border-slate-800 shadow-sm">
             @auth
             <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-primary-600 transition-colors">
                <i class="fa-solid fa-chevron-left text-[10px]"></i> Dashboard
             </a>
             @else
             <a href="{{ route('login') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-primary-600 hover:text-primary-700 transition-colors">
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
        <div class="w-full max-w-2xl grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            @php
                $txRoute = $tool->category?->moving_type === 'fast'
                    ? route('inventory.tool.fast-stock.index', ['tool_id' => $tool->id])
                    : route('inventory.tool.slow-batch.index', ['tool_id' => $tool->id]);
                
                $stoUrl = $activeStoId 
                    ? route('inventory.tool.sto.show', ['id' => $activeStoId, 'tool_id' => $tool->id])
                    : route('inventory.tool.sto.index');
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

        <!-- Main Details Card -->
        <div class="w-full max-w-2xl bg-white dark:bg-slate-900 rounded-xs border border-slate-200 dark:border-slate-800 shadow-xl shadow-slate-200/50 dark:shadow-none overflow-hidden">
            
            <!-- Tool Heading -->
            <div class="px-8 py-6 border-b border-slate-100 dark:border-slate-800 flex justify-between items-start gap-4">
                <div>
                    <h1 class="text-xl xl:text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                        {{ $tool->name }}
                    </h1>
                    <p class="text-xs font-bold text-slate-400 mt-1 uppercase tracking-wider">{{ $tool->brand }} · {{ $tool->spec_code ?? 'No Spec' }}</p>
                </div>
                <span class="px-2 py-0.5 bg-primary-50 dark:bg-primary-950/40 text-primary-600 dark:text-primary-400 border border-primary-100 dark:border-primary-900 text-[9px] font-black uppercase tracking-wider rounded-xs mt-1">
                    {{ $tool->category?->name ?? 'TOOL' }}
                </span>
            </div>

            <div class="p-8">
                <!-- Unified Stock Level Indicator -->
                <div class="mb-10">
                    @php
                        $status = 'safe';
                        if ($totalQty < $tool->qty_min) {
                            $status = 'critical';
                        } elseif ($totalQty == $tool->qty_min) {
                            $status = 'warning';
                        } elseif ($totalQty > $tool->qty_max) {
                            $status = 'over';
                        }

                        $statusStyles = [
                            'safe' => [
                                'gradient' => 'from-emerald-600 to-emerald-500',
                                'text' => 'text-white', 
                                'label' => 'Safe Stock Level',
                                'icon' => 'fa-circle-check'
                            ],
                            'warning' => [
                                'gradient' => 'from-amber-500 to-amber-400',
                                'text' => 'text-white', 
                                'label' => 'Warning Stock Level',
                                'icon' => 'fa-triangle-exclamation'
                            ],
                            'critical' => [
                                'gradient' => 'from-red-600 to-red-500',
                                'text' => 'text-white', 
                                'label' => 'Critical Stock Alert',
                                'icon' => 'fa-circle-exclamation'
                            ],
                            'over' => [
                                'gradient' => 'from-primary-600 to-primary-500',
                                'text' => 'text-white', 
                                'label' => 'Overstock Detected',
                                'icon' => 'fa-boxes-stacked'
                            ]
                        ];
                        $st = $statusStyles[$status] ?? $statusStyles['safe'];
                    @endphp
                    
                    <div class="w-full h-11 bg-gradient-to-r {{ $st['gradient'] }} {{ $st['text'] }} rounded-t-xs flex items-center justify-center gap-3 shadow-lg">
                        <i class="fa-solid {{ $st['icon'] }} text-sm"></i>
                        <span class="font-bold text-[11px] uppercase tracking-wider">{{ $st['label'] }}</span>
                    </div>

                    <div class="bg-primary-50 dark:bg-primary-900/50 p-6 rounded-b-xs border-x border-b border-slate-100 dark:border-slate-800 transition-all text-center">
                        <span class="text-xs font-bold text-slate-400 block mb-2 uppercase tracking-wide">Total Balance Available</span>
                        <div class="flex items-baseline justify-center gap-2">
                            <span class="text-4xl font-bold text-slate-900 dark:text-white leading-none">{{ $totalQty }}</span>
                            <span class="text-xs font-bold text-slate-400 tracking-wider uppercase">{{ $tool->uom }}</span>
                        </div>
                    </div>
                </div>

                <!-- Locations & Stock Details -->
                <div class="mb-10">
                    <h3 class="text-xs font-bold text-slate-400 tracking-wider mb-4 flex items-center gap-3">
                        Physical Storage Locations
                        <div class="flex-1 h-px bg-slate-100 dark:bg-slate-800"></div>
                    </h3>
                    <div class="space-y-2 border border-slate-100 dark:border-slate-800 rounded-xs p-3 bg-slate-50/50 dark:bg-slate-900/40">
                        @if(empty($stockInfo))
                            <p class="text-slate-400 text-center py-4 text-xs font-medium">No storage records in this warehouse.</p>
                        @else
                            @foreach($stockInfo as $item)
                                @if($tool->category?->moving_type === 'fast')
                                    <div class="flex justify-between items-center py-1.5 border-b border-slate-100/50 dark:border-slate-800 last:border-none">
                                        <span class="text-slate-700 dark:text-slate-350 text-xs font-semibold">
                                            <i class="fa-solid fa-location-dot text-slate-400 mr-1.5"></i> {{ $item['location'] }}
                                        </span>
                                        <span class="font-bold text-slate-800 dark:text-white text-xs">{{ $item['qty'] }} {{ $tool->uom }}</span>
                                    </div>
                                @else
                                    <div class="flex flex-col py-2 border-b border-slate-100/50 dark:border-slate-800 last:border-none">
                                        <div class="flex justify-between items-center">
                                            <span class="text-slate-800 dark:text-slate-200 text-xs font-bold">{{ $item['id_number'] }}</span>
                                            <span class="font-bold text-slate-800 dark:text-white text-xs">{{ $item['qty'] }} {{ $tool->uom }}</span>
                                        </div>
                                        <div class="flex justify-between text-[10px] text-slate-400 mt-1">
                                            <span>Loc: {{ $item['location'] }}</span>
                                            <span>Age/Condition: {{ $item['physical_rate'] }} ({{ $item['purchase_date'] }})</span>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @endif
                    </div>
                </div>

                <!-- Detail List specs -->
                <div class="space-y-4 mb-10">
                    <h3 class="text-xs font-bold text-slate-400 tracking-wider mb-4 flex items-center gap-3">
                        Technical Specifications
                        <div class="flex-1 h-px bg-slate-100 dark:bg-slate-800"></div>
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-4">
                        <div class="flex flex-col gap-1 border-b border-slate-50 dark:border-slate-800/40 pb-1.5">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Dimension (ø / T)</span>
                            <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                                {{ $tool->dimension ?? '-' }}
                                @if($tool->length)
                                    x {{ $tool->length }} mm
                                @endif
                            </span>
                        </div>
                        <div class="flex flex-col gap-1 border-b border-slate-50 dark:border-slate-800/40 pb-1.5">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Material Type</span>
                            <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ $tool->material_type ?? '-' }}</span>
                        </div>
                        <div class="flex flex-col gap-1 border-b border-slate-50 dark:border-slate-800/40 pb-1.5">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">HRC (Kekerasan)</span>
                            <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ $tool->hrc ?? '-' }}</span>
                        </div>
                        <div class="flex flex-col gap-1 border-b border-slate-50 dark:border-slate-800/40 pb-1.5">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Safety Limits (Min/Max)</span>
                            <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ $tool->qty_min ?? 0 }} / {{ $tool->qty_max ?? 0 }} {{ $tool->uom }}</span>
                        </div>
                    </div>
                </div>

                <!-- Machining Settings parameters -->
                @if($tool->settings && $tool->settings->count() > 0)
                    <div class="mb-2">
                        <h3 class="text-xs font-bold text-slate-400 tracking-wider mb-4 flex items-center gap-3">
                            Machining Parameters
                            <div class="flex-1 h-px bg-slate-100 dark:bg-slate-800"></div>
                        </h3>
                        
                        <div class="overflow-x-auto border border-slate-200 dark:border-slate-800 rounded-xs">
                            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-[11px] text-left">
                                <thead class="bg-slate-50 dark:bg-slate-800/40 text-[9px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-wider">
                                    <tr>
                                        <th class="px-3 py-2">Material</th>
                                        <th class="px-3 py-2 text-center">Spindle (rpm)</th>
                                        <th class="px-3 py-2 text-center">Feed (mm/min)</th>
                                        <th class="px-3 py-2 text-center">DoC (ap)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-150 dark:divide-slate-800 bg-white dark:bg-slate-900">
                                    @foreach($tool->settings as $setting)
                                        @if($setting->status === 'USE')
                                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50">
                                                <td class="px-3 py-2 font-semibold text-slate-700 dark:text-slate-300">{{ $setting->material_category }}</td>
                                                <td class="px-3 py-2 text-center font-mono">{{ number_format($setting->spindle_speed) }}</td>
                                                <td class="px-3 py-2 text-center font-mono">{{ number_format($setting->table_feed) }}</td>
                                                <td class="px-3 py-2 text-center font-mono">{{ $setting->depth_of_cut ?? '-' }}</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

            </div>

            <!-- Footer Meta -->
            <div class="px-8 py-5 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-100 dark:border-slate-800 flex flex-col md:flex-row justify-between items-center gap-4 text-xs font-medium text-slate-400 tracking-wider">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-clock opacity-60"></i>
                    Data as of {{ date('d M Y, H:i:s') }}
                </div>
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-shield-circle-check text-primary-500"></i>
                    Official Tool Inventory Record
                </div>
            </div>
        </div>

        <div class="mt-8 text-center">
            <p class="text-[10px] font-medium text-slate-400 tracking-[0.4em]">PROMISE <span class="text-primary-600">INVENTORY</span> SYSTEM</p>
        </div>
    </div>
</body>
</html>
