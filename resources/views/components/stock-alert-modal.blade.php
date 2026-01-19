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
            <div class="bg-white dark:bg-gray-900 rounded-2xl overflow-hidden border border-gray-200 dark:border-gray-800 flex flex-col">
                
                <!-- Professional Header -->
                <div class="px-7 py-6 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between bg-slate-50/20 dark:bg-gray-800/20">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-rose-50 dark:bg-rose-900/10 text-rose-500 dark:text-rose-400 flex items-center justify-center border border-rose-100/50 dark:border-rose-800/30 relative overflow-hidden">
                            <i class="fa-solid fa-triangle-exclamation text-xl relative z-10"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-slate-800 dark:text-white leading-tight tracking-tight">Stock Alerts</h3>
                            <p class="text-xs text-slate-500 dark:text-gray-400 font-semibold tracking-wide uppercase mt-0.5">
                                {{ count($stockAlerts) }} item{{ count($stockAlerts) !== 1 ? 's' : '' }} need attention
                            </p>
                        </div>
                    </div>
                    <button type="button" id="closeStockAlert" class="w-10 h-10 flex items-center justify-center rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-gray-200 hover:bg-slate-100 dark:hover:bg-gray-800 transition-all">
                        <i class="fa-solid fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Scrollable Content -->
                <div class="max-h-[55vh] overflow-y-auto custom-scrollbar bg-white dark:bg-gray-900">
                    @php
                        $criticalItems = $stockAlerts->where('status', 'Critical');
                        $warningItems = $stockAlerts->where('status', 'Warning');
                    @endphp

                    <div class="p-4 space-y-2">
                        @if(count($stockAlerts) === 0)
                            <div class="py-12 flex flex-col items-center justify-center text-center">
                                <div class="w-20 h-20 rounded-full bg-emerald-50 dark:bg-emerald-900/10 text-emerald-500 flex items-center justify-center mb-4">
                                    <i class="fa-solid fa-check-circle text-4xl"></i>
                                </div>
                                <h4 class="text-lg font-bold text-slate-800 dark:text-white">All Clear!</h4>
                                <p class="text-sm text-slate-500 dark:text-gray-400 mt-1 max-w-[200px]">
                                    All stock levels are within safe operating ranges.
                                </p>
                            </div>
                        @else
                            @if(count($criticalItems) > 0)
                                <div class="px-4 py-3 flex items-center gap-3">
                                    <span class="text-[11px] font-bold uppercase tracking-[0.15em] text-rose-500 dark:text-rose-400 bg-rose-50 dark:bg-rose-900/20 px-3 py-1 rounded-full border border-rose-100 dark:border-rose-800/30">Critical Stock</span>
                                    <div class="h-px flex-1 bg-gradient-to-r from-rose-100 to-transparent dark:from-rose-900/30"></div>
                                </div>
                                @foreach($criticalItems as $item)
                                <div class="px-5 py-4 rounded-xl bg-slate-50/50 dark:bg-gray-800/20 hover:bg-slate-100/50 dark:hover:bg-gray-800/40 transition-all border border-gray-100 dark:border-gray-800 hover:border-rose-200 dark:hover:border-rose-900/30 group mb-2">
                                    <div class="flex items-center justify-between gap-6">
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center gap-2">
                                                <div class="text-base font-bold text-slate-800 dark:text-gray-100 truncate group-hover:text-rose-600 dark:group-hover:text-rose-400 transition-colors">
                                                    {{ $item->part_no }}{{ $item->revision ? ' - ' . $item->revision : '' }}
                                                </div>
                                                <a href="{{ route('inventory.stockMonitoring') }}?search={{ $item->part_no }}" class="opacity-0 group-hover:opacity-100 transition-opacity text-blue-500 hover:text-blue-600 dark:text-blue-400 p-1" title="View Details">
                                                    <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                                                </a>
                                            </div>
                                            <div class="flex items-center gap-3 mt-1.5">
                                                <span class="text-[11px] font-bold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 px-2 py-0.5 rounded border border-blue-100 dark:border-blue-800/30">{{ $item->model_name }}</span>
                                                <span class="text-[11px] text-slate-400 dark:text-gray-500 font-bold uppercase tracking-wider">{{ $item->customer_code }}</span>
                                            </div>
                                        </div>
                                        <div class="shrink-0">
                                            <div class="flex items-center gap-4 px-4 py-2 bg-slate-100/30 dark:bg-gray-800/60 rounded-xl border border-gray-200 dark:border-gray-700">
                                                <div class="text-center min-w-[60px]">
                                                    <div class="text-[9px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-tighter mb-0.5">Stock (Pcs)</div>
                                                    <div class="text-xl font-bold text-rose-600 dark:text-rose-400 leading-none tabular-nums">{{ number_format($item->current_stock_qty) }}</div>
                                                </div>
                                                <div class="h-8 w-px bg-gray-200 dark:bg-gray-700"></div>
                                                <div class="text-center min-w-[60px]">
                                                    <div class="text-[9px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-tighter mb-0.5">Min (Pcs)</div>
                                                    <div class="text-xl font-bold text-slate-700 dark:text-gray-300 leading-none tabular-nums">{{ number_format($item->min_stock) }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            @endif

                            @if(count($warningItems) > 0)
                                <div class="pt-6 px-4 py-3 flex items-center gap-3">
                                    <span class="text-[11px] font-bold uppercase tracking-[0.15em] text-amber-500 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 px-3 py-1 rounded-full border border-amber-100 dark:border-amber-800/30">Overstock Warning</span>
                                    <div class="h-px flex-1 bg-gradient-to-r from-amber-100 to-transparent dark:from-amber-900/30"></div>
                                </div>
                                @foreach($warningItems as $item)
                                <div class="px-5 py-4 rounded-xl bg-slate-50/50 dark:bg-gray-800/20 hover:bg-slate-100/50 dark:hover:bg-gray-800/40 transition-all border border-gray-100 dark:border-gray-800 hover:border-amber-200 dark:hover:border-amber-900/30 group mb-2">
                                    <div class="flex items-center justify-between gap-6">
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center gap-2">
                                                <div class="text-base font-bold text-slate-800 dark:text-gray-100 truncate group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">
                                                    {{ $item->part_no }}{{ $item->revision ? ' - ' . $item->revision : '' }}
                                                </div>
                                                <a href="{{ route('inventory.stockMonitoring') }}?search={{ $item->part_no }}" class="opacity-0 group-hover:opacity-100 transition-opacity text-blue-500 hover:text-blue-600 dark:text-blue-400 p-1" title="View Details">
                                                    <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                                                </a>
                                            </div>
                                            <div class="flex items-center gap-3 mt-1.5">
                                                <span class="text-[11px] font-bold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 px-2 py-0.5 rounded border border-blue-100 dark:border-blue-800/30">{{ $item->model_name }}</span>
                                                <span class="text-[11px] text-slate-400 dark:text-gray-500 font-bold uppercase tracking-wider">{{ $item->customer_code }}</span>
                                            </div>
                                        </div>
                                        <div class="shrink-0">
                                            <div class="flex items-center gap-4 px-4 py-2 bg-slate-100/30 dark:bg-gray-800/60 rounded-xl border border-gray-200 dark:border-gray-700">
                                                <div class="text-center min-w-[60px]">
                                                    <div class="text-[9px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-tighter mb-0.5">Stock (Pcs)</div>
                                                    <div class="text-xl font-bold text-amber-600 dark:text-amber-400 leading-none tabular-nums">{{ number_format($item->current_stock_qty) }}</div>
                                                </div>
                                                <div class="h-8 w-px bg-gray-200 dark:bg-gray-700"></div>
                                                <div class="text-center min-w-[60px]">
                                                    <div class="text-[9px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-tighter mb-0.5">Max (Pcs)</div>
                                                    <div class="text-xl font-bold text-slate-700 dark:text-gray-300 leading-none tabular-nums">{{ number_format($item->min_stock * 3) }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            @endif
                        @endif
                    </div>
                </div>

                <!-- Action Footer -->
                <div class="p-8 border-t border-gray-200 dark:border-gray-800 bg-slate-50/20 dark:bg-gray-900">
                    <button type="button" id="closeStockAlertBtn" class="w-full h-14 bg-slate-800 dark:bg-blue-600 hover:bg-slate-900 dark:hover:bg-blue-700 text-white rounded-xl font-bold text-base tracking-wide transition-all active:scale-[0.98] flex items-center justify-center gap-3">
                        <i class="fa-solid fa-check-circle text-lg"></i>
                        Acknowledge & Close
                    </button>
                    <p class="mt-4 text-center text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-[0.2em]">
                        Promise Inventory Management
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
