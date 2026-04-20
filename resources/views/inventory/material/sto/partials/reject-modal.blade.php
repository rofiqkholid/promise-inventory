<!-- Reject Modal -->
<div id="rejectModal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4 bg-slate-900/80 backdrop-blur-sm transition-all">
    <div class="bg-white dark:bg-gray-800 rounded-xs shadow-xl w-full max-w-md overflow-hidden border border-slate-200 dark:border-gray-700">
        <div class="px-6 py-4 bg-rose-50 dark:bg-rose-900/20 border-b border-rose-100 dark:border-rose-800 flex justify-between items-center">
            <h3 class="font-bold text-rose-900 dark:text-rose-400 flex items-center gap-3 text-sm uppercase tracking-widest">
                <i class="fa-solid fa-ban"></i> Reject Submission
            </h3>
            <button onclick="closeRejectModal()" class="text-rose-400 hover:text-rose-900 dark:hover:text-white transition-colors">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <form action="{{ route('inventory.sto.reject', $stoEvent->hash_id) }}" method="POST" class="p-6">
            @csrf
            <div class="mb-5">
                <label for="rejection_note" class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-3">Feedback for the PIC</label>
                <textarea name="rejection_note" id="rejection_note" rows="4" required 
                    class="w-full bg-slate-50 dark:bg-gray-900 border-2 border-slate-100 dark:border-gray-700 rounded-xs p-4 text-sm font-bold focus:ring-0 focus:border-rose-500 transition-all dark:text-gray-200 placeholder-slate-300 outline-none"
                    placeholder="Provide clear reasons for rejection..."></textarea>
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="closeRejectModal()" class="flex-1 py-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest hover:bg-slate-50 dark:hover:bg-gray-800 rounded-xs transition-all">
                    Cancel
                </button>
                <button type="submit" class="flex-1 py-3 bg-rose-600 hover:bg-rose-700 text-white text-[10px] font-bold rounded-xs transition-all active:scale-95 uppercase tracking-widest shadow-lg shadow-rose-100 dark:shadow-none">
                    Confirm Reject
                </button>
            </div>
        </form>
    </div>
</div>
