<div id="modal-delete" class="modal-container hidden fixed inset-0 z-[150] flex items-center justify-center">
    <div class="fixed inset-0 bg-slate-950/60 transition-opacity"></div>
    <div class="relative w-full max-w-sm transform overflow-hidden rounded-xs bg-white dark:bg-gray-900 transition-all border border-slate-200 dark:border-gray-800 flex flex-col p-6">
        <div class="flex flex-col items-center text-center">
            <div class="w-16 h-16 bg-red-50 dark:bg-red-900/20 rounded-xs flex items-center justify-center mb-4">
                <i class="fa-solid fa-trash-can text-red-600 dark:text-red-400 text-2xl"></i>
            </div>
            <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-widest mb-2">Confirm Delete</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 font-medium leading-relaxed">Are you sure you want to delete this item? This action cannot be undone.</p>
        </div>
        
        <div class="flex gap-3 mt-6">
            <button type="button" class="close-modal flex-1 px-4 py-3 bg-white dark:bg-gray-800 rounded-xs border border-gray-300 text-[10px] font-bold text-gray-600 dark:text-gray-400 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">Cancel</button>
            <button type="button" id="confirmDelete" class="flex-1 px-4 py-3 bg-red-600 border border-transparent rounded-xs text-[10px] font-bold text-white uppercase tracking-widest hover:bg-red-700 transition-all">Delete</button>
        </div>
    </div>
</div>

<style>
.modal-container:not(.hidden) { display: flex; }
.error-msg { margin-top: 0.25rem; font-size: 0.75rem; line-height: 1rem; color: rgb(239 68 68); }
</style>

