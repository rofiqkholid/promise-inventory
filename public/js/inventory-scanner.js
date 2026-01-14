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

    /**
     * Handle Hardware Scanner Input (Fast Typing)
     */
    initHardwareListener() {
        $(document).on('keypress', (e) => {
            // Ignore if user is typing in a textarea or text input (except specially allowed ones if needed)
            // But we want to allow scanning even if focused on inputs generally, UNLESS it's a specific text field.
            // Logic from original: Ignore textarea and text inputs EXCEPT the qty field (rare case) or body.
            if ($(e.target).is('textarea')) {
                return;
            }

            // Allow scanning while focused on Select2 search field (which dynamic ID) is tricky.
            // Simplest: If typing fast, buffer it.

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

    /**
     * Handle Camera Scanner UI
     */
    initCameraListener() {
        $(this.scanButtonId).on('click', () => {
            // HTTPS Check
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

        // Close Button
        $('#closeScanner').on('click', () => this.stopCamera());

        // Mirror Toggle
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
            (errorMessage) => { } // Ignore frame errors
        ).then(() => {
            $('#qr-status').html('<i class="fa-solid fa-expand fa-beat mr-2"></i> Scanning...')
                .removeClass('bg-blue-50 dark:bg-blue-900/30')
                .addClass('bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400');
            $('#scanner-line').removeClass('hidden');
            this.applyMirror();
        }).catch((err) => {
            console.error(err);
            $('#scanner-line').addClass('hidden');
            Swal.fire('Camera Error', 'Unable to start camera.', 'error');
            $(this.modalId).addClass('hidden').removeClass('flex');
        });
    }

    stopCamera() {
        $('#scanner-line').addClass('hidden');
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

    /**
     * Process the Scanned Data
     */
    processQRInput(input) {
        if (!input) return;
        console.log("[SCANNER] Processing:", input);

        let finalId = input;
        let displayPartNo = "";

        // Detect JSON
        if (input.startsWith('{') && input.endsWith('}')) {
            try {
                const data = JSON.parse(input);
                if (data.id) {
                    displayPartNo = data.pn || '';
                    // Try exact match on ID or Value (Hash)
                    let matchHash = $(`${this.selectId} option[value="${data.id}"]`);
                    if (matchHash.length > 0) {
                        finalId = matchHash.val();
                    }
                }
            } catch (e) { console.error("JSON Parse Error", e); }
        }

        // Attempt Selection
        let option = $(`${this.selectId} option[value="${finalId}"]`);

        if (option.length > 0) {
            $(this.selectId).val(finalId).trigger('change');

            // Toast
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
            Toast.fire({
                icon: 'success',
                title: 'Product Selected',
                text: displayPartNo || option.text().split(' - ')[0]
            });

            setTimeout(() => $(this.qtyInputId).focus(), 300);
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Product Not Found',
                text: `ID "${finalId}" not found in list.`,
                timer: 2000,
                showConfirmButton: false
            });

            // Play error sound?
        }
    }
}
