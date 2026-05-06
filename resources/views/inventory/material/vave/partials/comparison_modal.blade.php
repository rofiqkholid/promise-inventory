{{-- ============================================================ --}}
{{-- VA/VE ANALYSIS COMPARISON MODAL                              --}}
{{-- ============================================================ --}}
<div id="comparisonModal" tabindex="-1" aria-hidden="true"
    class="hidden fixed inset-0 z-50 bg-black/40 flex items-stretch justify-stretch">

    <div class="flex flex-col w-full h-full bg-white dark:bg-gray-900">

        {{-- HEADER --}}
        <div class="flex items-center justify-between px-6 py-3 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shrink-0">
            <div>
                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest">VA/VE Material Analysis</p>
                <div id="comparisonSubtitle" class="mt-0.5 flex items-center gap-2 text-sm font-bold text-gray-800 dark:text-white">
                    {{-- Populated by JS: Part No + Part Name + Customer --}}
                </div>
            </div>
            <button type="button" class="close-modal-button w-8 h-8 flex items-center justify-center text-gray-400 hover:text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 rounded transition-colors">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        {{-- TOOLBAR --}}
        <div class="flex items-center justify-between px-6 py-2 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 shrink-0 gap-4 flex-wrap">

            {{-- Left: Selectors --}}
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2">
                    <label for="selectCompareBase" class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">Plan (EBD)</label>
                    <select id="selectCompareBase" class="border border-gray-300 dark:border-gray-600 text-xs text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 rounded px-2 py-1.5 outline-none focus:border-blue-500 min-w-[180px]"></select>
                </div>
                <div class="h-5 w-px bg-gray-300 dark:bg-gray-600"></div>
                <div class="flex items-center gap-2">
                    <label for="selectCompareActual" class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">Actual (Rev)</label>
                    <select id="selectCompareActual" class="border border-gray-300 dark:border-gray-600 text-xs text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 rounded px-2 py-1.5 outline-none focus:border-blue-500 min-w-[140px]"></select>
                </div>
            </div>

            {{-- Right: Net Impact + Actions --}}
            <div class="flex items-center gap-4">

                {{-- Net Impact Badge --}}
                <div id="netImpactContainer" class="hidden items-center gap-2 px-3 py-1 rounded border text-xs font-semibold">
                    <span class="text-[10px] font-medium text-current opacity-70 uppercase tracking-wider">Net Impact</span>
                    <span id="netImpactPct" class="font-bold"></span>
                    <span id="netImpactKg" class="opacity-60 font-normal"></span>
                    <span id="netImpactStatus" class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-white/30 border border-current/20 uppercase tracking-wider"></span>
                </div>

                {{-- Export Button --}}
                <button type="button" id="btnExportAnalysis"
                    class="hidden items-center gap-1.5 px-3 py-1.5 text-[11px] font-semibold text-white bg-gray-700 hover:bg-gray-800 dark:bg-gray-600 dark:hover:bg-gray-500 rounded transition-colors">
                    <i class="fa-solid fa-file-excel text-green-400"></i>
                    <span>Export</span>
                </button>

                <div class="h-5 w-px bg-gray-300 dark:bg-gray-600"></div>

                {{-- History Toggle --}}
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" id="toggleHistory" class="sr-only peer">
                    <div class="w-8 h-4 bg-gray-300 rounded-full peer dark:bg-gray-600
                                peer-checked:bg-blue-600
                                after:content-[''] after:absolute after:top-0.5 after:start-0.5
                                after:bg-white after:rounded-full after:h-3 after:w-3
                                after:transition-all peer-checked:after:translate-x-4
                                relative">
                    </div>
                    <span class="text-[10px] font-medium text-gray-500 uppercase tracking-wider">History</span>
                </label>
            </div>
        </div>

        {{-- CONTENT --}}
        <div class="flex-1 overflow-auto bg-white dark:bg-gray-900">
            <div id="comparisonContainer">
                {{-- Table injected by JS --}}
            </div>
        </div>

        {{-- FOOTER --}}
        <div class="flex items-center justify-between px-6 py-2.5 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 shrink-0">
            <div class="flex items-center gap-4 text-[10px] text-gray-400 font-medium uppercase tracking-widest">
                <span class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-green-500 inline-block"></span> Merit
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-red-500 inline-block"></span> Loss
                </span>
            </div>
            <button type="button" class="close-modal-button px-4 py-1.5 text-[11px] font-semibold text-gray-600 dark:text-gray-300 border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 rounded transition-colors">
                Close
            </button>
        </div>

    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Global State untuk Comparison Data
    window.compareState = { id: null, bases: [], revisions: [] };

    // Handle VA/VE Comparison (Fetch Data & Isi Dropdown) dengan Loading State
    $(document).on('click', '.compare-button', function() {
        const $btn = $(this);
        const id = $btn.data('id');
        const $icon = $btn.find('.btn-icon');
        const originalIconClass = $icon.attr('class');

        if($btn.prop('disabled')) return;

        // Start Loading
        $btn.prop('disabled', true).addClass('opacity-70 cursor-wait');
        $icon.attr('class', 'fa-solid fa-circle-notch fa-spin');

        window.compareState.id = id;

        $.get(`${VAVE_CONFIG.url_comparison}/${id}`, function(res) {
            const customer = res.product.customer ? res.product.customer.code : (res.product.customer_code || '');
            const subtitleEl = $('#comparisonSubtitle');
            subtitleEl.empty();
            subtitleEl.append(`<span class="text-blue-600 font-bold">${res.product.part_no}</span>`);
            subtitleEl.append(`<span class="text-gray-400 font-normal">/</span>`);
            subtitleEl.append(`<span>${res.product.part_name}</span>`);
            if (customer) {
                subtitleEl.append(`<span class="text-gray-300 font-normal mx-1">·</span>`);
                subtitleEl.append(`<span class="text-[11px] font-medium text-gray-400 uppercase tracking-wider">${customer}</span>`);
            }
            
            window.compareState.bases = res.bases || [];
            window.compareState.revisions = res.revisions || [];
            
            if (window.compareState.bases.length === 0) {
                $('#comparisonContainer').html('<div class="p-12 text-center text-gray-400"><i class="fa-solid fa-file-circle-exclamation text-4xl mb-4"></i><p>No baseline data found for this product.</p></div>');
                $('#comparisonModal').removeClass('hidden').addClass('flex');
                return;
            }

            // Isi Opsi Dropdown
            const baseSelect = $('#selectCompareBase').empty();
            const actualSelect = $('#selectCompareActual').empty();

            let html = '';
            res.bases.forEach(r => {
                const suffix = r.suffix ? ` - ${r.suffix.name}` : '';
                html += `<option value="${r.hash_id}" ${r.is_active ? 'selected' : ''}>${r.base_name}${suffix} (${parseFloat(r.weight_kg).toFixed(3)}kg)</option>`;
            });
            $('#selectCompareBase').html(html);

            if (window.compareState.revisions.length > 0) {
                const defaultRev = window.compareState.revisions[0];
                window.compareState.revisions.forEach(rev => {
                    const revCode = rev.revision ? rev.revision.code : '-';
                    const isSelected = rev.revision && defaultRev.revision && rev.revision.code === defaultRev.revision.code;
                    actualSelect.append(`<option value="${revCode}" ${isSelected ? 'selected' : ''}>Rev ${revCode}</option>`);
                });
            } else {
                actualSelect.append(`<option value="">No Revisions Found</option>`);
            }

            renderComparisonTable();
            $('#comparisonModal').removeClass('hidden').addClass('flex');
        }).always(function() {
            // Stop Loading
            $btn.prop('disabled', false).removeClass('opacity-70 cursor-wait');
            $icon.attr('class', originalIconClass);
        }).fail(function() {
            window.showToast('Error loading comparison data', 'error');
        });
    });

    // Handle Perubahan Dropdown
    $(document).on('change', '#selectCompareBase, #selectCompareActual', function() {
        renderComparisonTable();
        // Tampilkan ulang history jika toggle sedang nyala
        if ($('#toggleHistory').is(':checked')) { $('.history-col').removeClass('hidden'); }
    });

    // Fungsi Render Ulang Tabel
    function renderComparisonTable() {
        const isHistoryChecked = $('#toggleHistory').is(':checked');
        const id = window.compareState.id;
        const bases = window.compareState.bases;
        const revisions = window.compareState.revisions;

        const selectedBaseHash = $('#selectCompareBase').val();
        const selectedRevId = $('#selectCompareActual').val();

        // Gunakan data berdasarkan pilihan dropdown
        const benchmarkBase = bases.find(r => r.hash_id == selectedBaseHash) || bases[0];
        const latestRevision = revisions.find(r => r.revision && r.revision.code == selectedRevId) || null;

        // Update placeholders instead of injecting HTML
        const impactContainer = $('#netImpactContainer');
        const exportBtn = $('#btnExportAnalysis');

        if (benchmarkBase && latestRevision) {
            const saving = benchmarkBase.weight_kg - latestRevision.weight_kg;
            const savingPct = (saving / benchmarkBase.weight_kg) * 100;
            
            let statusBadge = 'NO CHANGE';
            let colorClass = 'text-gray-700 bg-gray-50 border-gray-200';
            if (saving > 0.001) {
                statusBadge = 'MERIT'; colorClass = 'text-green-700 bg-green-50 border-green-200';
            } else if (saving < -0.001) {
                statusBadge = 'LOSS'; colorClass = 'text-red-700 bg-red-50 border-red-200';
            }
            
            // Update Net Impact Shell
            impactContainer.removeClass('hidden text-gray-700 bg-gray-50 border-gray-200 text-green-700 bg-green-50 border-green-200 text-red-700 bg-red-50 border-red-200').addClass(`flex ${colorClass}`);
            $('#netImpactPct').text(`${Math.abs(savingPct).toFixed(2)}%`);
            $('#netImpactKg').text(`(${Math.abs(saving).toFixed(3)} kg)`);
            $('#netImpactStatus').text(statusBadge);

            // Update Export Button Shell
            exportBtn.removeClass('hidden').attr('data-url', `${VAVE_CONFIG.url_comparison}/${id}/export?base_id=${selectedBaseHash}&actual_id=${selectedRevId}`);
        } else {
            impactContainer.addClass('hidden');
            exportBtn.addClass('hidden');
        }

        let html = `
            <div class="overflow-x-auto custom-scrollbar rounded-xs border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-900 border-separate">
                <table id="comparisonTable" class="table-fixed min-w-full text-sm text-left border-collapse whitespace-nowrap">
                    <thead class="text-[10px] uppercase bg-slate-50 dark:bg-gray-800 sticky top-0 z-30">
                        <tr>
                            <th class="w-[160px] min-w-[160px] max-w-[160px] px-5 py-4 text-slate-400 font-black bg-slate-50 dark:bg-gray-800 border-b border-r border-slate-200 dark:border-gray-700 sticky left-0 z-40 text-left uppercase tracking-widest">PARAMETER</th>
        `;
        
        html += `<th class="w-[200px] min-w-[200px] max-w-[200px] px-5 py-4 text-center border-b border-r border-primary-200 dark:border-gray-700 bg-primary-50/50 dark:bg-primary-900/20 sticky top-0 z-40" style="left: 160px;">
                <div class="flex flex-col items-center">
                    <span class="text-[9px] text-primary-600 dark:text-primary-400 font-black tracking-[0.2em] mb-1">PLAN (EBD)</span>
                    <span class="font-black text-slate-800 dark:text-white truncate max-w-[180px] uppercase tracking-tighter" title="${benchmarkBase.base_name}">${benchmarkBase.base_name}</span>
                </div>
            </th>`;

        if (latestRevision) {
            html += `<th class="w-[200px] min-w-[200px] max-w-[200px] px-5 py-4 text-center border-b border-r border-emerald-200 dark:border-gray-700 bg-emerald-50/50 dark:bg-emerald-900/20 sticky top-0 z-40" style="left: 360px;">
                    <div class="flex flex-col items-center">
                        <span class="text-[9px] text-emerald-600 dark:text-emerald-400 font-black tracking-[0.2em] mb-1">ACTUAL (REV)</span>
                        <span class="font-black text-slate-800 dark:text-white uppercase tracking-tighter">Rev ${latestRevision.revision ? latestRevision.revision.code : '-'}</span>
                    </div>
                </th>`;
        } else {
             html += `<th class="w-[200px] min-w-[200px] px-5 py-4 bg-emerald-50/20 sticky top-0 z-40 border-b border-r border-emerald-100" style="left: 360px;">-</th>`;
        }

        html += `<th class="w-[130px] min-w-[130px] max-w-[130px] px-5 py-4 text-center border-b border-r border-slate-200 dark:border-gray-700 bg-slate-100 sticky top-0 z-40" style="left: 560px;">
                <div class="flex flex-col items-center">
                    <span class="text-[9px] text-slate-500 font-bold tracking-widest mb-1">VARIANCE (Δ)</span>
                    <span class="font-bold text-slate-600 uppercase tracking-tighter italic">Diff ±</span>
                </div>
            </th>`;

        revisions.forEach(rev => {
            if (latestRevision && rev.revision === latestRevision.revision) return;
            html += `<th class="w-[120px] min-w-[120px] px-4 py-3 text-center border-b border-r border-gray-200 dark:border-gray-700 bg-gray-50/50 history-col hidden border-dashed">
                    <div class="flex flex-col items-center opacity-60">
                        <span class="text-[9px] text-gray-400 font-bold">HISTORY (REV)</span>
                        <span class="font-semibold text-gray-500 text-xs">Rev ${rev.revision ? rev.revision.code : '-'}</span>
                    </div>
                </th>`;
        });

        html += `</tr></thead><tbody class="divide-y divide-gray-200 dark:divide-gray-700">`;

        const buildRow = (label, valueFormatter) => {
            let row = `<tr class="hover:bg-primary-100 dark:hover:bg-primary-900/40 transition-colors duration-150 text-xs text-gray-700 dark:text-gray-300">`;
            row += `<td class="w-[160px] min-w-[160px] max-w-[160px] px-4 py-2 font-semibold sticky left-0 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 z-10 transition-colors group-hover:bg-primary-50">${label}</td>`;
            row += `<td class="w-[160px] min-w-[160px] max-w-[160px] px-4 py-2 text-center border-r border-gray-200 dark:border-gray-700 font-medium sticky z-30 bg-white dark:bg-gray-800 transition-colors group-hover:bg-primary-50" style="left: 160px;">${valueFormatter(benchmarkBase)}</td>`;
            row += `<td class="w-[160px] min-w-[160px] max-w-[160px] px-4 py-2 text-center border-r border-gray-200 dark:border-gray-700 font-medium sticky z-30 bg-white dark:bg-gray-800 transition-colors group-hover:bg-primary-50" style="left: 320px;">${latestRevision ? valueFormatter(latestRevision) : '-'}</td>`;
            row += `<td class="w-[110px] min-w-[110px] max-w-[110px] px-4 py-2 text-center border-r border-gray-200 dark:border-gray-700 font-bold variance-cell sticky z-30 bg-gray-50 transition-colors group-hover:bg-primary-100" style="left: 480px;">-</td>`;
            revisions.forEach(rev => { if (!latestRevision || !rev.revision || !latestRevision.revision || rev.revision.code !== latestRevision.revision.code) row += `<td class="w-[120px] min-w-[120px] px-4 py-2 text-center border-r border-gray-200 dark:border-gray-200 text-gray-400 history-col hidden border-dashed">${valueFormatter(rev)}</td>`; });
            return row + `</tr>`;
        };

        const buildComputedRow = (label, valueGetter, unit = '', precision = 2, invertColor = false) => {
            const getVal = (item) => typeof valueGetter === 'function' ? valueGetter(item) : parseFloat(item[valueGetter] || 0);
            const baseVal = getVal(benchmarkBase);
            let row = `<tr class="hover:bg-primary-100 dark:hover:bg-primary-900/40 transition-colors duration-150 text-xs group">
                <td class="w-[160px] min-w-[160px] max-w-[160px] px-4 py-2.5 font-semibold text-gray-700 dark:text-gray-300 sticky left-0 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 z-10 transition-colors group-hover:bg-primary-50">${label}</td>
                <td class="w-[160px] min-w-[160px] max-w-[160px] px-4 py-2.5 text-center border-r border-gray-200 dark:border-gray-700 font-mono text-gray-600 sticky z-30 bg-white dark:bg-gray-800 transition-colors group-hover:bg-primary-50" style="left: 160px;">${baseVal.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: precision})} ${unit}</td>`;

            let actualVal = 0;
            if (latestRevision) {
                actualVal = getVal(latestRevision);
                row += `<td class="w-[160px] min-w-[160px] max-w-[160px] px-4 py-2.5 text-center border-r border-gray-200 dark:border-gray-700 font-mono text-gray-800 font-bold bg-yellow-50 group-hover:bg-yellow-100 sticky z-30 transform-gpu" style="left: 320px;">${actualVal.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: precision})} ${unit}</td>`;
                const delta = actualVal - baseVal;
                const isGood = invertColor ? delta > 0 : delta <= 0;
                let cClass = 'text-gray-400', bClass = 'bg-gray-50'; 
                if (Math.abs(delta) > 0.0001) { cClass = isGood ? 'text-green-600' : 'text-red-600'; bClass = isGood ? 'bg-green-50' : 'bg-red-50'; }
                row += `<td class="w-[110px] min-w-[110px] max-w-[110px] px-4 py-2.5 text-center border-r border-gray-200 dark:border-gray-700 font-mono font-bold ${cClass} sticky z-30 ${bClass} transition-colors group-hover:bg-primary-100" style="left: 480px;">${delta > 0 ? '+' : ''}${delta.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: precision})} ${unit}</td>`;
            } else {
                row += `<td class="w-[160px] min-w-[160px] px-4 py-2.5 bg-white border-r border-gray-200 sticky z-30" style="left: 320px;">-</td><td class="w-[110px] min-w-[110px] px-4 py-2.5 bg-gray-50 border-r border-gray-200 sticky z-30" style="left: 480px;">-</td>`;
            }

            revisions.forEach(rev => { if (!latestRevision || !rev.revision || !latestRevision.revision || rev.revision.code !== latestRevision.revision.code) row += `<td class="w-[120px] min-w-[120px] px-4 py-2.5 text-center border-r border-gray-200 dark:border-gray-700 text-gray-400 history-col hidden border-dashed font-mono">${getVal(rev).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: precision})}</td>`; });
            return row + `</tr>`;
        };

        const buildSectionRow = (title) => {
            let row = `<tr class="bg-slate-100 dark:bg-gray-800/80 text-[10px] uppercase tracking-[0.2em] font-bold text-slate-500 dark:text-gray-400">
                <td class="w-[160px] min-w-[160px] max-w-[160px] px-5 py-3 sticky left-0 bg-slate-100 dark:bg-gray-800 md:z-20 border-r border-slate-200 dark:border-gray-700">${title}</td>
                <td class="w-[200px] min-w-[200px] max-w-[200px] px-4 py-3 bg-slate-100 dark:bg-gray-800 border-r border-slate-200 dark:border-gray-700 sticky md:z-20" style="left: 160px;"></td>
                <td class="w-[200px] min-w-[200px] max-w-[200px] px-4 py-3 bg-slate-100 dark:bg-gray-800 border-r border-slate-200 dark:border-gray-700 sticky md:z-20" style="left: 360px;"></td>
                <td class="w-[130px] min-w-[130px] max-w-[130px] px-4 py-3 bg-slate-100 dark:bg-gray-800 border-r border-slate-200 dark:border-gray-700 sticky md:z-20" style="left: 560px;"></td>`;
            revisions.forEach(rev => { if (!latestRevision || !rev.revision || !latestRevision.revision || rev.revision.code !== latestRevision.revision.code) row += `<td class="w-[120px] min-w-[120px] px-4 py-3 bg-slate-100 dark:bg-gray-800 history-col hidden border-dashed"></td>`; });
            return row + `</tr>`;
        };

        html += buildSectionRow('Specification');
        html += buildRow('Material Spec', item => item.material_spec ? item.material_spec.spec_name : '-');
        html += buildRow('Unit Type', item => item.unit ? item.unit.name : '-');
        html += buildRow('Dimensions', i => {
            const unt = (i.unit ? i.unit.name : '').toLowerCase();
            let d = `${parseFloat(i.thickness)} x ${parseFloat(i.width)}`;
            if (unt.includes('coil')) d += ` x ${parseFloat(i.pitch)} (P)`;
            else if (unt.includes('trapezoid')) d += ` x (${parseFloat(i.length)} + ${parseFloat(i.length_2)})/2`;
            else d += ` x ${parseFloat(i.length)}`;
            return d;
        });

        html += buildComputedRow('Thickness (mm)', 'thickness', '', 2, false); 
        html += buildComputedRow('Width (mm)', 'width', '', 2, false);

        const untCompare = (benchmarkBase.unit ? benchmarkBase.unit.name : '').toLowerCase();
        if (untCompare.includes('trapezoid')) {
            html += buildComputedRow('Length 1 (L1) (mm)', 'length', '', 2, false);
            html += buildComputedRow('Length 2 (L2) (mm)', 'length_2', '', 2, false);
        } else if (untCompare.includes('coil')) {
            html += buildComputedRow('Pitch (mm)', 'pitch', '', 2, false);
        } else {
            html += buildComputedRow('Length (mm)', 'length', '', 2, false);
        }
        
        html += buildSectionRow('Yield & Weight');
        html += buildComputedRow('Density', 'density', '', 3, true);
        html += buildComputedRow('Pcs / Pitch', 'pcs_per_pitch', '', 0, true);
        html += buildComputedRow('Pcs / Unit', 'pcs_per_unit', '', 0, true);
        html += buildComputedRow('Gross Weight (Kg)', 'weight_kg', '', 3, false); 
        html += buildComputedRow('Net Weight/Part (Kg)', 'net_weight', '', 3, false);
        html += buildComputedRow('Scrap (Kg)', i => (parseFloat(i.weight_kg)||0) - (parseFloat(i.net_weight)||0), '', 3, false); 
        html += buildComputedRow('Yield Ratio (%)', i => {
            const g = parseFloat(i.weight_kg)||0, n = parseFloat(i.net_weight)||0;
            return (g>0 && n>0) ? (n/g)*100 : 0;
        }, '', 1, true); 
        
        html += buildSectionRow('Commercial');
        html += buildComputedRow('Price/Kg (IDR)', i => parseFloat(i.material_price || 0), '', 0, false);
        html += buildComputedRow('Total Cost (IDR)', i => (parseFloat(i.weight_kg||0) * parseFloat(i.material_price || 0)), '', 0, false); 

        html += buildSectionRow('Other Information');
        html += buildRow('Remark', item => item.remark ? item.remark : '-');

        let statusRow = `<tr class="bg-gray-50/50 hover:bg-primary-100 dark:bg-gray-800 dark:hover:bg-primary-900/40 text-xs border-t-2 border-gray-200 dark:border-gray-600 group"><td class="w-[160px] min-w-[160px] max-w-[160px] px-4 py-3 sticky left-0 bg-gray-50 dark:bg-gray-800 border-r border-gray-300 z-10 font-bold uppercase group-hover:bg-primary-100">Status</td><td class="w-[160px] min-w-[160px] max-w-[160px] px-4 py-3 text-center border-r border-gray-300 bg-gray-50 dark:bg-gray-800 sticky z-30 group-hover:bg-primary-100" style="left: 160px;">-</td>`;
        let rateRow = `<tr class="bg-white hover:bg-primary-100 dark:bg-gray-800 dark:hover:bg-primary-900/40 text-xs group"><td class="w-[160px] min-w-[160px] max-w-[160px] px-4 py-3 sticky left-0 bg-white dark:bg-gray-800 border-r border-gray-300 z-10 font-bold uppercase group-hover:bg-primary-100">Rate</td><td class="w-[160px] min-w-[160px] max-w-[160px] px-4 py-3 text-center border-r border-gray-300 bg-white dark:bg-gray-800 sticky z-30 group-hover:bg-primary-100" style="left: 160px;">-</td>`;        if (latestRevision) {
            const saving = benchmarkBase.weight_kg - latestRevision.weight_kg;
            const savingPct = (saving / benchmarkBase.weight_kg) * 100;
            let sText = 'NO CHANGE', sColor = 'text-gray-700 font-bold bg-gray-100', rColor = 'text-gray-700'; 
            if (saving > 0.0001) { sText = 'MERIT'; sColor = 'text-green-700 bg-green-50'; rColor = 'text-green-700'; }
            else if (saving < -0.0001) { sText = 'LOSS'; sColor = 'text-red-700 bg-red-50'; rColor = 'text-red-700'; }
            
            statusRow += `<td class="w-[160px] min-w-[160px] px-4 py-3 text-center border-r border-gray-300 ${sColor} font-bold sticky z-30 group-hover:bg-opacity-90" style="left: 320px;">${sText}</td><td class="w-[110px] min-w-[110px] px-4 py-3 text-center border-r border-gray-300 bg-gray-100 text-gray-400 sticky z-30 group-hover:bg-primary-100" style="left: 480px;">-</td>`;
            rateRow += `<td class="w-[160px] min-w-[160px] px-4 py-3 text-center border-r border-gray-300 font-bold ${rColor} sticky z-30 bg-white dark:bg-gray-800 group-hover:bg-primary-100" style="left: 320px;">${Math.abs(savingPct).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 2})}%</td><td class="w-[110px] min-w-[110px] px-4 py-3 text-center border-r border-gray-300 bg-gray-100 text-gray-400 sticky z-30 group-hover:bg-primary-100" style="left: 480px;">-</td>`;
        } else {
            statusRow += `<td class="w-[160px] min-w-[160px] px-4 py-3 bg-gray-50 border-r border-gray-300 sticky z-30" style="left: 320px;">-</td><td class="w-[110px] min-w-[110px] px-4 py-3 bg-gray-100 sticky z-30" style="left: 480px;">-</td>`;
            rateRow += `<td class="w-[160px] min-w-[160px] px-4 py-3 bg-white border-r border-gray-300 sticky z-30" style="left: 320px;">-</td><td class="w-[110px] min-w-[110px] px-4 py-3 bg-gray-100 sticky z-30" style="left: 480px;">-</td>`;
        }
 
        const buildHistStatus = (item) => {
            const baseW = parseFloat(benchmarkBase.weight_kg) || 0;
            const itemW = parseFloat(item.weight_kg) || 0;
            if (baseW <= 0 || itemW <= 0) {
                statusRow += `<td class="w-[120px] min-w-[120px] px-4 py-3 text-center bg-gray-50 history-col hidden border-dashed text-gray-400">-</td>`;
                rateRow += `<td class="w-[120px] min-w-[120px] px-4 py-3 text-center bg-white history-col hidden border-dashed text-gray-400">-</td>`;
                return;
            }
            const hSaving = baseW - itemW, hPct = (hSaving / baseW) * 100;
            let hText = 'NO CHANGE', hCol = 'text-gray-400';
            if (hSaving > 0.0001) { hText = 'MERIT'; hCol = 'text-green-600'; }
            else if (hSaving < -0.0001) { hText = 'LOSS'; hCol = 'text-red-600'; }
            statusRow += `<td class="w-[120px] min-w-[120px] px-4 py-3 text-center font-bold ${hCol} bg-gray-50 history-col hidden border-dashed text-[10px] tracking-wider">${hText}</td>`;
            rateRow += `<td class="w-[120px] min-w-[120px] px-4 py-3 text-center font-bold ${hCol} bg-white history-col hidden border-dashed">${Math.abs(hPct).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 2})}%</td>`;
        };

        revisions.forEach(rev => { if (!latestRevision || !rev.revision || !latestRevision.revision || rev.revision.code !== latestRevision.revision.code) buildHistStatus(rev); });
        
        statusRow += `</tr>`; rateRow += `</tr>`;
        html += statusRow + rateRow + `</tbody></table></div>`;
        
        $('#comparisonContainer').html(html);

        $('#toggleHistory').off().on('change', function() {
            if(this.checked) $('.history-col').removeClass('hidden');
            else $('.history-col').addClass('hidden');
        });

        if (isHistoryChecked) {
            $('.history-col').removeClass('hidden');
        }
    }

    // Handle Export Analysis di dalam Modal dengan AJAX Blob
    $(document).on('click', '#btnExportAnalysis', function(e) {
        e.preventDefault();
        const $btn = $(this);
        const url = $btn.data('url') || $btn.attr('href');
        handleAjaxDownload($btn, url, 'VAVE_Analysis_' + new Date().getTime() + '.xlsx');
    });

    $('.close-modal-button').on('click', function() {
        $(this).closest('[tabindex="-1"]').addClass('hidden').removeClass('flex');
    });
});
</script>
<style>
    .font-mono { font-family: 'JetBrains Mono', 'Fira Code', monospace; }
    .custom-scrollbar::-webkit-scrollbar { height: 8px; width: 8px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 20px; border: 2px solid transparent; background-clip: content-box; }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; }
</style>
@endpush
