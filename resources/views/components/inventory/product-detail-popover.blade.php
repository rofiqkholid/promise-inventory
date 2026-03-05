<div id="global-tooltip-portal" class="fixed z-[9999] bg-white dark:bg-gray-800 rounded-xs shadow-2xl border border-slate-200 dark:border-gray-700 p-5 w-72 text-left hidden font-sans scale-in"></div>

@push('scripts')
<script>
    function renderDimension(row) {
        if (!row) return '-';
        const t = row.thickness || 0;
        const w = row.width || 0;
        const l = row.length || 0;
        const l2 = row.length_2 || 0;
        const p = row.pitch || 0;
        const weight = row.weight || 0;
        const unit = (row.unit_name || '').toLowerCase();

        const fmt = (lbl, val) => `
            <span class="inline-flex items-center gap-x-0.5 mr-2">
                <span class="text-gray-400 dark:text-gray-500 font-bold">${lbl}:</span>
                <span class="text-slate-800 dark:text-gray-200 font-medium">${val}</span>
            </span>
        `;
        
        let items = [];
        if (t > 0) items.push(fmt('T', t));
        if (w > 0) items.push(fmt('W', w));
        
        if (unit.includes('coil')) {
            if (p > 0) items.push(fmt('P', p));
        } else if (unit.includes('trapezoid')) {
            if (l > 0) items.push(fmt('L', l));
            if (l2 > 0) items.push(fmt('L2', l2));
        } else {
            if (l > 0) items.push(fmt('L', l));
        }

        if (weight > 0) items.push(fmt('Wt', weight + 'kg'));

        return items.length > 0 ? items.join('') : '-';
    }

    $(document).ready(function() {
        const tooltip = $('#global-tooltip-portal');

        $(document).on('mouseenter', '.hover-trigger', function(e) {
            const el = $(this);
            const data = el.data('details');
            if (!data) return;

            let content = `
                <h4 class="font-bold text-slate-900 dark:text-white mb-3 border-b border-slate-100 dark:border-gray-700 pb-2 text-[10px] uppercase tracking-widest flex items-center gap-2">
                    <i class="fa-solid fa-circle-info text-primary-500"></i> Part Details
                </h4>
                <div class="grid grid-cols-2 gap-x-2 gap-y-1.5 text-xs">
                    <div class="text-gray-500 dark:text-gray-400">Model:</div>
                    <div class="font-bold text-slate-800 dark:text-white truncate" title="${data.model}">${data.model || '-'}</div>

                    <div class="text-gray-500 dark:text-gray-400">Customer:</div>
                    <div class="font-medium text-slate-600 dark:text-gray-300 truncate" title="${data.customer}">${data.customer || '-'}</div>

                    <div class="col-span-2 my-1 border-t border-slate-50 dark:border-gray-700/50"></div>

                    <div class="text-gray-500 dark:text-gray-400">Material:</div>
                    <div class="font-bold text-slate-800 dark:text-white truncate" title="${data.spec}">${data.spec || '-'}</div>

                    <div class="text-gray-500 dark:text-gray-400">Dimension:</div>
                    <div id="tip-dimension" class="font-mono text-[10px] tracking-tight text-slate-700 dark:text-gray-300"></div>
                    
                    <div class="text-gray-500 dark:text-gray-400">Remark:</div>
                    <div class="font-medium text-slate-600 dark:text-gray-300 italic text-[11px] leading-tight" title="${data.remark}">${data.remark || '-'}</div>

                    <div class="col-span-2 my-1 border-t border-slate-50 dark:border-gray-700/50"></div>

                    <div class="text-gray-500 dark:text-gray-400">Rank/Limit:</div>
                    <div class="font-medium text-gray-900 dark:text-white">${data.rank || '-'} <span class="text-gray-400">(${data.limit_value || '-'})</span></div>
                    
                    <div class="text-gray-500 dark:text-gray-400">Coating:</div>
                    <div class="font-medium text-gray-900 dark:text-white">${data.coating_type || '-'}</div>
                    
                    <div class="text-gray-500 dark:text-gray-400">Min. Stock:</div>
                    <div class="font-medium text-gray-900 dark:text-white">${data.min_stock || '-'}</div>
                    
                    <div class="text-gray-500 dark:text-gray-400">Unit/Car:</div>
                    <div class="font-medium text-gray-900 dark:text-white">${data.unit_per_car || '-'}</div>
                    
                    <div class="col-span-2 mt-2 border-t border-slate-50 dark:border-gray-700 pt-2 flex justify-between items-center text-[9px] text-slate-400 font-bold uppercase tracking-widest">
                        <span>Last Update</span>
                        <span class="text-slate-600 dark:text-gray-400">${data.last_update || '-'}</span>
                    </div>
                </div>
            `;

            tooltip.html(content).removeClass('hidden').show();
            $('#tip-dimension').html(renderDimension(data));
            
            const rect = this.getBoundingClientRect();
            const tipWidth = tooltip.outerWidth();
            const tipHeight = tooltip.outerHeight();
            
            let top = rect.bottom + 5;
            let left = rect.left;

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
