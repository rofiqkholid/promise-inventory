<div id="global-tooltip-portal" class="fixed z-[9999] bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 p-4 w-64 text-left hidden font-sans shadow-blue-900/5"></div>

@push('scripts')
<script>
    $(document).ready(function() {
        const tooltip = $('#global-tooltip-portal');

        $(document).on('mouseenter', '.hover-trigger', function(e) {
            const el = $(this);
            const data = el.data('details');
            if (!data) return;

            let content = `
                <h4 class="font-bold text-gray-900 dark:text-white mb-2 border-b border-gray-100 dark:border-gray-700 pb-1.5 text-xs uppercase tracking-wide">Product Details</h4>
                <div class="grid grid-cols-2 gap-x-2 gap-y-1.5 text-xs">
                    <div class="text-gray-500 dark:text-gray-400">Customer:</div>
                    <div class="font-medium text-gray-900 dark:text-white truncate" title="${data.customer}">${data.customer || '-'}</div>
                    
                    <div class="text-gray-500 dark:text-gray-400">Model:</div>
                    <div class="font-medium text-gray-900 dark:text-white truncate" title="${data.model}">${data.model || '-'}</div>
                    
                    <div class="text-gray-500 dark:text-gray-400">Rank/Limit:</div>
                    <div class="font-medium text-gray-900 dark:text-white">${data.rank || '-'} <span class="text-gray-400">(${data.limit_value || '-'})</span></div>
                    
                    <div class="text-gray-500 dark:text-gray-400">Coating:</div>
                    <div class="font-medium text-gray-900 dark:text-white">${data.coating_type || '-'}</div>
                    
                    <div class="text-gray-500 dark:text-gray-400">Min. Stock:</div>
                    <div class="font-medium text-gray-900 dark:text-white">${data.min_stock || '-'}</div>
                    
                    <div class="text-gray-500 dark:text-gray-400">Unit/Car:</div>
                    <div class="font-medium text-gray-900 dark:text-white">${data.unit_per_car || '-'}</div>
                    
                    <div class="col-span-2 mt-1 border-t border-gray-100 dark:border-gray-700 pt-1.5 flex justify-between items-center text-[10px] text-gray-400">
                        <span>Last Update:</span>
                        <span class="font-mono text-gray-600 dark:text-gray-300">${data.last_update || '-'}</span>
                    </div>
                </div>
            `;

            tooltip.html(content).removeClass('hidden').show();
            
            const rect = this.getBoundingClientRect();
            // Smart positioning is less critical here as tooltips usually follow mouse or are small
            // But let's use similar robust logic to STO popover
            const tipWidth = tooltip.outerWidth();
            const tipHeight = tooltip.outerHeight();
            
            let top = rect.bottom + 5;
            let left = rect.left;

            // Edge detection
            if (top + tipHeight > window.innerHeight) top = rect.top - tipHeight - 5;
            if (left + tipWidth > window.innerWidth) left = window.innerWidth - tipWidth - 10;
            if (left < 10) left = 10;

            tooltip.css({
                top: top + 'px',
                left: left + 'px',
                position: 'fixed'
            });
        });

        $(document).on('mouseleave', '.hover-trigger', function() {
            tooltip.hide();
        });
    });
</script>
@endpush
