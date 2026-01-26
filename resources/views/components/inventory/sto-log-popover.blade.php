<div id="sto-log-popover" class="fixed z-[9999] bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 w-80 text-left hidden p-0 overflow-hidden font-sans">
    <div class="bg-gray-50 dark:bg-gray-700 px-3 py-2 border-b border-gray-200 dark:border-gray-600 flex justify-between items-center">
        <h4 class="font-bold text-gray-700 dark:text-gray-200 text-xs uppercase tracking-wider">STO Log History</h4>
        <button type="button" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors" onclick="$('#sto-log-popover').addClass('hidden')">
            <i class="fa-solid fa-times"></i>
        </button>
    </div>
    <div id="sto-log-content" class="max-h-60 overflow-y-auto p-0 scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-gray-600">
        <div class="p-4 text-center text-gray-500 text-xs">
            <i class="fa-solid fa-spinner fa-spin mr-1"></i> Loading...
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        const stoPopover = $('#sto-log-popover');
        const contentDiv = $('#sto-log-content');

        // STO Log Trigger Click Handler
        $(document).on('click', '.sto-log-trigger', function(e) {
            e.stopPropagation();
            const el = $(this);
            const id = el.data('id');
            
            // Show first to get dimensions
            stoPopover.removeClass('hidden'); 
            
            // --- Smart Positioning Logic ---
            const rect = this.getBoundingClientRect();
            const popoverWidth = stoPopover.outerWidth() || 320;
            const popoverHeight = stoPopover.outerHeight() || 300; 
            const viewportWidth = window.innerWidth;
            const viewportHeight = window.innerHeight;

            // 1. Horizontal Centering
            let left = rect.left + (rect.width / 2) - (popoverWidth / 2);
            
            // 2. Horizontal Collision Check
            if (left < 10) left = 10;
            if (left + popoverWidth > viewportWidth - 10) left = viewportWidth - popoverWidth - 10;

            // 3. Vertical Smart Flip
            let top = rect.bottom + 8; // Default: show below
            
            const spaceBelow = viewportHeight - rect.bottom;
            const spaceAbove = rect.top;
            
            // If tight space below AND more space above, flip to top
            if (spaceBelow < popoverHeight && spaceAbove > spaceBelow) {
                top = rect.top - popoverHeight - 8;
            }

            stoPopover.css({
                top: top + 'px',
                left: left + 'px',
                position: 'fixed'
            });
            
            // Loading State
            contentDiv.html('<div class="p-4 text-center text-gray-500 text-xs"><i class="fa-solid fa-spinner fa-spin mr-1"></i> Loading log...</div>');
            
            // Fetch Data
            $.ajax({
                url: "{{ url('inventory/stock-monitoring/log') }}/" + id,
                success: function(data) {
                    if (data.length === 0) {
                        contentDiv.html('<div class="p-4 text-center text-gray-500 text-xs italic">No STO history found for this item.</div>');
                        return;
                    }
                    
                    let html = '<table class="w-full text-xs text-left border-collapse">';
                    html += '<thead class="bg-gray-50 dark:bg-gray-800 text-gray-500 border-b border-gray-100 dark:border-gray-600"><tr><th class="p-2 py-1.5 font-semibold">Date / Event</th><th class="p-2 py-1.5 text-right font-semibold">Sys</th><th class="p-2 py-1.5 text-right font-semibold">Act</th><th class="p-2 py-1.5 text-right font-semibold">Diff</th></tr></thead>';
                    html += '<tbody class="divide-y divide-gray-100 dark:divide-gray-700">';
                    
                    data.forEach(log => {
                        let diffClass = log.diff > 0 ? 'text-green-600 dark:text-green-400' : (log.diff < 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-400');
                        let diffSign = log.diff > 0 ? '+' : '';
                        
                        html += `
                            <tr class="hover:bg-blue-50 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="p-2 align-top">
                                    <div class="font-bold text-gray-700 dark:text-gray-300 mb-0.5">${log.event}</div>
                                    <div class="text-[10px] text-gray-400 mb-0.5">${log.date}</div>
                                    ${log.remark && log.remark !== '-' ? `<div class="text-[10px] text-gray-500 italic truncate max-w-[120px]" title="${log.remark}">${log.remark}</div>` : ''}
                                </td>
                                <td class="p-2 text-right font-mono text-gray-600 dark:text-gray-400 align-top">${log.system}</td>
                                <td class="p-2 text-right font-mono text-gray-600 dark:text-gray-400 align-top">${log.actual}</td>
                                <td class="p-2 text-right font-mono font-bold ${diffClass} align-top">${diffSign}${log.diff}</td>
                            </tr>
                        `;
                    });
                    
                    html += '</tbody></table>';
                    contentDiv.html(html);
                },
                error: function() {
                    contentDiv.html('<div class="p-4 text-center text-red-500 text-xs">Failed to load log data.</div>');
                }
            });
        });

        // Close Popover on Outside Click
        $(document).click(function(e) {
            if (!$(e.target).closest('#sto-log-popover, .sto-log-trigger').length) {
                stoPopover.addClass('hidden');
            }
        });
    });
</script>
@endpush
