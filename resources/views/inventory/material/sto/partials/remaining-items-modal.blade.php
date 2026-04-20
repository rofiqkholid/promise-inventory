<!-- Unscanned Items Modal -->
<div id="remainingItemsModal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4 bg-slate-900/50 transition-all">
    <div class="bg-white dark:bg-gray-800 rounded-xs shadow-xl w-full max-w-2xl overflow-hidden border border-slate-200 dark:border-gray-700 flex flex-col max-h-[85vh]">
        <div class="px-6 py-4 bg-slate-50 dark:bg-gray-900 border-b border-slate-200 dark:border-gray-700 flex justify-between items-center shrink-0">
            <h3 class="font-bold text-gray-900 dark:text-white flex items-center gap-3 text-sm uppercase tracking-widest">
                <i class="fa-solid fa-clipboard-list text-primary-600"></i> Remaining Products
            </h3>
            <button onclick="closeRemainingModal()" class="text-slate-400 hover:text-rose-500 transition-colors">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <div class="p-0 overflow-y-auto flex-1 h-full">
            <table class="w-full text-left border-collapse">
                <thead class="sticky top-0 bg-gray-50 dark:bg-gray-900 text-[10px] font-bold text-gray-500 uppercase tracking-widest border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-6 py-3">Product</th>
                        <th class="px-6 py-3">Part Name</th>
                        <th class="px-6 py-3 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($products as $p)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors group">
                        <td class="px-6 py-3 font-mono font-bold text-xs text-gray-900 dark:text-white">
                            {{ $p->part_no }} {{ $p->revision ? '- ' . $p->revision : '' }}
                        </td>
                        <td class="px-6 py-3 text-xs text-gray-600 dark:text-gray-400">{{ $p->part_name }}</td>
                        <td class="px-6 py-3 text-center">
                            <button onclick="closeRemainingModal(); editFromTable('{{ $p->hash_id }}', null)" 
                                    class="h-8 w-8 inline-flex items-center justify-center text-primary-600 rounded-xs bg-primary-50 hover:bg-primary-100 dark:bg-primary-900/20 dark:text-primary-400 dark:hover:bg-primary-900/30 transition-all" title="Record Now">
                                <i class="fa-solid fa-pen-to-square text-sm"></i>
                            </button>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-10 text-center text-gray-400 italic text-sm">All products have been recorded.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-3 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700 shrink-0 text-center">
            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Total Remaining: {{ count($products) }} Items</span>
        </div>
    </div>
</div>
