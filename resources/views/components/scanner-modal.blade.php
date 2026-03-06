{{-- Scanner Modal Partial --}}

<div id="scannerModal" tabindex="-1" aria-hidden="true" class="hidden fixed inset-0 z-[60] justify-center items-center w-full h-full bg-slate-900/60 backdrop-blur-sm flex p-4 transition-all duration-300">
    <div class="relative w-full max-w-lg h-auto">
        <div class="relative bg-white dark:bg-gray-800 rounded-xs border border-slate-200 dark:border-gray-700 shadow-xl overflow-hidden">
            <!-- Header -->
            <div class="px-5 py-4 border-b border-slate-100 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white tracking-tight">QR Scanner</h3>
                <div class="flex items-center gap-1.5">
                    <button type="button" id="toggleMirror" class="w-8 h-8 flex items-center justify-center rounded-xs text-gray-400 hover:text-gray-700 dark:hover:text-white transition-all" title="Mirror Camera">
                        <i class="fa-solid fa-arrows-left-right text-xs"></i>
                    </button>
                    <button type="button" id="closeScanner" class="w-8 h-8 flex items-center justify-center rounded-xs text-gray-400 hover:text-rose-600 transition-all">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
            </div>

            <!-- Scanner Area -->
            <div class="p-4">
                <div class="relative overflow-hidden rounded-xs bg-black aspect-square border border-slate-200 dark:border-gray-700 shadow-inner">
                    <div id="qr-reader" class="w-full h-full overflow-hidden bg-black flex items-center justify-center"></div>
                </div>

                <div id="qr-status" class="mt-4 flex items-center justify-center gap-2 py-2 px-4 rounded-xs text-sm font-medium transition-all border border-transparent">
                    <i class="fa-solid fa-circle-notch fa-spin text-xs"></i> 
                    <span>Initializing Engine...</span>
                </div>

                <p class="mt-3 text-[11px] text-gray-400 dark:text-gray-500 font-medium italic text-center">
                    Align the QR code within the frame.
                </p>
            </div>
        </div>
    </div>
</div>

@push('style')
<style>
    #qr-reader video {
        object-fit: cover !important;
        border-radius: 2px !important;
        z-index: 10;
        position: relative;
    }
    #qr-reader {
        border: none !important;
    }
    #qr-reader__scan_region {
        background: black !important;
    }
    #qr-status.status-initializing {
        @apply bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 border-primary-100 dark:border-primary-800/30;
    }
    #qr-status.status-scanning {
        @apply bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 border-emerald-100 dark:border-emerald-800/30;
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/html5-qrcode"></script>
<script>
    /**
     * Inventory Scanner Helper
     * Handles Hardware Scanner (Wedge) and Camera Scanner (html5-qrcode)
     */
    class InventoryScanner {
        constructor(config) {
            this.selectId = config.selectId || '#product_detail_id';
            this.scanButtonId = config.scanButtonId || '#btn-scan';
            this.qtyInputId = config.qtyInputId || '#qty';
            this.modalId = config.modalId || '#scannerModal';

            // State
            this.isMirrored = false;
            this.html5QrCode = null;
            this.scannerBuffer = "";
            this.scannerTimeout = null;

            this.init();
        }

        init() {
            this.initHardwareListener();
            this.initCameraListener();
        }

        initHardwareListener() {
            $(document).on('keypress', (e) => {
                if ($(e.target).is('textarea')) return;
                
                if (this.scannerTimeout) clearTimeout(this.scannerTimeout);

                if (e.which === 13) { // Enter
                    if (this.scannerBuffer.length > 2) {
                        e.preventDefault();
                        this.processQRInput(this.scannerBuffer);
                        this.scannerBuffer = "";
                    }
                } else {
                    this.scannerBuffer += String.fromCharCode(e.which);
                }

                this.scannerTimeout = setTimeout(() => {
                    this.scannerBuffer = "";
                }, 50);
            });
        }

        initCameraListener() {
            $(this.scanButtonId).on('click', () => {
                if (location.protocol !== 'https:' && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'HTTPS Required',
                        text: 'Mobile browsers strictly require a secure (HTTPS) connection to access the camera.',
                    });
                    return;
                }

                $(this.modalId).removeClass('hidden').addClass('flex');
                $('#qr-status').addClass('status-initializing').html('<i class="fa-solid fa-circle-notch fa-spin text-xs mr-2"></i> Initializing Engine...');
                this.startCamera();
            });

            $('#closeScanner').on('click', () => this.stopCamera());

            $('#toggleMirror').on('click', (e) => {
                this.isMirrored = !this.isMirrored;
                this.applyMirror();
                $(e.currentTarget).toggleClass('text-primary-600 dark:text-primary-400', this.isMirrored);
            });
        }

        startCamera() {
            if (this.html5QrCode === null) {
                this.html5QrCode = new Html5Qrcode("qr-reader", { verbose: false });
            }

            const config = {
                fps: 25,
                qrbox: (viewfinderWidth, viewfinderHeight) => {
                    let minEdgeSize = Math.min(viewfinderWidth, viewfinderHeight);
                    let qrboxSize = Math.floor(minEdgeSize * 0.85);
                    return { width: qrboxSize, height: qrboxSize };
                },
                aspectRatio: 1.0,
                showTorchButtonIfSupported: true,
                formatsToSupport: [Html5QrcodeSupportedFormats.QR_CODE],
                videoConstraints: {
                    facingMode: "environment",
                    focusMode: "continuous"
                }
            };

            this.html5QrCode.start(
                config.videoConstraints,
                config,
                (decodedText) => {
                    this.stopCamera();
                    this.processQRInput(decodedText.trim());
                },
                (errorMessage) => { } 
            ).then(() => {
                $('#qr-status').html('<i class="fa-solid fa-expand fa-beat text-xs mr-2"></i> Scanning System Ready')
                    .removeClass('status-initializing')
                    .addClass('status-scanning');
                this.applyMirror();
            }).catch((err) => {
                console.error(err);
                Swal.fire('Camera Error', 'Unable to start camera.', 'error');
                $(this.modalId).addClass('hidden').removeClass('flex');
            });
        }

        stopCamera() {
            $(this.modalId).addClass('hidden').removeClass('flex');

            if (this.html5QrCode && this.html5QrCode.isScanning) {
                this.html5QrCode.stop().catch(console.error);
            }
        }

        applyMirror() {
            const video = $('#qr-reader video')[0];
            if (video) {
                video.style.transform = this.isMirrored ? 'scaleX(-1)' : 'scaleX(1)';
            }
        }

        processQRInput(input) {
            if (!input) return;
            console.log("[SCANNER] Processing:", input);

            let finalId = input;
            let displayPartNo = "";

            // Handle URL input (e.g., http://.../scan-info/HASH_ID)
            if (input.includes('/scan-info/')) {
                const parts = input.split('/');
                finalId = parts[parts.length - 1].split('?')[0]; // Get the hash ID, ignore query params if any
            } 
            // Handle legacy JSON input
            else if (input.startsWith('{') && input.endsWith('}')) {
                try {
                    const data = JSON.parse(input);
                    if (data.id) {
                        finalId = data.id;
                        displayPartNo = data.pn || '';
                    }
                } catch (e) { console.error("JSON Parse Error", e); }
            }

            let option = $(`${this.selectId} option[value="${finalId}"]`);

            if (option.length > 0) {
                $(this.selectId).val(finalId).trigger('change');
                
                const prodName = displayPartNo || option.text().split(' - ')[0];
                if (window.showToast) {
                    window.showToast(`Product Selected: ${prodName}`, 'success');
                }

                setTimeout(() => $(this.qtyInputId).focus(), 300);
            } else {
                if (window.showToast) {
                    window.showToast(`Product Not Found: ${finalId}`, 'warning');
                } else {
                    alert(`Product Not Found: ${finalId}`);
                }
            }
        }
    }
</script>
@endpush
