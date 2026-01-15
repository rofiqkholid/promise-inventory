@extends('layouts.app')
@section('title', 'Inventory Transaction')
@section('header-title', 'Inventory Transaction')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 text-gray-900 dark:text-gray-100">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Transaction Form Panel --}}
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 sm:rounded-lg overflow-hidden">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fa-solid fa-right-left"></i> Transaction Form
                    </h3>
                </div>
                <div class="p-6">
                    <form id="transactionForm">
                        @csrf
                        {{-- Product Selection --}}
                        <div class="mb-4">
                            <div class="flex justify-between items-end mb-2">
                                <label for="product_detail_id" class="block text-sm font-medium text-gray-900 dark:text-white">Product <span class="text-red-500">*</span></label>
                                <button type="button" id="btn-scan" class="inline-flex items-center px-4 py-2 text-sm font-semibold text-blue-700 bg-blue-50 border border-blue-300 rounded-lg hover:bg-blue-100 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-900/40 dark:text-blue-300 dark:border-blue-700 dark:hover:bg-blue-900/60 transition-all shadow-sm">
                                    <i class="fa-solid fa-barcode mr-2"></i> Scan Camera
                                </button>
                            </div>
                            <select name="product_detail_id" id="product_detail_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white select2" data-placeholder="Select Product..." required>
                                <option value="">Select Product...</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->hash_id }}" data-partno="{{ $product->part_no }}">{{ $product->part_no }} {{ $product->revision ? '- ' . $product->revision : '' }} - {{ $product->part_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Transaction Category --}}
                        <div class="mb-4">
                            <label for="transaction_category_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Category <span class="text-red-500">*</span></label>
                            <select name="transaction_category_id" id="transaction_category_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" required>
                                <option value="">Select Category...</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->hash_id }}" data-effect="{{ $category->effect }}" class="{{ $category->effect == 1 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $category->name }} ({{ $category->effect == 1 ? 'IN +' : 'OUT -' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Qty & Date Row --}}
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="qty" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Qty (Unit) <span class="text-red-500">*</span></label>
                                <input type="number" name="qty" id="qty" step="1" min="1" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" required placeholder="0">
                            </div>
                            <div>
                                <label for="transaction_date" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Date <span class="text-red-500">*</span></label>
                                <input type="date" name="transaction_date" id="transaction_date" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" required value="{{ date('Y-m-d') }}">
                            </div>
                        </div>

                        {{-- PIC --}}
                        <div class="mb-4">
                            <label for="pic_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">PIC Name <span class="text-red-500">*</span></label>
                            <select name="pic_id" id="pic_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:outline-none block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white select2" required>
                                <option value="">Select PIC...</option>
                                @foreach($pics as $pic)
                                    <option value="{{ $pic->hash_id }}">{{ $pic->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Remark --}}
                        <div class="mb-6">
                            <label for="remark" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Remark</label>
                            <textarea name="remark" id="remark" rows="2" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" placeholder="Optional notes..."></textarea>
                        </div>

                        {{-- Submit Button --}}
                        <button type="submit" class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-3 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                            <i class="fa-solid fa-save mr-2"></i> Save Transaction
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Recent Transactions Table --}}
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 sm:rounded-lg overflow-hidden h-full flex flex-col">
                <div class="p-4 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-800">
                    <h3 class="text-lg font-medium text-slate-900 dark:text-white">
                        <i class="fa-solid fa-clock-rotate-left mr-2"></i> Recent Transactions
                    </h3>
                    <button id="refreshTable" class="text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200">
                        <i class="fa-solid fa-arrows-rotate"></i>
                    </button>
                </div>
                <div class="overflow-x-auto flex-1 bg-white dark:bg-gray-800">
                    <x-table id="recentTransactionTable">
                        <thead>
                            <tr>
                                <th class="px-6 py-3">Date</th>
                                <th class="px-6 py-3">Product</th>
                                <th class="px-6 py-3">Category</th>
                                <th class="px-6 py-3 text-right">Qty</th>
                                <th class="px-6 py-3">PIC</th>
                                <th class="px-6 py-3">Remark</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </x-table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Scanner Modal --}}
<div id="scannerModal" tabindex="-1" aria-hidden="true" class="hidden fixed inset-0 z-[60] justify-center items-center w-full h-full bg-black/60 backdrop-blur-sm flex p-4">
    <div class="relative w-full max-w-lg h-auto">
        <div class="relative bg-white rounded-lg shadow dark:bg-gray-800 p-4">
            <div class="flex items-center justify-between mb-4 px-2">
                <h3 class="text-xl font-medium text-gray-900 dark:text-white">QR Code Scanner</h3>
                <div class="flex items-center gap-2">
                    <button type="button" id="toggleMirror" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white p-1.5 transition-colors" title="Mirror Camera">
                        <i class="fa-solid fa-arrows-left-right w-5 h-5"></i>
                    </button>
                    <button type="button" id="closeScanner" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                        <i class="fa-solid fa-xmark w-6 h-6"></i>
                    </button>
                </div>
            </div>
            <div class="relative">
                <div id="qr-reader" class="w-full overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 bg-black"></div>
                {{-- Scanning Line Animation --}}
                <div id="scanner-line" class="hidden absolute top-0 left-0 w-full h-1 bg-blue-500 shadow-[0_0_8px_rgba(59,130,246,0.8)] z-10 animate-scan"></div>
            </div>
            <div id="qr-status" class="mt-4 text-center text-sm font-medium text-blue-600 dark:text-blue-400 p-2 rounded-lg bg-blue-50 dark:bg-blue-900/30">
                <i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Initializing Camera...
            </div>
            <div id="qr-reader-results" class="hidden"></div>
            <p class="mt-4 text-xs text-gray-500 text-center italic">Point your camera at the QR code on the product label.</p>
        </div>
    </div>
</div>
@endsection

@push('style')
<style>
    @keyframes scan {
        0% { top: 0; }
        100% { top: 100%; }
    }
    .animate-scan {
        animation: scan 2s linear infinite;
    }
    #qr-reader video {
        border-radius: 0.5rem;
    }
</style>
@endpush

@push('scripts')
{{-- html5-qrcode library --}}
<script src="https://unpkg.com/html5-qrcode"></script>
<script>
    $(document).ready(function() {
        // Initialize Select2 for Product (Searchable)
        // Note: Generic initialization is handled in helpers.js
        
        // Add Custom formatting for Category if select2 is present
        if ($.fn.select2) {
            function formatCategory(state) {
                if (!state.id) return state.text;
                let effect = $(state.element).data('effect');
                let icon = effect == 1 
                    ? '<i class="fa-solid fa-arrow-down text-emerald-500 mr-2"></i>' // IN (Arrow Down/In)
                    : '<i class="fa-solid fa-arrow-up text-red-500 mr-2"></i>';     // OUT (Arrow Up/Out)
                
                let textClass = effect == 1 ? 'text-emerald-700 dark:text-emerald-400' : 'text-red-700 dark:text-red-400';
                
                return $(`<span class="${textClass} flex items-center">${icon}${state.text}</span>`);
            }

            $('#transaction_category_id').select2({
                minimumResultsForSearch: Infinity,
                templateResult: formatCategory,
                templateSelection: formatCategory,
                width: '100%'
            });
        }

        // DataTable
        // DataTable
        var table = window.defaultDataTable('recentTransactionTable', {
            processing: true,
            serverSide: true,
            ajax: "{{ route('inventory.transaction.data') }}",
            columns: [
                { data: 'transaction_date', width: '15%' },
                { 
                    data: 'part_no', 
                    render: (d, t, r) => `<div class="font-medium text-gray-900 dark:text-white">${r.part_no}</div><div class="text-xs">${r.product_name}</div>`
                },
                { 
                    data: 'category',
                    render: (d, t, r) => {
                        // Color label based on category (simple check or passed logic)
                        // Ideally we pass effect in data, but for now simple check
                        let color = d.includes('IN') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                        return `<span class="${color} text-xs font-medium px-2 py-0.5 rounded">${d}</span>`;
                    }
                },
                { data: 'qty', className: 'text-right font-medium' },
                { data: 'pic_name' },
                { data: 'remark', render: (d) => d || '-' }
            ],
            order: [[0, 'desc']], // Order by date desc
            pageLength: 10,
            
            // Custom simplified layout for this widget-like table
            dom: "<'flex flex-col md:flex-row justify-between items-center mb-4'rt><'flex flex-col md:flex-row justify-between items-center mt-4 gap-4 px-2'i p>",
            searching: false
        });

        $('#refreshTable').click(function() {
            table.ajax.reload();
        });

        // Process QR/Scan Input (New Helper)
        function processQRInput(input) {
            input = input.trim();
            if (!input) return;

            console.log("[SCANNER] Processing:", input);
            let finalId = input;
            let displayPartNo = "";

            // 1. Detect JSON Format
            if (input.startsWith('{') && input.endsWith('}')) {
                try {
                    const data = JSON.parse(input);
                    if (data.id) {
                        displayPartNo = data.pn || '';
                        
                        // Check if it's a HashID (alphanumeric) or Base64 (Legacy)
                        // If it's a hash, we use it directly. If it looks like base64, we try decoding.
                        // Actually, our robust logic is to check BOTH against the select options.
                        
                        // Try finding option by Hash (Value)
                        let matchHash = $(`#product_detail_id option[value="${data.id}"]`);
                        if (matchHash.length > 0) {
                            finalId = matchHash.val(); // Get HashID (same as data.id)
                            console.log("[SCANNER] Matched via HashID:", data.id);
                        } else {
                            // HashID mismatch
                            console.log("[SCANNER] HashID mismatch for:", data.id);
                        }
                    }
                } catch (e) {
                    console.error("[SCANNER] JSON Parse Error:", e);
                }
            }

            // 2. Auto-Select Product
            let option = $(`#product_detail_id option[value="${finalId}"]`);
            
            if (option.length > 0) {
                $('#product_detail_id').val(finalId).trigger('change');
                
                // Visual feedback via toast
                toast('success', 'Product Selected', displayPartNo || option.text().split(' - ')[0]);
                
                // Focus Qty field
                setTimeout(() => $('#qty').focus(), 300);
                
                return true;
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Product Not Found',
                    text: `ID/Part No "${finalId}" does not exist in the current list.`,
                    timer: 2000,
                    showConfirmButton: false
                });
                return false;
            }
        }

        // Global Hardware Scanner Listener (Wedge)
        let scannerBuffer = "";
        let scannerTimeout = null;

        $(document).on('keypress', function(e) {
            // Ignore if user is typing in a textarea or certain inputs
            if ($(e.target).is('textarea') || $(e.target).is('input[type="text"]:not(#qty)')) {
                return;
            }

            // Detect fast typing (typical of hardware scanners)
            if (scannerTimeout) clearTimeout(scannerTimeout);
            
            if (e.which === 13) { // Enter usually marks end of scan
                if (scannerBuffer.length > 2) {
                    e.preventDefault();
                    processQRInput(scannerBuffer);
                    scannerBuffer = "";
                }
            } else {
                scannerBuffer += String.fromCharCode(e.which);
            }

            scannerTimeout = setTimeout(() => {
                scannerBuffer = "";
            }, 50); // Scanners are very fast, 50ms is enough to clear human typing
        });

        // Form Submit
        $('#transactionForm').submit(function(e) {
            e.preventDefault();
            
            // Basic Frontend Validation
            if (!$('#product_detail_id').val()) {
                Swal.fire('Error', 'Please select a product', 'error');
                return;
            }

            let formData = $(this).serialize();
            
            $.ajax({
                url: "{{ route('inventory.transaction.store') }}",
                type: "POST",
                data: formData,
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Saved!',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                    
                    // Reset Form
                    $('#transactionForm')[0].reset();
                    $('#product_detail_id').val('').trigger('change'); // Reset Select2
                    $('#transaction_date').val(new Date().toISOString().split('T')[0]); // Reset Date to today
                    
                    // Reload Table
                    table.ajax.reload();
                },
                error: function(xhr) {
                    let msg = xhr.responseJSON.message || 'Something went wrong';
                    Swal.fire('Error', msg, 'error');
                }
            });
        });

        // Camera Scanner Logic
        let html5QrCode = null;
        let isMirrored = false;

        $('#btn-scan').on('click', function() {
            // Mobile browsers require HTTPS for camera access
            if (location.protocol !== 'https:' && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
                Swal.fire({
                    icon: 'warning',
                    title: 'HTTPS Required',
                    text: 'Mobile browsers strictly require a secure (HTTPS) connection to access the camera. Please use HTTPS or access via localhost.',
                    footer: '<a href="https://developer.mozilla.org/en-US/docs/Web/API/MediaDevices/getUserMedia#security_requirements" target="_blank" class="text-blue-600 underline">Learn more about camera security</a>'
                });
                return;
            }

            $('#scannerModal').removeClass('hidden').addClass('flex');
            
            if (html5QrCode === null) {
                html5QrCode = new Html5Qrcode("qr-reader", { verbose: false });
            }

            const config = { 
                fps: 25, // Increased for smoother detection
                qrbox: (viewfinderWidth, viewfinderHeight) => {
                    // Make the box larger (85% of smaller edge) for easier alignment
                    let minEdgeSize = Math.min(viewfinderWidth, viewfinderHeight);
                    let qrboxSize = Math.floor(minEdgeSize * 0.85);
                    return { width: qrboxSize, height: qrboxSize };
                },
                aspectRatio: 1.0,
                showTorchButtonIfSupported: true,
                rememberLastUsedCamera: true,
                formatsToSupport: [ Html5QrcodeSupportedFormats.QR_CODE ],
                experimentalFeatures: {
                    useBarCodeDetectorIfSupported: true
                },
                // Use a higher resolution for better detail capture
                videoConstraints: {
                    facingMode: "environment",
                    width: { min: 640, ideal: 1280, max: 1920 },
                    height: { min: 480, ideal: 720, max: 1080 },
                    focusMode: "continuous"
                }
            };

            html5QrCode.start(
                config.videoConstraints,
                config,
                (decodedText) => {
                    decodedText = decodedText.trim();
                    console.log("[SCANNER] Found QR Code:", decodedText);
                    
                    // Stop scanning immediately on success
                    html5QrCode.stop().then(() => {
                        $('#scannerModal').addClass('hidden').removeClass('flex');
                        processQRInput(decodedText);
                    }).catch(err => {
                        console.error("[SCANNER] Stop failed:", err);
                        $('#scannerModal').addClass('hidden').removeClass('flex');
                        processQRInput(decodedText);
                    });
                },
                (errorMessage) => {
                    // Log errors occasionally or only if they are not "NotFound"
                    // Too many logs here can slow down the browser
                }
            ).then(() => {
                console.log("[SCANNER] Camera started successfully");
                $('#qr-status').html('<i class="fa-solid fa-expand fa-beat mr-2"></i> Scanning... Move camera to focus')
                    .removeClass('bg-blue-50 dark:bg-blue-900/30')
                    .addClass('bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400');
                $('#scanner-line').removeClass('hidden');
                applyMirror();
            }).catch((err) => {
                console.error("[SCANNER] Start failed:", err);
                $('#scanner-line').addClass('hidden');
                $('#qr-status').html('<span class="text-red-500"><i class="fa-solid fa-circle-exclamation mr-2"></i> Camera Error</span>')
                    .removeClass('bg-blue-50 dark:bg-blue-900/30')
                    .addClass('bg-red-50 dark:bg-red-900/30');
                Swal.fire({
                    icon: 'error',
                    title: 'Camera Error',
                    text: 'Unable to start camera. Error: ' + err
                });
                $('#scannerModal').addClass('hidden').removeClass('flex');
            });
        });

        function applyMirror() {
            const video = $('#qr-reader video')[0];
            if (video) {
                console.log("[SCANNER] Applying mirror state:", isMirrored);
                video.style.transform = isMirrored ? 'scaleX(-1)' : 'scaleX(1)';
            }
        }

        $('#toggleMirror').on('click', function() {
            isMirrored = !isMirrored;
            applyMirror();
            $(this).toggleClass('text-blue-600 dark:text-blue-400', isMirrored);
        });

        $('#closeScanner').on('click', function() {
            $('#scanner-line').addClass('hidden');
            if (html5QrCode && html5QrCode.isScanning) {
                html5QrCode.stop().then(() => {
                    $('#scannerModal').addClass('hidden').removeClass('flex');
                });
            } else {
                $('#scannerModal').addClass('hidden').removeClass('flex');
            }
        });
    });
</script>
@endpush
