<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Product Info - {{ $product->part_no }}</title>
    
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

             <div class="flex items-center gap-3">
                <span class="w-2 h-2 rounded-full {{ Auth::check() ? 'bg-emerald-500' : 'bg-slate-300' }}"></span>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                    {{ Auth::check() ? Auth::user()->name : 'Guest' }}
                </span>
             </div>
        </div>

        <!-- Quick Actions -->
        <div class="w-full max-w-2xl grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <a href="{{ route('inventory.transaction', ['product' => $product->hash_id]) }}" 
               class="group h-14 bg-primary-600 text-white rounded-xs flex items-center justify-center gap-3 font-bold text-xs uppercase tracking-widest hover:bg-primary-700 transition-all">
                <i class="fa-solid fa-plus-circle text-lg text-primary-200"></i>
                <span>New Transaction</span>
                @guest <i class="fa-solid fa-lock text-[10px] opacity-40"></i> @endguest
            </a>
            
            @php
                $stoUrl = $activeStoHashId 
                    ? route('inventory.sto.show', ['id' => $activeStoHashId, 'product' => $product->hash_id])
                    : route('inventory.sto.index');
            @endphp
            <a href="{{ $stoUrl }}" 
               class="group h-14 bg-white dark:bg-slate-900 text-slate-900 dark:text-white border border-slate-200 dark:border-slate-800 rounded-xs flex items-center justify-center gap-3 font-bold text-xs uppercase tracking-widest hover:bg-slate-50 dark:hover:bg-slate-800 transition-all">
                <i class="fa-solid fa-clipboard-check text-lg text-emerald-500"></i>
                <span>Stock Opname</span>
                @guest <i class="fa-solid fa-lock text-[10px] opacity-40"></i> @endguest
            </a>
        </div>

        <div class="w-full max-w-2xl bg-white dark:bg-slate-900 rounded-xs border border-slate-200 dark:border-slate-800 shadow-xl shadow-slate-200/50 dark:shadow-none overflow-hidden">
            
            <!-- Status Badge Area -->
            <div class="px-8 py-6 border-b border-slate-100 dark:border-slate-800">
                <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white flex items-center gap-3">
                    {{ $product->part_no }}
                    @if($product->revision)
                    <span class="text-primary-600 dark:text-primary-400 font-mono text-xl">/ {{ $product->revision }}</span>
                    @endif
                </h1>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mt-1">{{ $product->part_name }}</p>
            </div>

            <div class="p-8">
                <!-- Unified Stock KPI Section -->
                <div class="mb-10">
                    @php
                        $statusStyles = [
                            'safe' => [
                                'bg' => 'bg-emerald-600', 
                                'gradient' => 'from-emerald-600 to-emerald-500',
                                'text' => 'text-white', 
                                'label' => 'Safe Stock Level',
                                'icon' => 'fa-circle-check'
                            ],
                            'warning' => [
                                'bg' => 'bg-amber-500', 
                                'gradient' => 'from-amber-500 to-amber-400',
                                'text' => 'text-white', 
                                'label' => 'Warning Stock Level',
                                'icon' => 'fa-triangle-exclamation'
                            ],
                            'danger' => [
                                'bg' => 'bg-red-600', 
                                'gradient' => 'from-red-600 to-red-500',
                                'text' => 'text-white', 
                                'label' => 'Critical Stock Alert',
                                'icon' => 'fa-circle-exclamation'
                            ],
                            'over' => [
                                'bg' => 'bg-primary-600', 
                                'gradient' => 'from-primary-600 to-primary-500',
                                'text' => 'text-white', 
                                'label' => 'Overstock Detected',
                                'icon' => 'fa-boxes-stacked'
                            ]
                        ];
                        $st = $statusStyles[$product->status] ?? [
                            'bg' => 'bg-slate-600', 
                            'gradient' => 'from-slate-600 to-slate-500',
                            'text' => 'text-white', 
                            'label' => 'Unknown Status',
                            'icon' => 'fa-question-circle'
                        ];
                    @endphp
                    
                    <div class="w-full h-11 bg-gradient-to-r {{ $st['gradient'] }} {{ $st['text'] }} rounded-t-xs flex items-center justify-center gap-3 shadow-lg">
                        <i class="fa-solid {{ $st['icon'] }} text-sm"></i>
                        <span class="font-black text-[11px] uppercase tracking-[0.3em]">{{ $st['label'] }}</span>
                    </div>

                    <div class="bg-primary-50 dark:bg-primary-900/50 p-6 rounded-b-xs border-x border-b border-slate-100 dark:border-slate-800 transition-all text-center">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-3 text-center">Total Balance Available</span>
                        
                        <div class="flex flex-wrap items-center justify-center gap-x-4 gap-y-2">
                            {{-- PCS Section --}}
                            <div class="flex items-baseline gap-2">
                                <span class="text-4xl font-black text-slate-900 dark:text-white leading-none">{{ $product->balance_pcs }}</span>
                                <span class="text-xs font-bold text-slate-400 uppercase tracking-tighter">PCS</span>
                            </div>

                            {{-- Separator --}}
                            <span class="text-slate-300 dark:text-slate-700 font-light text-4xl hidden md:block">/</span>

                            {{-- Unit Section --}}
                            @php
                                $unitParts = explode(' ', $product->balance_unit);
                                $val = $unitParts[0] ?? '0';
                                $u = $unitParts[1] ?? 'UNIT';
                            @endphp
                            <div class="flex items-baseline gap-2">
                                <span class="text-4xl font-black text-primary-600 dark:text-primary-400 leading-none">{{ $val }}</span>
                                <span class="text-xs font-bold text-slate-400 uppercase tracking-tighter">{{ $u }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detail List -->
                <div class="space-y-4 mb-10">
                    <h3 class="text-[11px] font-bold text-slate-400 uppercase tracking-[0.2em] px-1 mb-4 flex items-center gap-3">
                        Technical Specifications
                        <div class="flex-1 h-px bg-slate-100 dark:bg-slate-800"></div>
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-4 px-1">
                        <div class="flex flex-col gap-1">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Model Series</span>
                            <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ $product->model_name }}</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Customer / Partner</span>
                            <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ $product->customer_code }}</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Dimensions</span>
                            <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                {{ $product->dimension }}
                                <span class="text-[10px] text-slate-400 font-normal ml-1">{{ $product->dimension_label }}</span>
                            </span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Material Specification</span>
                            <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ $product->material }}</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Project Status</span>
                            <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                {{ $product->product_status ?: $product->model_project_status }}
                                @if($product->product_status) <span class="text-[9px] text-primary-500 font-bold ml-1 uppercase">(Override)</span> @endif
                            </span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Status Remark</span>
                            <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ $product->product_status_remark ?: '-' }}</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Safety Stock Level</span>
                            <span class="text-sm font-bold text-red-600">{{ $product->min_stock }} <small class="text-[10px] uppercase ml-0.5">PCS</small></span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Footer Meta -->
            <div class="px-8 py-5 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-100 dark:border-slate-800 flex flex-col md:flex-row justify-between items-center gap-4 text-[10px] font-semibold text-slate-400 uppercase tracking-widest">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-clock opacity-60"></i>
                    Data as of {{ date('d M Y, H:i:s') }}
                </div>
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-shield-circle-check text-primary-500"></i>
                    Official Inventory Record
                </div>
            </div>
        </div>

        <div class="mt-8 text-center">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.4em]">PROMISE <span class="text-primary-600">INVENTORY</span> SYSTEM</p>
        </div>
    </div>
</body>
</html>
