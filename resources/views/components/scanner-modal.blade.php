{{-- Scanner Modal Partial --}}

<div id="scannerModal" tabindex="-1" aria-hidden="true" class="hidden fixed inset-0 z-[60] justify-center items-center w-full h-full bg-slate-900/50 flex p-4">
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
            <div class="relative overflow-hidden rounded-lg bg-black h-[300px] md:h-[400px]">
                <div id="qr-reader" class="w-full h-full overflow-hidden border border-gray-200 dark:border-gray-700 bg-black"></div>
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
    #qr-reader video {
        object-fit: cover !important;
        border-radius: 0.5rem;
        z-index: 10;
        position: relative;
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
                this.startCamera();
            });

            $('#closeScanner').on('click', () => this.stopCamera());

            $('#toggleMirror').on('click', (e) => {
                this.isMirrored = !this.isMirrored;
                this.applyMirror();
                $(e.currentTarget).toggleClass('text-blue-600 dark:text-blue-400', this.isMirrored);
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
                $('#qr-status').html('<i class="fa-solid fa-expand fa-beat mr-2"></i> Scanning...')
                    .removeClass('bg-blue-50 dark:bg-blue-900/30')
                    .addClass('bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400');
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
