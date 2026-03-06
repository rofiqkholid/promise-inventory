<!-- Modern Balanced Stock Alert Modal -->
@if(isset($stockAlerts))
<div id="stockAlertModal" 
     class="fixed inset-0 z-[9999] hidden opacity-0 transition-all duration-300" 
     style="display: none;">
    
    <!-- Modern Backdrop -->
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" id="stockAlertBackdrop"></div>
    
    <!-- Modal Container -->
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="relative w-full max-w-xl transform transition-all duration-300 ease-out scale-95 opacity-0" id="stockAlertContent">
            
            <!-- Card Body -->
            <div class="bg-white dark:bg-gray-800 rounded-xs overflow-hidden border border-slate-200 dark:border-gray-700 flex flex-col">
                
                <!-- Professional Header -->
                <div class="px-6 py-4 border-b border-slate-100 dark:border-gray-700 flex items-center justify-between bg-white dark:bg-gray-800">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xs bg-rose-50 dark:bg-rose-900/20 text-rose-500 dark:text-rose-400 flex items-center justify-center border border-rose-100 dark:border-rose-900/30">
                            <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900 dark:text-white tracking-tight leading-none">Stock Alerts</h3>
                            <p class="text-[10px] font-medium text-slate-400 dark:text-gray-500 uppercase tracking-widest mt-1">
                                {{ count($stockAlerts) }} item{{ count($stockAlerts) !== 1 ? 's' : '' }} need attention
                            </p>
                        </div>
                    </div>
                    <button type="button" id="closeStockAlert" class="w-8 h-8 flex items-center justify-center rounded-xs text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-gray-700 transition-all">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <!-- Scrollable Content -->
                <div class="max-h-[60vh] overflow-y-auto custom-scrollbar bg-slate-50/50 dark:bg-gray-900/50 p-4">
                    @php
                        $criticalItems = $stockAlerts->where('status', 'Critical');
                        $warningItems = $stockAlerts->where('status', 'Warning');
                    @endphp

                    <div class="space-y-4">
                        @if(count($stockAlerts) === 0)
                            <div class="py-12 flex flex-col items-center justify-center text-center">
                                <div class="w-16 h-16 rounded-full bg-emerald-50 dark:bg-emerald-900/10 text-emerald-500 flex items-center justify-center mb-4 border border-emerald-100 dark:border-emerald-900/30">
                                    <i class="fa-solid fa-check text-3xl"></i>
                                </div>
                                <h4 class="text-base font-semibold text-slate-900 dark:text-white tracking-tight">All Clear</h4>
                                <p class="text-xs text-slate-500 dark:text-gray-400 mt-1 max-w-[200px]">
                                    Stock levels are within safe ranges.
                                </p>
                            </div>
                        @else
                            @if(count($criticalItems) > 0)
                                <div>
                                    <div class="flex items-center gap-3 mb-3">
                                        <span class="text-[10px] font-bold uppercase tracking-widest text-rose-600 dark:text-rose-400 bg-rose-100 dark:bg-rose-900/30 px-2 py-1 rounded-xs border border-rose-200 dark:border-rose-800">Critical Stock</span>
                                        <div class="h-px flex-1 bg-rose-200 dark:bg-rose-900/30"></div>
                                    </div>
                                    <div class="space-y-2">
                                        @foreach($criticalItems as $item)
                                        <a href="{{ route('inventory.stockMonitoring') }}?search={{ urlencode($item->part_no) }}" 
                                           class="block p-3 bg-white dark:bg-gray-800 rounded-xs border border-slate-200 dark:border-gray-700 hover:border-rose-300 dark:hover:border-rose-700 transition-colors group"
                                           title="Click to view in Stock Monitoring">
                                            <div class="flex items-start justify-between gap-4">
                                                <div class="min-w-0 flex-1">
                                                    <div class="flex items-center gap-2 mb-1">
                                                        <span class="text-xs font-bold text-slate-900 dark:text-white tracking-tight truncate group-hover:text-rose-600 transition-colors">
                                                            {{ $item->part_no }}{{ $item->revision ? ' - ' . $item->revision : '' }}
                                                        </span>
                                                    </div>
                                                    <div class="flex items-center gap-2 text-[10px] text-slate-500 dark:text-gray-400">
                                                        <span class="font-semibold uppercase tracking-wide">{{ $item->customer_code }}</span>
                                                        <span class="text-slate-300 dark:text-gray-600">|</span>
                                                        <span class="font-medium">{{ $item->model_name }}</span>
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-3 bg-slate-50 dark:bg-gray-700/50 px-3 py-1.5 rounded border border-slate-100 dark:border-gray-700">
                                                    <div class="text-right">
                                                        <div class="text-[9px] text-slate-400 uppercase tracking-widest font-bold">Stock</div>
                                                        <div class="text-sm font-bold text-rose-600 dark:text-rose-400 tabular-nums leading-none">{{ number_format($item->current_stock_qty) }}</div>
                                                    </div>
                                                    <div class="w-px h-6 bg-slate-200 dark:bg-gray-600"></div>
                                                    <div class="text-right">
                                                        <div class="text-[9px] text-slate-400 uppercase tracking-widest font-bold">Min</div>
                                                        <div class="text-sm font-bold text-slate-700 dark:text-gray-300 tabular-nums leading-none">{{ number_format($item->min_stock) }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if(count($warningItems) > 0)
                                <div class="mt-6">
                                    <div class="flex items-center gap-3 mb-3">
                                        <span class="text-[10px] font-bold uppercase tracking-widest text-amber-600 dark:text-amber-400 bg-amber-100 dark:bg-amber-900/30 px-2 py-1 rounded-xs border border-amber-200 dark:border-amber-800">Overstock</span>
                                        <div class="h-px flex-1 bg-amber-200 dark:bg-amber-900/30"></div>
                                    </div>
                                    <div class="space-y-2">
                                        @foreach($warningItems as $item)
                                        <a href="{{ route('inventory.stockMonitoring') }}?search={{ urlencode($item->part_no) }}"
                                           class="block p-3 bg-white dark:bg-gray-800 rounded-xs border border-slate-200 dark:border-gray-700 hover:border-amber-300 dark:hover:border-amber-700 transition-colors group"
                                           title="Click to view in Stock Monitoring">
                                            <div class="flex items-start justify-between gap-4">
                                                <div class="min-w-0 flex-1">
                                                    <div class="flex items-center gap-2 mb-1">
                                                        <span class="text-xs font-bold text-slate-900 dark:text-white tracking-tight truncate group-hover:text-amber-600 transition-colors">
                                                            {{ $item->part_no }}{{ $item->revision ? ' - ' . $item->revision : '' }}
                                                        </span>
                                                    </div>
                                                    <div class="flex items-center gap-2 text-[10px] text-slate-500 dark:text-gray-400">
                                                        <span class="font-semibold uppercase tracking-wide">{{ $item->customer_code }}</span>
                                                        <span class="text-slate-300 dark:text-gray-600">|</span>
                                                        <span class="font-medium">{{ $item->model_name }}</span>
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-3 bg-slate-50 dark:bg-gray-700/50 px-3 py-1.5 rounded border border-slate-100 dark:border-gray-700">
                                                    <div class="text-right">
                                                        <div class="text-[9px] text-slate-400 uppercase tracking-widest font-bold">Stock</div>
                                                        <div class="text-sm font-bold text-amber-600 dark:text-amber-400 tabular-nums leading-none">{{ number_format($item->current_stock_qty) }}</div>
                                                    </div>
                                                    <div class="w-px h-6 bg-slate-200 dark:bg-gray-600"></div>
                                                    <div class="text-right">
                                                        <div class="text-[9px] text-slate-400 uppercase tracking-widest font-bold">Max</div>
                                                        <div class="text-sm font-bold text-slate-700 dark:text-gray-300 tabular-nums leading-none">{{ number_format($item->min_stock * 3) }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>

                <!-- Action Footer -->
                <div class="p-4 border-t border-slate-100 dark:border-gray-800 bg-white dark:bg-gray-800">
                    <button type="button" id="closeStockAlertBtn" class="w-full h-10 bg-primary-600 hover:bg-primary-700 text-white rounded-xs font-semibold text-xs tracking-widest transition-all active:scale-[0.98] flex items-center justify-center gap-2">
                        <i class="fa-solid fa-check text-sm"></i>
                        Acknowledge
                    </button>
                    <p class="mt-2 text-center text-[9px] font-bold text-slate-300 dark:text-gray-600 uppercase tracking-[0.2em]">
                        Promise Inventory
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    #stockAlertModal.show #stockAlertContent {
        animation: modalEnter 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    @keyframes modalEnter {
        from {
            transform: scale(0.9) translateY(20px);
            opacity: 0;
        }
        to {
            transform: scale(1) translateY(0);
            opacity: 1;
        }
    }

    .custom-scrollbar::-webkit-scrollbar {
        width: 5px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #e2e8f0;
        border-radius: 10px;
    }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #334155;
    }
</style>

<script>
    (function() {
        const modal = document.getElementById('stockAlertModal');
        const content = document.getElementById('stockAlertContent');
        
        function openModal() {
            modal.style.display = 'block';
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            
            requestAnimationFrame(() => {
                modal.style.opacity = '1';
                modal.classList.add('show');
            });
        }

        function closeModal() {
            modal.style.opacity = '0';
            content.style.transform = 'scale(0.9) translateY(20px)';
            content.style.opacity = '0';
            
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('show');
                modal.style.display = 'none';
                document.body.style.overflow = '';
                content.style.transform = '';
                content.style.opacity = '';
            }, 300);
        }

        window.addEventListener('open-stock-alert', openModal);
        document.getElementById('closeStockAlert')?.addEventListener('click', closeModal);
        document.getElementById('closeStockAlertBtn')?.addEventListener('click', closeModal);
        document.getElementById('stockAlertBackdrop')?.addEventListener('click', closeModal);
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
        });
        @if(isset($stockAlertAutoOpen) && $stockAlertAutoOpen)
        openModal();
        @endif
    })();
</script>
@endif
