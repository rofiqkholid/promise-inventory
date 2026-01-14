{{-- Scanner Modal Partial --}}
{{-- Requires: public/js/inventory-scanner.js --}}

<div id="scannerModal" tabindex="-1" aria-hidden="true" class="hidden fixed inset-0 z-[60] justify-center items-center w-full h-full bg-black bg-opacity-75 flex p-4">
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
                <div id="qr-reader" class="w-full overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 bg-black" style="min-height:300px"></div>
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
        object-fit: cover;
        border-radius: 0.5rem;
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/html5-qrcode"></script>
<script src="{{ asset('js/inventory-scanner.js') }}"></script>
@endpush
