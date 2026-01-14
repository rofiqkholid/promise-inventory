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
                <span class="px-2 py-0.5 text-xs rounded font-bold uppercase {{ $stoEvent->status === 'OPEN' ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-800' }}">
                    {{ $stoEvent->status }}
                </span>
            </div>
            <h1 class="text-xl md:text-2xl font-bold text-gray-800 dark:text-white">{{ $stoEvent->name }}</h1>
            <p class="text-xs md:text-sm text-gray-500 dark:text-gray-400">Code: {{ $stoEvent->code }} • Start: {{ $stoEvent->period_start->format('d M Y') }}</p>
        </div>

        <div class="flex flex-wrap gap-2 w-full md:w-auto">
            <a href="{{ route('inventory.sto.exportExcel', $stoEvent->hash_id) }}" class="flex-1 md:flex-none bg-green-600 hover:bg-green-700 text-white px-3 md:px-4 py-2 rounded-lg flex items-center justify-center gap-2 shadow-sm text-sm">
                <i class="fa-solid fa-download"></i> <span class="hidden sm:inline">Export</span>
            </a>
            @if($stoEvent->status === 'CLOSED')
            <form action="{{ route('inventory.sto.reopen', $stoEvent->hash_id) }}" method="POST" id="reopenForm" class="flex-1 md:flex-none">
                @csrf
                <button type="button" onclick="confirmReopen()" class="w-full bg-yellow-600 hover:bg-yellow-700 text-white px-3 md:px-4 py-2 rounded-lg flex items-center justify-center gap-2 shadow-sm text-sm">
                    <i class="fa-solid fa-rotate-left"></i> <span class="hidden sm:inline">Reopen</span>
                </button>
            </form>
            @endif
            @if($stoEvent->status === 'OPEN')
            <form action="{{ route('inventory.sto.finalize', $stoEvent->hash_id) }}" method="POST" id="finalizeForm" class="flex-1 md:flex-none">
                @csrf
                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white px-3 md:px-4 py-2 rounded-lg flex items-center justify-center gap-2 shadow-sm text-sm">
                    <i class="fa-solid fa-lock"></i> <span class="hidden sm:inline">Finalize</span>
                </button>
            </form>
            @endif
        </div>
    </div>

    @php
        $totalIncrease = $details->where('diff_qty', '>', 0)->sum('diff_qty');
        $totalDecrease = $details->where('diff_qty', '<', 0)->sum('diff_qty');
        $netAdjustment = $details->sum('diff_qty');
        $totalProducts = \App\Models\InventoryModel\InventoryProduct::where('is_active', 1)->count();
        $progress = $totalProducts > 0 ? round(($stats['total_items'] / $totalProducts) * 100, 1) : 0;
    @endphp

    <!-- Statistics Dashboard - Compact Horizontal & Mobile Friendly -->
    <div class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-700 rounded-lg p-3 md:p-4 mb-4 md:mb-6 border border-gray-200 dark:border-gray-600 overflow-x-auto">
        <div class="flex items-center gap-4 md:gap-6 text-xs md:text-sm whitespace-nowrap">
            <div class="flex items-center gap-2">
                <span class="text-gray-600 dark:text-gray-400">Total:</span>
                <span class="font-bold text-base md:text-lg text-blue-600 dark:text-blue-400">{{ $stats['total_items'] }}</span>
                <span class="text-[10px] md:text-xs text-gray-500 dark:text-gray-400">({{ $progress }}%)</span>
            </div>
            <span class="text-gray-300 dark:text-gray-600">|</span>
            <div class="flex items-center gap-2">
                <span class="text-green-600 dark:text-green-400">Inc:</span>
                <span class="font-bold text-green-700 dark:text-green-300">+{{ number_format($totalIncrease, 2) }}</span>
                <span class="text-[10px] md:text-xs text-gray-500 dark:text-gray-400">({{ $details->where('diff_qty', '>', 0)->count() }})</span>
            </div>
            <span class="text-gray-300 dark:text-gray-600">|</span>
            <div class="flex items-center gap-2">
                <span class="text-red-600 dark:text-red-400">Dec:</span>
                <span class="font-bold text-red-700 dark:text-red-300">{{ number_format($totalDecrease, 2) }}</span>
                <span class="text-[10px] md:text-xs text-gray-500 dark:text-gray-400">({{ $details->where('diff_qty', '<', 0)->count() }})</span>
            </div>
            <span class="text-gray-300 dark:text-gray-600">|</span>
            <div class="flex items-center gap-2">
                <span class="text-purple-600 dark:text-purple-400">Net:</span>
                <span class="font-bold text-purple-700 dark:text-purple-300">{{ $netAdjustment >= 0 ? '+' : '' }}{{ number_format($netAdjustment, 2) }}</span>
            </div>
            <span class="text-gray-300 dark:text-gray-600">|</span>
            <div class="flex items-center gap-2">
                <span class="text-gray-600 dark:text-gray-400">Match:</span>
                <span class="font-bold text-gray-700 dark:text-gray-300">{{ $stats['total_matched'] }}</span>
            </div>
        </div>
    </div>

    @if($stoEvent->status === 'OPEN')
    <!-- SCANNER SECTION - Responsive -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 md:p-6 mb-4 md:mb-6">
        <label class="block text-xs md:text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
            <i class="fa-solid fa-barcode"></i> Select or Scan Product
        </label>
        
        <div class="flex gap-2">
            <div class="flex-1">
                <select id="product_detail_id" class="select2 w-full text-sm" data-placeholder="Select Product via Search or Scanner...">
                    <option value="">Select Product via Search or Scanner...</option>
                    @foreach($products as $product)
                        <option value="{{ $product->hash_id }}" data-partno="{{ $product->part_no }}">
                            {{ $product->part_no }} {{ $product->revision ? '- ' . $product->revision : '' }} - {{ $product->part_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button id="btn-scan" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg border border-gray-300 transition-colors" title="Open Scanner">
                <i class="fa-solid fa-camera"></i>
            </button>
        </div>

        <!-- Clean Result / Input Form -->
        <div class="border-t dark:border-gray-700 pt-5 hidden" id="scanResultArea">
             <div class="flex flex-col md:flex-row items-center gap-4 text-sm">
                 <!-- Product Info - Simplified -->
                 <div class="flex-1 w-full space-y-1">
                    <div class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-tight" id="resPartNo">-</div>
                    <div class="text-base font-semibold text-gray-800 dark:text-gray-200" id="resPartName">-</div>
                    <div class="flex gap-4 text-xs text-gray-500 dark:text-gray-400">
                        <span>Unit: <span class="font-bold text-gray-700 dark:text-gray-300" id="resUnit">-</span></span>
                        <span>Stock: <span class="font-bold text-gray-700 dark:text-gray-300" id="resSystemQty">0</span></span>
                        <span>Prev: <span class="font-bold text-gray-700 dark:text-gray-300" id="resPrevReal">0</span></span>
                    </div>
                 </div>
                 
                 <!-- Actions - Inline-ish -->
                 <div class="flex flex-col sm:flex-row items-center gap-2 w-full md:w-auto">
                    <div class="flex w-full sm:w-32">
                        <input type="number" id="realQtyInput" step="any" 
                               class="w-full rounded border-gray-300 focus:border-blue-500 focus:ring-0 text-center font-bold px-2 py-2 shadow-sm" 
                               placeholder="Qty">
                    </div>
                    <div class="flex w-full sm:w-48">
                        <input type="text" id="remarkInput" 
                               class="w-full rounded border-gray-300 focus:border-blue-500 focus:ring-0 text-sm px-3 py-2 shadow-sm" 
                               placeholder="Note (Optional)">
                    </div>
                    <button id="btnSaveCount" class="w-full sm:w-auto bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded font-bold shadow-sm transition flex items-center justify-center gap-2">
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

    <!-- DATA TABLE - Responsive -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="px-4 md:px-6 py-3 md:py-4 border-b dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-700">
            <h3 class="font-semibold text-sm md:text-base text-gray-700 dark:text-gray-200">Counted Items ({{ $stats['total_items'] }})</h3>
            <div class="text-xs md:text-sm gap-3 md:gap-4 flex">
                <span class="text-green-600"><i class="fa-solid fa-check-circle"></i> Match: {{ $stats['total_matched'] }}</span>
                <span class="text-red-600"><i class="fa-solid fa-exclamation-circle"></i> Diff: {{ $stats['total_diff'] }}</span>
            </div>
        </div>
        <div class="overflow-x-auto">
        <table class="w-full text-left text-xs md:text-sm">
            <thead class="bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 uppercase font-semibold text-[10px] md:text-xs">
                <tr>
                    <th class="px-2 md:px-4 py-2 md:py-3">Time</th>
                    <th class="px-2 md:px-4 py-2 md:py-3">Part No / Name</th>
                    <th class="px-2 md:px-4 py-2 md:py-3 text-center">Sys</th>
                    <th class="px-2 md:px-4 py-2 md:py-3 text-center bg-yellow-50 dark:bg-yellow-900/10">Real</th>
                    <th class="px-2 md:px-4 py-2 md:py-3 text-center">Diff</th>
                    <th class="px-2 md:px-4 py-2 md:py-3">Status</th>
                    @if($stoEvent->status === 'OPEN')
                    <th class="px-2 md:px-4 py-2 md:py-3 text-center">Action</th>
                    @endif
                </tr>
            </thead>
            <tbody id="stoTableBody" class="divide-y divide-gray-200 dark:divide-gray-600">
                @forelse($details as $detail)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                    <td class="px-4 py-3 text-gray-500">{{ $detail->updated_at->format('H:i') }}</td>
                    <td class="px-4 py-3">
                        <div class="font-bold text-gray-800 dark:text-gray-200">{{ $detail->product->product->part_no }}</div>
                        <div class="text-xs text-gray-500">{{ $detail->product->product->part_name }}</div>
                        @if($detail->remark)
                            <div class="text-xs text-gray-500 italic mt-1"><i class="fa-solid fa-message mr-1"></i> {{ $detail->remark }}</div>
                        @endif
                        @if($detail->auditor)
                            <div class="text-xs text-blue-500 mt-0.5"><i class="fa-solid fa-user mr-1"></i> {{ $detail->auditor->name }}</div>
                        @elseif($detail->auditor_id)
                             {{-- Fallback if relation not loaded or generic ID --}}
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center font-mono">{{ $detail->system_qty_snapshot + 0 }}</td>
                    <td class="px-4 py-3 text-center font-mono font-bold bg-yellow-50 dark:bg-yellow-900/10 text-blue-600">
                        {{ $detail->real_qty_input + 0 }}
                    </td>
                    <td class="px-4 py-3 text-center font-bold">
                        @php $diff = $detail->real_qty_input - $detail->system_qty_snapshot; @endphp
                        @if($diff > 0)
                            <span class="text-green-600">+{{ $diff + 0 }}</span>
                        @elseif($diff < 0)
                            <span class="text-red-600">{{ $diff + 0 }}</span>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if($diff == 0)
                            <span class="px-2 py-0.5 rounded text-xs bg-green-100 text-green-800">MATCH</span>
                        @else
                            <span class="px-2 py-0.5 rounded text-xs bg-red-100 text-red-800">MISMATCH</span>
                        @endif
                    </td>
                    @if($stoEvent->status === 'OPEN')
                    <td class="px-4 py-3 text-center">
                        <button type="button" onclick="editItem('{{ $detail->product->hash_id }}')" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 transition-colors mr-2" title="Edit Count">
                            <i class="fa-solid fa-pen-to-square text-lg"></i>
                        </button>
                        <button type="button" onclick="deleteItem('{{ $detail->hash_id }}')" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 transition-colors" title="Delete">
                            <i class="fa-solid fa-trash text-lg"></i>
                        </button>
                    </td>
                    @endif
                </tr>
                @empty
                <tr>
                    <td colspan="{{ $stoEvent->status === 'OPEN' ? '7' : '6' }}" class="px-4 py-8 text-center text-gray-400 italic">No items scanned yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>

@push('scripts')
{{-- scanner-modal partial already includes html5-qrcode and inventory-scanner.js --}}
<script>
    const scanUrl = "{{ route('inventory.sto.scan', $stoEvent->hash_id) }}";
    const saveUrl = "{{ route('inventory.sto.saveCount', $stoEvent->hash_id) }}";
    const csrfToken = "{{ csrf_token() }}";

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
                        Swal.fire('Deleted!', 'Item has been deleted.', 'success').then(() => location.reload());
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
        resUnit.innerText = data.unit_name;
        resSystemQty.innerText = (data.current_stock_qty || 0) + 0;
        resPrevReal.innerText = (data.last_real_qty || 0) + 0;
        currentHashId.value = data.hash_id;
        
        // Populate existing values if any
        realQtyInput.value = data.last_real_qty || '';
        document.getElementById('remarkInput').value = data.last_remark || '';
        
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
                location.reload(); 
            } else {
                alert('Error saving: ' + data.message);
            }
        });
    }

    // Enhanced Finalize Confirmation with SweetAlert2
    @if($stoEvent->status === 'OPEN')
    const finalizeForm = document.getElementById('finalizeForm');
    if (finalizeForm) {
        finalizeForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const totalItems = {{ $stats['total_items'] }};
            const totalDiff = {{ $stats['total_diff'] }};
            const totalIncrease = {{ $details->where('diff_qty', '>', 0)->count() }};
            const totalDecrease = {{ $details->where('diff_qty', '<', 0)->count() }};
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
                    <p>Stock Increases: <span class="text-green-600">${totalIncrease} items (+{{ number_format($totalIncrease, 2) }})</span></p>
                    <p>Stock Decreases: <span class="text-red-600">${totalDecrease} items ({{ number_format($totalDecrease, 2) }})</span></p>
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
    @endif

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

</script>
@endpush
@endsection
