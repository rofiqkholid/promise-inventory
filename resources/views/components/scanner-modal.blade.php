{{-- Scanner Modal Partial --}}

<div id="scannerModal" tabindex="-1" aria-hidden="true" class="hidden fixed inset-0 z-[60] justify-center items-center w-full h-full bg-slate-900/50 flex p-4 transition-all duration-300">
    <div class="relative w-full max-w-lg h-auto">
        <div class="relative bg-white dark:bg-gray-800 rounded-xs border border-slate-200 dark:border-gray-700 shadow-xl overflow-hidden">
            <!-- Header -->
            <div class="px-5 py-3.5 border-b border-slate-100 dark:border-gray-700 flex flex-col gap-2.5 bg-slate-50/50 dark:bg-gray-800">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm sm:text-base font-semibold text-gray-900 dark:text-white tracking-tight flex items-center gap-2">
                        <i class="fa-solid fa-qrcode text-primary-600"></i> QR Scanner
                    </h3>
                    <div class="flex items-center gap-2">
                        <button type="button" id="toggleMirror" class="w-8.5 h-8.5 min-w-[34px] min-h-[34px] flex items-center justify-center rounded-xs bg-slate-100 hover:bg-slate-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-slate-600 dark:text-gray-300 transition-all active:scale-95 border border-slate-200/80 dark:border-gray-600 shadow-2xs" title="Mirror Camera View">
                            <i class="fa-solid fa-arrows-rotate text-xs"></i>
                        </button>
                        <button type="button" id="closeScanner" class="w-8.5 h-8.5 min-w-[34px] min-h-[34px] flex items-center justify-center rounded-xs bg-slate-100 hover:bg-rose-100 hover:text-rose-600 dark:bg-gray-700 dark:hover:bg-rose-900/40 dark:text-gray-300 dark:hover:text-rose-400 text-slate-600 transition-all active:scale-95 border border-slate-200/80 dark:border-gray-600 shadow-2xs" title="Close Scanner">
                            <i class="fa-solid fa-xmark text-sm"></i>
                        </button>
                    </div>
                </div>
                <!-- Camera Select Dropdown (Auto-shown if device has multiple cameras) -->
                <div id="cameraSelectContainer" class="hidden">
                    <select id="cameraSelect" class="w-full min-h-[42px] px-3 py-2 bg-white dark:bg-gray-900 border border-slate-200 dark:border-gray-700 rounded-xs text-xs text-slate-800 dark:text-gray-200 font-medium focus:outline-none focus:border-primary-500 leading-normal shadow-2xs cursor-pointer">
                    </select>
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

                <div class="mt-3 p-2 bg-slate-50 dark:bg-slate-900/60 rounded border border-slate-200/60 dark:border-gray-700/60 flex items-start gap-2 text-[10px] text-slate-500 dark:text-gray-400">
                    <i class="fa-solid fa-lightbulb text-amber-500 mt-0.5 shrink-0"></i>
                    <span><strong>Handheld Tip:</strong> You can scan barcodes directly using the <strong>hardware trigger button</strong> on your scanner device without opening this camera window.</span>
                </div>
            </div>
        </div>
    </div>
</div>

@push('style')
<style>
    #qr-reader {
        border: none !important;
        width: 100% !important;
        height: 100% !important;
    }
    #qr-reader video, #qr-reader__scan_region video {
        object-fit: cover !important;
        width: 100% !important;
        height: 100% !important;
        border-radius: 2px !important;
    }
    #qr-reader__scan_region {
        background: transparent !important;
        width: 100% !important;
        height: 100% !important;
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
<script src="{{ asset('js/html5-qrcode.min.js') }}"></script>
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
            this.cameras = [];

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
                
                // Allow DOM animation/layout to finish before starting stream
                setTimeout(() => {
                    this.startCamera();
                }, 250);
            });

            $('#closeScanner').on('click', () => this.stopCamera());

            $('#toggleMirror').on('click', (e) => {
                this.isMirrored = !this.isMirrored;
                this.applyMirror();
                $(e.currentTarget).toggleClass('bg-primary-100 dark:bg-primary-900/50 text-primary-700 dark:text-primary-300 border-primary-300 dark:border-primary-700', this.isMirrored);
            });

            $('#cameraSelect').on('change', (e) => {
                const selectedCameraId = $(e.target).val();
                if (selectedCameraId) {
                    this.switchCameraTo(selectedCameraId);
                }
            });
        }

        startCamera() {
            if (this.html5QrCode === null) {
                this.html5QrCode = new Html5Qrcode("qr-reader", { verbose: false });
            }

            Html5Qrcode.getCameras().then(devices => {
                if (devices && devices.length > 0) {
                    this.cameras = devices;
                    
                    const $select = $('#cameraSelect');
                    $select.empty();
                    
                    devices.forEach((device, idx) => {
                        const label = device.label || `Camera ${idx + 1} (${device.id.substring(0, 6)}...)`;
                        $select.append(new Option(label, device.id));
                    });

                    if (devices.length > 1) {
                        $('#cameraSelectContainer').removeClass('hidden');
                    } else {
                        $('#cameraSelectContainer').addClass('hidden');
                    }

                    // Find main rear/back camera
                    let preferredCam = devices.find(d => {
                        const l = (d.label || '').toLowerCase();
                        return (l.includes('back') || l.includes('rear') || l.includes('environment') || l.includes('main')) && !l.includes('front');
                    });

                    let selectedId = preferredCam ? preferredCam.id : devices[devices.length - 1].id;
                    $select.val(selectedId);

                    this.startCameraWithId(selectedId);
                } else {
                    this.startCameraFallback();
                }
            }).catch(err => {
                console.warn("getCameras failed, trying fallback facingMode", err);
                this.startCameraFallback();
            });
        }

        switchCameraTo(cameraId) {
            if (this.html5QrCode && this.html5QrCode.isScanning) {
                this.html5QrCode.stop().then(() => {
                    this.startCameraWithId(cameraId);
                }).catch(err => {
                    console.error("Error stopping camera for switch:", err);
                    this.startCameraWithId(cameraId);
                });
            } else {
                this.startCameraWithId(cameraId);
            }
        }

        startCameraWithId(cameraId) {
            const config = {
                fps: 25,
                qrbox: (viewfinderWidth, viewfinderHeight) => {
                    let minEdgeSize = Math.min(viewfinderWidth, viewfinderHeight);
                    let qrboxSize = Math.floor(minEdgeSize * 0.85);
                    return { width: qrboxSize, height: qrboxSize };
                },
                aspectRatio: 1.0,
                showTorchButtonIfSupported: true,
                formatsToSupport: [Html5QrcodeSupportedFormats.QR_CODE]
            };

            const onScanSuccess = (decodedText) => {
                this.stopCamera();
                this.processQRInput(decodedText.trim());
            };

            const onScanError = (errorMessage) => {};

            const applySuccessState = () => {
                $('#qr-status').html('<i class="fa-solid fa-expand fa-beat text-xs mr-2"></i> Scanning System Ready')
                    .removeClass('status-initializing')
                    .addClass('status-scanning');
                this.applyMirror();
            };

            this.html5QrCode.start(cameraId, config, onScanSuccess, onScanError)
                .then(applySuccessState)
                .catch(err => {
                    console.warn("Failed starting with camera ID, trying fallback facingMode", err);
                    this.startCameraFallback();
                });
        }

        startCameraFallback() {
            const config = {
                fps: 25,
                qrbox: (viewfinderWidth, viewfinderHeight) => {
                    let minEdgeSize = Math.min(viewfinderWidth, viewfinderHeight);
                    let qrboxSize = Math.floor(minEdgeSize * 0.85);
                    return { width: qrboxSize, height: qrboxSize };
                },
                aspectRatio: 1.0,
                showTorchButtonIfSupported: true,
                formatsToSupport: [Html5QrcodeSupportedFormats.QR_CODE]
            };

            const onScanSuccess = (decodedText) => {
                this.stopCamera();
                this.processQRInput(decodedText.trim());
            };

            const onScanError = (errorMessage) => {};

            const applySuccessState = () => {
                $('#qr-status').html('<i class="fa-solid fa-expand fa-beat text-xs mr-2"></i> Scanning System Ready')
                    .removeClass('status-initializing')
                    .addClass('status-scanning');
                this.applyMirror();
            };

            this.html5QrCode.start({ facingMode: "environment" }, config, onScanSuccess, onScanError)
                .then(applySuccessState)
                .catch(err => this.handleCameraError(err));
        }

        handleCameraError(err) {
            console.error("Camera Error:", err);
            Swal.fire({
                icon: 'error',
                title: 'Camera Error',
                text: 'Unable to start camera stream. Please check browser camera permissions or use the built-in hardware scanner button.'
            });
            $(this.modalId).addClass('hidden').removeClass('flex');
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
