    <!-- Statistics Dashboard -->
    <div class="mb-6 bg-white dark:bg-gray-800 rounded-xs border border-slate-200 dark:border-gray-700 overflow-hidden relative">
        <div class="relative grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 divide-x divide-y md:divide-y-0 divide-gray-100 dark:divide-gray-700">
            <div class="p-4 flex flex-col items-center text-center group">
                <div class="w-10 h-10 mb-2 flex items-center justify-center bg-primary-50 dark:bg-primary-900/30 rounded-xs text-primary-600 dark:text-primary-400">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Recorded</span>
                <div class="flex items-baseline gap-1">
                    <span id="stat-total-items" class="text-xl font-bold text-gray-900 dark:text-white">{{ $stats['total_items'] }}</span>
                    <span class="text-[9px] font-bold text-primary-500 bg-primary-50 dark:bg-primary-900/40 px-1.5 py-0.5 rounded-xs"><span id="stat-progress">{{ $progress }}</span>%</span>
                </div>
                <span class="text-[9px] font-medium text-gray-400 mt-1 uppercase">Items Recorded</span>
            </div>

            <!-- Total Recorded PCS -->
            <div class="p-4 flex flex-col items-center text-center group">
                <div class="w-10 h-10 mb-2 flex items-center justify-center bg-primary-50 dark:bg-primary-900/30 rounded-xs text-primary-600 dark:text-primary-400">
                    <i class="fa-solid fa-calculator"></i>
                </div>
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Total Qty Counted</span>
                <span id="stat-total-recorded-pcs" class="text-xl font-bold text-primary-700 dark:text-primary-400 leading-none">
                    {{ number_format($stats['total_recorded_pcs'] ?? 0, 0) }} 
                </span>
                <span class="text-[9px] font-medium text-gray-400 mt-1 uppercase">PCS Recorded</span>
            </div>

            <!-- Remaining -->
            <div onclick="openRemainingModal()" class="p-4 flex flex-col items-center text-center group cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-all relative overflow-hidden">
                <div class="w-10 h-10 mb-2 flex items-center justify-center bg-amber-50 dark:bg-amber-900/30 rounded-xs text-amber-600 dark:text-amber-400">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
                <span class="text-[10px] font-semibold text-gray-500 uppercase tracking-wide mb-1">Remaining</span>
                <span id="stat-total-missing-items" class="text-xl font-bold text-amber-600 leading-none">
                    {{ $stats['total_missing_items'] ?? ($stats['total_count'] - $stats['total_items']) }}
                </span>
            </div>

            <!-- Increment -->
            <div class="p-4 flex flex-col items-center text-center group">
                <div class="w-10 h-10 mb-2 flex items-center justify-center bg-emerald-50 dark:bg-emerald-900/30 rounded-xs text-emerald-600 dark:text-emerald-400">
                    <i class="fa-solid fa-square-plus text-lg"></i>
                </div>
                <span class="text-[9px] font-bold text-emerald-600/70 dark:text-emerald-400 uppercase tracking-widest mb-1">Stock Increment</span>
                <span id="stat-total-increase-pcs" class="text-lg font-bold text-emerald-700 dark:text-emerald-400 leading-none">{{ number_format($stats['total_increase_pcs'], 0) }} Pcs</span>
                <span id="stat-total-increase" class="text-[9px] font-medium text-gray-400 mt-1">({{ number_format($stats['total_increase'], 0) }} Unit / {{ $stats['count_increase'] }} items)</span>
            </div>

            <!-- Decrement -->
            <div class="p-4 flex flex-col items-center text-center group">
                <div class="w-10 h-10 mb-2 flex items-center justify-center bg-rose-50 dark:bg-rose-900/30 rounded-xs text-rose-600 dark:text-rose-400">
                    <i class="fa-solid fa-square-minus text-lg"></i>
                </div>
                <span class="text-[9px] font-bold text-rose-600/70 dark:text-rose-400 uppercase tracking-widest mb-1">Stock Decrement</span>
                <span id="stat-total-decrease-pcs" class="text-lg font-bold text-rose-700 dark:text-rose-400 leading-none">{{ number_format($stats['total_decrease_pcs'], 0) }} Pcs</span>
                <span id="stat-total-decrease" class="text-[9px] font-medium text-gray-400 mt-1">({{ number_format($stats['total_decrease'], 0) }} Unit / {{ $stats['count_decrease'] }} items)</span>
            </div>

            <!-- Net Adjustment -->
            <div class="p-4 flex flex-col items-center text-center group">
                <div class="w-10 h-10 mb-2 flex items-center justify-center bg-purple-50 dark:bg-purple-900/30 rounded-xs text-purple-600 dark:text-purple-400">
                    <span class="font-black text-xs">NET</span>
                </div>
                <span class="text-[9px] font-bold text-purple-600/70 dark:text-purple-400 uppercase tracking-widest mb-1">Adjustment Impact</span>
                <span id="stat-net-adjustment-pcs" class="text-lg font-bold text-purple-700 dark:text-purple-400 leading-none">{{ ($stats['net_adjustment_pcs'] >= 0 ? '+' : '') . number_format($stats['net_adjustment_pcs'], 0) }} Pcs</span>
                <span id="stat-net-adjustment" class="text-[9px] font-medium text-gray-400 mt-1">({{ ($netAdjustment >= 0 ? '+' : '') . number_format($netAdjustment, 0) }} Unit)</span>
            </div>

            <!-- Financial Impact -->
            <div class="p-4 flex flex-col items-center text-center group">
                <div id="stat-net-amount-bg" class="w-10 h-10 mb-2 flex items-center justify-center rounded-xs {{ $stats['net_amount_impact'] >= 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }}">
                    <i class="fa-solid fa-coins text-lg"></i>
                </div>
                <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-1">Financial Impact</span>
                <span id="stat-net-amount-impact" class="text-lg font-bold leading-none {{ $stats['net_amount_impact'] >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                    {{ ($stats['net_amount_impact'] > 0 ? '+' : ($stats['net_amount_impact'] < 0 ? '-' : '')) . number_format(abs($stats['net_amount_impact'] ?? 0), 0) }}
                </span>
                <span class="text-[9px] font-medium text-gray-400 mt-1 uppercase">Total Currency</span>
            </div>

            <!-- Perfect Match -->
            <div class="p-4 flex flex-col items-center text-center group">
                <div class="w-10 h-10 mb-2 flex items-center justify-center bg-slate-50 dark:bg-slate-900 rounded-xs text-slate-400 border border-slate-100 dark:border-slate-800">
                    <i class="fa-solid fa-circle-check text-lg"></i>
                </div>
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">Perfect Match</span>
                <span id="stat-total-matched" class="text-lg font-bold text-slate-900 dark:text-white leading-none">{{ $stats['total_matched'] }}</span>
                <span class="text-[9px] font-medium text-slate-400 mt-1 lowercase">items found match</span>
            </div>
        </div>
    </div>
