@extends('layouts.app')

@section('content')
<div class="p-6">
    <!-- Header - Responsive -->
    <div class="flex flex-col md:flex-row justify-between items-start gap-4 mb-4 md:mb-6">
        <div class="flex-1">
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('inventory.sto.index') }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    <i class="fa-solid fa-arrow-left"></i> Back
                </a>
                <span class="text-gray-300">|</span>
                <span class="px-2 py-0.5 text-xs rounded font-bold uppercase {{ $stoEvent->status === 'OPEN' ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' : 'bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}">
                    {{ $stoEvent->status }}
                </span>
            </div>
            <h1 class="text-xl md:text-2xl font-bold text-gray-800 dark:text-white">{{ $stoEvent->name }}</h1>
            <p class="text-xs md:text-sm text-gray-500 dark:text-gray-400">Code: {{ $stoEvent->code }} • Start: {{ $stoEvent->period_start->format('d M Y') }}</p>
        </div>

        <div class="flex flex-wrap gap-2 w-full md:w-auto">
            <a href="{{ route('inventory.sto.exportExcel', $stoEvent->hash_id) }}" class="flex-none bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-lg flex items-center justify-center gap-2 shadow-sm text-sm">
                <i class="fa-solid fa-download"></i> Export
            </a>
            @if($stoEvent->status === 'CLOSED')
            <form action="{{ route('inventory.sto.reopen', $stoEvent->hash_id) }}" method="POST" id="reopenForm" class="flex-none">
                @csrf
                <button type="button" onclick="confirmReopen()" class="bg-yellow-600 hover:bg-yellow-700 text-white px-3 md:px-4 py-2 rounded-lg flex items-center justify-center gap-2 shadow-sm text-sm">
                    <i class="fa-solid fa-rotate-left"></i> <span class="hidden sm:inline">Reopen</span>
                </button>
            </form>
            @endif
            @if($stoEvent->status === 'OPEN')
            <form action="{{ route('inventory.sto.finalize', $stoEvent->hash_id) }}" method="POST" id="finalizeForm" class="flex-1 w-full md:w-auto min-w-[140px]">
                @csrf
                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white px-6 py-2.5 rounded-lg flex items-center justify-center gap-2 shadow-sm font-medium">
                    <i class="fa-solid fa-lock"></i> Finalize
                </button>
            </form>
            @endif
        </div>
    </div>

    {{-- Statistics calculated in Controller --}}

    <!-- Statistics Dashboard - Horizontal with Wrap (No Scroll) -->
    <div class="bg-white dark:bg-gray-800 rounded-lg p-3 md:p-4 mb-4 md:mb-6 border border-gray-200 dark:border-gray-600">
        <div class="flex flex-wrap items-center gap-3 md:gap-4 text-xs md:text-sm">
            <div class="flex items-center gap-2">
                <span class="text-gray-600 dark:text-gray-400">Total:</span>
                <span id="stat-total-items" class="font-bold text-base md:text-lg text-blue-600 dark:text-blue-400">{{ $stats['total_items'] }}</span>
                <span class="text-[10px] md:text-xs text-gray-500 dark:text-gray-400">(<span id="stat-progress">{{ $progress }}</span>%)</span>
            </div>
            <span class="text-gray-300 dark:text-gray-600">|</span>
            <div class="flex items-center gap-2">
                <span class="text-green-600 dark:text-green-400">Inc:</span>
                <div class="flex flex-col leading-tight">
                    <span id="stat-total-increase-pcs" class="font-bold text-green-700 dark:text-green-300">{{ number_format($stats['total_increase_pcs'], 0) }} Pcs</span>
                    <span id="stat-total-increase" class="text-[10px] text-green-600 dark:text-green-400">({{ number_format($stats['total_increase'], 0) }} Unit)</span>
                </div>
                <span class="text-[10px] md:text-xs text-gray-500 dark:text-gray-400 ml-1 self-start">(<span id="stat-count-increase">{{ $stats['count_increase'] }}</span>)</span>
            </div>
            <span class="text-gray-300 dark:text-gray-600">|</span>
            <div class="flex items-center gap-2">
                <span class="text-red-600 dark:text-red-400">Dec:</span>
                <div class="flex flex-col leading-tight">
                    <span id="stat-total-decrease-pcs" class="font-bold text-red-700 dark:text-red-300">{{ number_format($stats['total_decrease_pcs'], 0) }} Pcs</span>
                    <span id="stat-total-decrease" class="text-[10px] text-red-600 dark:text-red-400">({{ number_format($stats['total_decrease'], 0) }} Unit)</span>
                </div>
                <span class="text-[10px] md:text-xs text-gray-500 dark:text-gray-400 ml-1 self-start">(<span id="stat-count-decrease">{{ $stats['count_decrease'] }}</span>)</span>
            </div>
            <span class="text-gray-300 dark:text-gray-600">|</span>
            <div class="flex items-center gap-2">
                <span class="text-purple-600 dark:text-purple-400">Net:</span>
                <div class="flex flex-col leading-tight">
                    <span id="stat-net-adjustment-pcs" class="font-bold text-purple-700 dark:text-purple-300">{{ ($stats['net_adjustment_pcs'] >= 0 ? '+' : '') . number_format($stats['net_adjustment_pcs'], 0) }} Pcs</span>
                    <span id="stat-net-adjustment" class="text-[10px] text-purple-600 dark:text-purple-400">({{ ($netAdjustment >= 0 ? '+' : '') . number_format($netAdjustment, 0) }} Unit)</span>
                </div>
            </div>
            <span class="text-gray-300 dark:text-gray-600">|</span>
            <div class="flex items-center gap-2">
                <span class="text-gray-600 dark:text-gray-400">Match:</span>
                <span id="stat-total-matched" class="font-bold text-gray-700 dark:text-gray-300">{{ $stats['total_matched'] }}</span>
            </div>
        </div>
    </div>

    @if($stoEvent->status === 'OPEN')
    <!-- SCANNER SECTION - Mobile Optimized -->
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg p-3 md:p-6 mb-4 md:mb-6">
        <label class="block text-sm md:text-base font-semibold text-gray-700 dark:text-gray-300 mb-3">
            <i class="fa-solid fa-barcode mr-1"></i> Select or Scan Product
        </label>
        
        <div class="flex gap-2">
            <div class="flex-1 min-w-0">
                <select id="product_detail_id" class="select2 w-full" data-placeholder="Select Product via Search or Scanner...">
                    <option value="">Select Product via Search or Scanner...</option>
                    @foreach($products as $product)
                        <option value="{{ $product->hash_id }}" data-partno="{{ $product->part_no }}">
                            {{ $product->part_no }} {{ $product->revision ? '- ' . $product->revision : '' }} - {{ $product->part_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button id="btn-scan" class="flex-shrink-0 bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 md:px-4 py-2 rounded-lg border border-gray-300 transition-colors dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-200 dark:border-gray-600" title="Open Scanner">
                <i class="fa-solid fa-camera text-lg"></i>
            </button>
        </div>

        <!-- Clean Result / Input Form -->
        <div class="mt-4 hidden" id="scanResultArea">
             
             <div class="flex flex-col md:flex-row items-end gap-4 text-sm bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                 <!-- Product Info -->
                 <div class="flex-1 w-full text-left">
                    <div class="text-[10px] md:text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wide mb-1" id="resPartNo">-</div>
                    <div class="text-base md:text-lg font-bold text-gray-800 dark:text-gray-100 leading-tight mb-3" id="resPartName">-</div>
                    <div class="flex flex-wrap gap-2 text-xs">
                        <span class="px-2 py-1 rounded bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300">
                            Unit: <span class="text-gray-900 dark:text-gray-100 font-semibold" id="resUnit">-</span>
                        </span>
                        <span class="px-2 py-1 rounded bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 text-blue-700 dark:text-blue-300">
                            Stock: <span class="font-semibold" id="resSystemQty">0</span>
                        </span>
                        <span class="px-2 py-1 rounded bg-purple-50 dark:bg-purple-900/20 border border-purple-100 dark:border-purple-800 text-purple-700 dark:text-purple-300">
                            Prev: <span class="font-semibold" id="resPrevReal">0</span>
                        </span>
                    </div>
                 </div>
                 
                 <!-- Actions -->
                 <div class="flex flex-col sm:flex-row items-end gap-2 w-full md:w-auto mt-2 md:mt-0">
                    <div class="w-full sm:w-44 flex flex-col">
                        <div id="unitHelperLabel" class="text-[10px] font-bold text-blue-600 dark:text-blue-400 mb-1 uppercase tracking-tight hidden">Input in Unit</div>
                        <input type="number" id="realQtyInput" step="any" 
                               class="w-full h-[42px] rounded-md border border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-800 dark:text-white text-center font-semibold px-3" 
                               placeholder="Qty">
                    </div>
                    <div class="w-full sm:w-72">
                        <input type="text" id="remarkInput" 
                               class="w-full h-[42px] rounded-md border border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-800 dark:text-white text-sm px-3" 
                               placeholder="Note (Optional)">
                    </div>
                    <button id="btnSaveCount" class="w-full sm:w-auto h-[42px] bg-green-600 hover:bg-green-700 text-white px-6 rounded-md font-semibold transition flex items-center justify-center gap-2">
                        SAVE <i class="fa-solid fa-check"></i>
                    </button>
                 </div>
             </div>
             <input type="hidden" id="currentHashId">
        </div>
        <div id="scanError" class="mt-3 text-red-600 font-semibold hidden"></div>
    </div>
    @endif
    
    @include('components.scanner-modal')

    <!-- RESULTS TABLE - Mobile Optimized -->
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg overflow-hidden mt-6 md:mt-8">
        <div class="px-4 md:px-6 py-3 md:py-4 border-b border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
                <h3 class="font-bold text-base md:text-lg text-gray-800 dark:text-gray-200">Counted Items</h3>
                <div class="flex items-center gap-2 md:gap-3 text-xs md:text-sm font-bold">
                    <span class="flex items-center gap-1 md:gap-1.5 text-green-600 bg-green-50 dark:bg-green-900/20 px-2 md:px-3 py-1 rounded-full border border-green-100 dark:border-green-800 whitespace-nowrap">
                        <i class="fa-solid fa-circle-check text-xs"></i> 
                        <span id="table-total-matched">{{ $stats['total_matched'] }}</span>
                        <span class="hidden sm:inline ml-1">Match</span>
                    </span>
                    <span class="flex items-center gap-1 md:gap-1.5 text-red-600 bg-red-50 dark:bg-red-900/20 px-2 md:px-3 py-1 rounded-full border border-red-100 dark:border-red-800 whitespace-nowrap">
                        <i class="fa-solid fa-circle-exclamation text-xs"></i> 
                        <span id="table-total-diff">{{ $stats['total_diff'] }}</span>
                        <span class="hidden sm:inline ml-1">Mismatch</span>
                    </span>
                </div>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            {{-- Results Table --}}
            <x-table id="stoDetailsTable" class="mb-6 mt-3">
                <thead class="bg-slate-50/50">
                    <tr>
                        <th class="px-6 py-4">No</th>
                        <th class="px-6 py-4">Time</th>
                        <th class="px-6 py-4">Product Details</th>
                        <th class="px-6 py-4 text-center">System</th>
                        <th class="px-4 py-4 text-center w-36 sm:w-40 md:w-44">Real</th>
                        <th class="px-6 py-4 text-center">Diff</th>
                        <th class="px-4 py-4 w-48 sm:w-56 md:w-56">Remark</th>
                        @if($stoEvent->status === 'OPEN')
                        <th class="px-6 py-4 text-right">Action</th>
                        @endif
                    </tr>
                </thead>
                <tbody id="stoTableBody" class="">
                </tbody>
            </x-table>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const scanUrl = "{{ route('inventory.sto.scan', $stoEvent->hash_id) }}";
    const saveUrl = "{{ route('inventory.sto.saveCount', $stoEvent->hash_id) }}";
    const csrfToken = "{{ csrf_token() }}";

    let table;
    // Initialize DataTable
    document.addEventListener('DOMContentLoaded', function() {
        if (window.defaultDataTable) {
            table = window.defaultDataTable('stoDetailsTable', {
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('inventory.sto.detailsData', $stoEvent->hash_id) }}",
                    type: 'GET'
                },
                columns: [
                    { data: 'row_number', className: 'px-6 py-4 text-center text-gray-600 dark:text-gray-400', orderable: false, searchable: false },
                    { data: 'updated_at', className: 'px-6 py-4' },
                    { data: 'product_info', orderable: true },
                    { data: 'system_qty', className: 'px-6 py-4 text-center font-mono text-sm text-gray-600 dark:text-gray-400' },
                    { data: 'real_qty', className: 'px-4 py-4 text-center', orderable: false },
                    { data: 'diff', className: 'px-6 py-4 text-center' },
                    { data: 'remark', className: 'px-4 py-4', orderable: false },
                    @if($stoEvent->status === 'OPEN')
                    { data: 'action', className: 'px-6 py-4 text-right', orderable: false }
                    @endif
                ],
                order: [[1, 'desc']], // Time column first
            });

            // Inline Editing for QTY - Save on Enter or blur
            $('#stoDetailsTable').on('keydown', '.qty-input', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    $(this).blur(); // Trigger save
                }
            });

            $('#stoDetailsTable').on('blur', '.qty-input', function() {
                const $input = $(this);
                const detailId = $input.data('detail-id');
                const productId = $input.data('product-id');
                const newQty = $input.val();
                const originalQty = $input.data('original-value');

                // Skip if value hasn't changed
                if (newQty === originalQty || !newQty || newQty === '') return;

                // Get existing remark from the same row
                const $row = $input.closest('tr');
                const $remarkInput = $row.find('.remark-input');
                const existingRemark = $remarkInput.length ? $remarkInput.val() : '';

                // Visual feedback
                $input.addClass('border-yellow-500 bg-yellow-50');

                // Save inline edit
                fetch(saveUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ 
                        product_id_hash: productId, 
                        real_qty: newQty,
                        remark: existingRemark
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        $input.removeClass('border-yellow-500 bg-yellow-50').addClass('border-green-500');
                        setTimeout(() => $input.removeClass('border-green-500'), 1000);
                        $input.data('original-value', newQty);
                        table.ajax.reload(null, false);
                        
                        if (data.stats && window.updateStatsCard) {
                            window.updateStatsCard(data.stats);
                        }

                        if (window.showToast) {
                            window.showToast('Quantity updated', 'success');
                        }
                    } else {
                        $input.removeClass('border-yellow-500 bg-yellow-50').addClass('border-red-500');
                        if (window.showToast) {
                            window.showToast('Error: ' + data.message, 'error');
                        }
                    }
                })
                .catch(err => {
                    console.error('Save error:', err);
                    $input.removeClass('border-yellow-500 bg-yellow-50').addClass('border-red-500');
                    if (window.showToast) {
                        window.showToast('Failed to save quantity', 'error');
                    }
                });
            });

            // Inline Editing for Remark - Save on Enter or blur (same as QTY)
            $('#stoDetailsTable').on('keydown', '.remark-input', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    $(this).blur(); // Trigger save
                }
            });

            $('#stoDetailsTable').on('blur', '.remark-input', function() {
                const $input = $(this);
                const detailId = $input.data('detail-id');
                const newRemark = $input.val();
                const originalRemark = $input.data('original-value') || '';

                // Skip if value hasn't changed
                if (newRemark === originalRemark) return;

                // Get the current qty from this row
                const $row = $input.closest('tr');
                const $qtyInput = $row.find('.qty-input');
                const productId = $qtyInput.data('product-id');
                const currentQty = $qtyInput.val();

                if (!currentQty) {
                    if (window.showToast) {
                        window.showToast('Quantity is required', 'warning');
                    }
                    return;
                }

                // Visual feedback
                $input.addClass('border-yellow-500 bg-yellow-50');

                // Save remark update
                fetch(saveUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ 
                        product_id_hash: productId, 
                        real_qty: currentQty,
                        remark: newRemark
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        $input.removeClass('border-yellow-500 bg-yellow-50').addClass('border-green-500');
                        setTimeout(() => $input.removeClass('border-green-500'), 1000);
                        $input.data('original-value', newRemark);

                        if (data.stats && window.updateStatsCard) {
                            window.updateStatsCard(data.stats);
                        }

                        if (window.showToast) {
                            window.showToast('Remark saved', 'success');
                        }
                    }
                })
                .catch(err => {
                    console.error('Save error:', err);
                    $input.removeClass('border-yellow-500 bg-yellow-50').addClass('border-red-500');
                    if (window.showToast) {
                        window.showToast('Failed to save remark', 'error');
                    }
                });
            });

            // Store original values when table is drawn
            table.on('draw', function() {
                $('#stoDetailsTable .qty-input').each(function() {
                    $(this).data('original-value', $(this).val());
                });
                $('#stoDetailsTable .remark-input').each(function() {
                    $(this).data('original-value', $(this).val());
                });
            });
        }
    });

    // DOM Elements
    const resultArea = document.getElementById('scanResultArea');
    const errorArea = document.getElementById('scanError');
    const realQtyInput = document.getElementById('realQtyInput');
    const btnSave = document.getElementById('btnSaveCount');
    const resPartName = document.getElementById('resPartName');
    const resPartNo = document.getElementById('resPartNo');
    const resUnit = document.getElementById('resUnit');
    const resSystemQty = document.getElementById('resSystemQty');
    const resPrevReal = document.getElementById('resPrevReal');
    const currentHashId = document.getElementById('currentHashId');
    const btnSaveCount = document.getElementById('btnSaveCount'); // Original ID

    // Initialize Select2
    const productSelect = $('#product_detail_id');
    if (productSelect.length) {
        productSelect.select2({
            placeholder: 'Scan or Select Product...',
            allowClear: true,
            width: '100%',
            matcher: function(params, data) {
                 // Custom Matcher to search Part No and Part Name robustly
                 // Select2 default search is usually good enough, but we want robust "PartNo PartName" searching
                 return $.fn.select2.defaults.defaults.matcher(params, data);
            }
        });

        productSelect.on('change', function() {
            const hashId = $(this).val();
            if (!hashId) return;

            // Fetch Product STO Info (System Qty, etc)
            fetchStoInfo(hashId);
        });
    }

    // Init Scanner Helper (Service)
    // Ensure InventoryScanner is loaded before this.
    if (typeof InventoryScanner !== 'undefined') {
        const scanner = new InventoryScanner({
            selectId: '#product_detail_id',
            scanButtonId: '#btn-scan',
            qtyInputId: null, // We handle focus manually after fetch
            modalId: '#scannerModal'
        });
    }

    // Expose function for editItem
    window.fetchStoInfo = function(hashId) {
        if (!csrfToken || !scanUrl) return;
        
        fetch(scanUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ qr_code: hashId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showResult(data.data);
            } else {
                showError(data.message);
                if (scanResultArea) scanResultArea.classList.add('hidden');
            }
        })
        .catch(err => showError('Network Error: ' + err));
    };

    // Edit Function (Called from Table)
    window.editItem = function(hashId) {
        // Reset Select2 just in case
        if (productSelect.length) {
            productSelect.val(null).trigger('change.select2');
        }
        
        // Call fetch directly
        if(window.fetchStoInfo) {
            window.fetchStoInfo(hashId);
            // Scroll to top
            const scrollTarget = document.querySelector('.bg-white');
            if (scrollTarget) scrollTarget.scrollIntoView({ behavior: 'smooth' });
        }
    };

    // Delete Function (Called from Table)
    window.deleteItem = function(detailHashId) {
        if (!csrfToken) return;

        Swal.fire({
            title: 'Delete this item?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EF4444',
            cancelButtonColor: '#6B7280',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                const deleteUrl = "{{ route('inventory.sto.deleteDetail', ['id' => $stoEvent->hash_id, 'detailId' => '__DETAIL_ID__']) }}".replace('__DETAIL_ID__', detailHashId);

                fetch(deleteUrl, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Deleted!', 'Item has been deleted.', 'success').then(() => {
                            if (data.stats && window.updateStatsCard) {
                                window.updateStatsCard(data.stats);
                            }
                            if (table) table.ajax.reload();
                            else location.reload();
                        });
                    } else {
                        Swal.fire('Error!', data.message, 'error');
                    }
                })
                .catch(err => Swal.fire('Error!', 'Network Error: ' + err, 'error'));
            }
        });
    };

    function showResult(data) {
        if (!resultArea) return;
        
        resultArea.classList.remove('hidden');
        resPartName.innerText = data.part_name;
        resPartNo.innerText = data.part_no;
        resUnit.innerText = data.unit || 'PCS';
        resSystemQty.innerText = (data.system_qty || 0) + 0;
        resPrevReal.innerText = (data.prev_real_qty || 0) + 0;
        currentHashId.value = data.product_id_hash;
        
        // Populate existing values if any
        realQtyInput.value = data.prev_real_qty || '';
        // Explicitly set placeholder to include Unit
        realQtyInput.placeholder = 'Qty in ' + (data.unit || 'Unit');
        
        // Update helper label
        const unitHelper = document.getElementById('unitHelperLabel');
        if (unitHelper) {
            unitHelper.innerHTML = 'Input in <span class="text-blue-600 dark:text-blue-400">' + (data.unit || 'Unit') + '</span>';
            unitHelper.classList.remove('hidden');
        }

        document.getElementById('remarkInput').value = '';
        
        setTimeout(() => realQtyInput.focus(), 100);
    }

    function showError(msg) {
        if (!errorArea) return;
        
        errorArea.textContent = msg;
        errorArea.classList.remove('hidden');
        setTimeout(() => errorArea.classList.add('hidden'), 3000);
    }

    // Save Logic with Keyboard Shortcuts - Only if elements exist
    if (realQtyInput) {
        realQtyInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                const remarkInput = document.getElementById('remarkInput');
                if (remarkInput) remarkInput.focus();
            }
            if (e.key === 'Escape') {
                if (resultArea) resultArea.classList.add('hidden');
                if (productSelect.length) productSelect.val(null).trigger('change');
            }
        });
    }
    
    const remarkInput = document.getElementById('remarkInput');
    if (remarkInput) {
        remarkInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                saveCount();
            }
            if (e.key === 'Escape') {
                if (resultArea) resultArea.classList.add('hidden');
                if (productSelect.length) productSelect.val(null).trigger('change');
            }
        });
    }

    // Alt+S to save
    document.addEventListener('keydown', (e) => {
        if (e.altKey && e.key === 's') {
            e.preventDefault();
            if (resultArea && !resultArea.classList.contains('hidden')) {
                saveCount();
            }
        }
    });

    if (btnSave) {
        btnSave.addEventListener('click', saveCount);
    }

    function saveCount() {
        if (!realQtyInput || !currentHashId || !csrfToken) return;
        
        const qty = realQtyInput.value;
        const remark = remarkInput ? remarkInput.value : '';
        const hash = currentHashId.value;

        if (qty === '' || !hash) return;

        fetch(saveUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ product_id_hash: hash, real_qty: qty, remark: remark })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (data.stats && window.updateStatsCard) {
                    window.updateStatsCard(data.stats);
                }
                if (table) {
                    table.ajax.reload();
                    if (resultArea) resultArea.classList.add('hidden');
                    if (productSelect.length) productSelect.val(null).trigger('change');
                } else {
                    location.reload(); 
                }
            } else {
                alert('Error saving: ' + data.message);
            }
        });
    }

    // Enhanced Finalize Confirmation with SweetAlert2

    const finalizeForm = document.getElementById('finalizeForm');
    if (finalizeForm) {
        finalizeForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const totalItems = {{ $stats['total_items'] }};
            const totalDiff = {{ $stats['total_diff'] }};
            const countIncrease = {{ $stats['count_increase'] }};
            const countDecrease = {{ $stats['count_decrease'] }};
            const totalIncrease = {{ $stats['total_increase'] }};
            const totalDecrease = {{ $stats['total_decrease'] }};
            const netAdjustment = {{ $netAdjustment }};

            if (totalItems === 0) {
                Swal.fire('Cannot Finalize', 'Empty STO event. Please count at least one item.', 'warning');
                return;
            }

            const htmlContent = `
                <div class="text-left space-y-2">
                    <p class="font-semibold">Total Items Counted: <span class="text-blue-600">${totalItems}</span></p>
                    <p>Matched (No Change): <span class="text-green-600">{{ $stats['total_matched'] }}</span></p>
                    <p>Mismatched: <span class="text-red-600">${totalDiff}</span></p>
                    <hr class="my-2">
                    <p>Stock Increases: <span class="text-green-600">${countIncrease} items (+${totalIncrease.toFixed(2)})</span></p>
                    <p>Stock Decreases: <span class="text-red-600">${countDecrease} items (${totalDecrease.toFixed(2)})</span></p>
                    <p class="font-semibold">Net Adjustment: <span class="text-purple-600">${netAdjustment >= 0 ? '+' : ''}${netAdjustment.toFixed(2)}</span></p>
                    <hr class="my-2">
                    <p class="text-sm text-gray-600">This will <strong>LOCK</strong> the event and <strong>UPDATE</strong> stock levels.</p>
                </div>
            `;

            Swal.fire({
                title: 'Finalize STO Event?',
                html: htmlContent,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#EF4444',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'Yes, Finalize!',
                cancelButtonText: 'Cancel',
                width: '500px'
            }).then((result) => {
                if (result.isConfirmed) {
                    finalizeForm.submit();
                }
            });
        });
    }


    // Reopen Confirmation
    function confirmReopen() {
        Swal.fire({
            title: 'Reopen STO Event?',
            html: '<p>This will:</p><ul class="text-left list-disc pl-5"><li>Reverse all stock adjustments</li><li>Reopen the event for editing</li><li>Clear finalization data</li></ul>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#F59E0B',
            cancelButtonColor: '#6B7280',
            confirmButtonText: 'Yes, Reopen!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('reopenForm').submit();
            }
        });
    }

    // Helper to update Statistics Card dynamically
    window.updateStatsCard = function(data) {
        if (!data || !data.stats) return;
        
        const stats = data.stats;
        const net = data.netAdjustment;
        const prog = data.progress;

        const updateEl = (id, val) => {
            const el = document.getElementById(id);
            if (el) el.innerText = val;
        };

        const formatNum = (num) => new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num);

        updateEl('stat-total-items', stats.total_items);
        updateEl('stat-progress', prog);
        updateEl('stat-total-matched', stats.total_matched);
        updateEl('table-total-matched', stats.total_matched);
        updateEl('table-total-diff', stats.total_diff);
        updateEl('stat-count-increase', stats.count_increase);
        updateEl('stat-count-decrease', stats.count_decrease);
        
        const totalIncEl = document.getElementById('stat-total-increase');
        if (totalIncEl) totalIncEl.innerText = '+' + formatNum(stats.total_increase);
        
        const totalDecEl = document.getElementById('stat-total-decrease');
        if (totalDecEl) totalDecEl.innerText = formatNum(stats.total_decrease);
        
        const netEl = document.getElementById('stat-net-adjustment');
        if (netEl) netEl.innerText = (net >= 0 ? '+' : '') + formatNum(net);
    };

</script>

<style>
    /* Fix Select2 clear button overlapping with dropdown arrow */
    .select2-container .select2-selection--single .select2-selection__clear {
        position: absolute;
        right: 28px !important; /* Move clear button closer to arrow */
        top: 50%;
        transform: translateY(-50%);
        z-index: 10;
        margin-right: 0 !important;
    }
    
    /* Add more padding to right side when clear button is visible */
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        padding-right: 45px !important;
    }
    
    /* Ensure dropdown arrow stays in place but not too close to edge */
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        right: 10px !important; /* Move arrow away from edge */
    }
</style>

@endpush
@endsection


