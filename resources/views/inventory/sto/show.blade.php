@extends('layouts.app')

@section('content')
<div class="p-6">
    <!-- Header -->
    <div class="flex justify-between items-start mb-6">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('inventory.sto.index') }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    <i class="fa-solid fa-arrow-left"></i> Back
                </a>
                <span class="text-gray-300">|</span>
                <span class="px-2 py-0.5 text-xs rounded font-bold uppercase {{ $stoEvent->status === 'OPEN' ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-800' }}">
                    {{ $stoEvent->status }}
                </span>
            </div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">{{ $stoEvent->name }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Code: {{ $stoEvent->code }} • Start: {{ $stoEvent->period_start->format('d M Y') }}</p>
        </div>

        @if($stoEvent->status === 'OPEN')
        <form action="{{ route('inventory.sto.finalize', $stoEvent->hash_id) }}" method="POST" onsubmit="return confirm('Are you sure you want to finalize this STO? This will lock the event and create inventory adjustment transactions. This cannot be undone.')">
            @csrf
            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 shadow-sm">
                <i class="fa-solid fa-lock"></i> Finalize & Adjust
            </button>
        </form>
        @endif
    </div>

    @if($stoEvent->status === 'OPEN')
    <!-- SCANNER SECTION -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
            <i class="fa-solid fa-barcode"></i> Select or Scan Product
        </label>
        
        <div class="flex gap-4 mb-6">
            <div class="flex-1">
                <select id="product_detail_id" class="select2 w-full" data-placeholder="Scan or Select Product...">
                    <option value="">Scan or Select Product...</option>
                    @foreach($products as $product)
                        <option value="{{ $product->hash_id }}" data-partno="{{ $product->part_no }}">
                            {{ $product->part_no }} {{ $product->revision ? '- ' . $product->revision : '' }} - {{ $product->part_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button id="btn-scan" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 whitespace-nowrap shadow-sm">
                <i class="fa-solid fa-camera"></i> Scan Camera
            </button>
        </div>

        <!-- Result / Input Form -->
        <div class="border-t dark:border-gray-700 pt-6 hidden" id="scanResultArea">
             <div class="flex flex-col md:flex-row gap-6">
                 <div class="flex-1">
                     <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-2" id="resPartName">-</h3>
                     <div class="grid grid-cols-2 gap-4 text-sm text-gray-600 dark:text-gray-300">
                         <div>Part No: <span class="font-mono font-bold text-gray-900 dark:text-white" id="resPartNo">-</span></div>
                         <div>Unit: <span class="font-bold text-gray-900 dark:text-white" id="resUnit">-</span></div>
                         <div>System Stock: <span class="font-bold text-blue-600" id="resSystemQty">-</span></div>
                         <div>Previous Real: <span class="font-bold text-gray-500" id="resPrevReal">-</span></div>
                     </div>
                 </div>
                 
                 <div class="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-lg border border-yellow-200 dark:border-yellow-700 flex flex-col justify-center items-center gap-2">
                     <label class="block text-sm font-bold text-gray-800 dark:text-gray-200">Enter REAL Quantity</label>
                     <div class="flex gap-2 w-full">
                         <input type="number" id="realQtyInput" step="any" class="flex-1 rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-lg font-bold text-center" placeholder="Qty">
                     </div>
                     <input type="text" id="remarkInput" class="w-full rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm" placeholder="Remark / Note (Optional)">
                     <button id="btnSaveCount" class="w-full bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded font-bold shadow transition">
                         SAVE <i class="fa-solid fa-check ml-1"></i>
                     </button>
                 </div>
             </div>
             <input type="hidden" id="currentHashId">
        </div>
        <div id="scanError" class="mt-3 text-red-600 font-semibold hidden"></div>
    </div>
    @endif
    
    @include('components.scanner-modal')

    <!-- DATA TABLE -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-700">
            <h3 class="font-semibold text-gray-700 dark:text-gray-200">Counted Items ({{ $stats['total_items'] }})</h3>
            <div class="text-sm gap-4 flex">
                <span class="text-green-600"><i class="fa-solid fa-check-circle"></i> Matched: {{ $stats['total_matched'] }}</span>
                <span class="text-red-600"><i class="fa-solid fa-exclamation-circle"></i> Diff: {{ $stats['total_diff'] }}</span>
            </div>
        </div>
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 uppercase font-semibold">
                <tr>
                    <th class="px-4 py-3">Time</th>
                    <th class="px-4 py-3">Part No / Name</th>
                    <th class="px-4 py-3 text-center">System Qty (Snapshot)</th>
                    <th class="px-4 py-3 text-center bg-yellow-50 dark:bg-yellow-900/10">Real Qty</th>
                    <th class="px-4 py-3 text-center">Diff</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-center">Action</th>
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
                    <td class="px-4 py-3 text-center">
                        <button type="button" onclick="editItem('{{ $detail->product->hash_id }}')" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 transition-colors" title="Edit Count">
                            <i class="fa-solid fa-pen-to-square text-lg"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-gray-400 italic">No items scanned yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
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

    $(document).ready(function() {
        // Init Select2
        $('#product_detail_id').select2({
            width: '100%',
            matcher: function(params, data) {
                 // Custom Matcher to search Part No and Part Name robustly
                 // Select2 default search is usually good enough, but we want robust "PartNo PartName" searching
                 return $.fn.select2.defaults.defaults.matcher(params, data);
            }
        });

        // Init Scanner Helper (Service)
        const scanner = new InventoryScanner({
            selectId: '#product_detail_id',
            scanButtonId: '#btn-scan',
            qtyInputId: null, // We handle focus manually after fetch
            modalId: '#scannerModal'
        });

        // Event: When Product is Selected (via Scan OR Manual Selection)
        // Use 'change' because inventory-scanner.js performs .val().trigger('change')
        $('#product_detail_id').on('change', function(e) {
            const hashId = $(this).val();
            if(!hashId) return;

            // Fetch Product STO Info (System Qty, etc)
            fetchStoInfo(hashId);
        });

        // Expose function for editItem
        window.fetchStoInfo = function(hashId) {
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
                    resultArea.classList.add('hidden');
                }
            })
            .catch(err => showError('Network Error: ' + err));
        };
    });

    // Edit Function (Called from Table)
    window.editItem = function(hashId) {
        // Reset Select2 just in case
        $('#product_detail_id').val(null).trigger('change.select2'); // Don't trigger 'change' or it might loop if logic was different
        
        // Call fetch directly
        if(window.fetchStoInfo) {
            window.fetchStoInfo(hashId);
            // Scroll to top
            document.querySelector('.bg-white.shadow-md').scrollIntoView({ behavior: 'smooth' });
        }
    };

    function showResult(data) {
        resultArea.classList.remove('hidden');
        resPartName.textContent = data.part_name;
        resPartNo.textContent = data.part_no;
        resUnit.textContent = data.unit;
        resSystemQty.textContent = parseFloat(data.system_qty);
        resPrevReal.textContent = data.prev_real_qty !== null ? parseFloat(data.prev_real_qty) : '-';
        currentHashId.value = data.product_id_hash;

        realQtyInput.value = '';
        realQtyInput.focus();
    }

    function showError(msg) {
        errorArea.textContent = msg;
        errorArea.classList.remove('hidden');
        setTimeout(() => errorArea.classList.add('hidden'), 3000);
    }

    // Save Logic
    realQtyInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') document.getElementById('remarkInput').focus();
    });
    document.getElementById('remarkInput').addEventListener('keydown', (e) => {
         if (e.key === 'Enter') saveCount();
    });

    btnSave.addEventListener('click', saveCount);

    function saveCount() {
        const qty = realQtyInput.value;
        const remark = document.getElementById('remarkInput').value;
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
                // Success
                // Reload Page to update table
                location.reload(); 
            } else {
                alert('Error saving: ' + data.message);
            }
        });
    }
</script>
@endpush
@endsection
