<!-- Toast Container (Standard HTML) -->
<div id="toast-container-html" class="fixed top-4 right-4 z-50 space-y-2 max-w-sm">
</div>

<script>
    /**
     * Global Toast Function (SweetAlert2)
     */
    window.toast = function(icon, title, text) {
        const isDark = document.documentElement.classList.contains('dark');
        const theme = isDark ? {
            bg: 'rgba(30, 41, 59, 0.95)',
            fg: '#E5E7EB',
            border: 'rgba(71, 85, 105, 0.5)',
            progress: 'rgba(255,255,255,.9)',
            icon: { success: '#22c55e', error: '#ef4444', warning: '#f59e0b', info: '#3b82f6' }
        } : {
            bg: 'rgba(255, 255, 255, 0.98)',
            fg: '#0f172a',
            border: 'rgba(226, 232, 240, 1)',
            progress: 'rgba(15,23,42,.8)',
            icon: { success: '#16a34a', error: '#dc2626', warning: '#d97706', info: '#2563eb' }
        };
        
        Swal.fire({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            icon,
            title,
            text,
            iconColor: theme.icon[icon] || theme.icon.info,
            background: theme.bg,
            color: theme.fg,
            customClass: { 
                popup: 'swal2-slim-toast border',
                title: 'swal2-slim-title',
                htmlContainer: 'swal2-slim-text'
            },
            didOpen: (t) => {
                const bar = t.querySelector('.swal2-timer-progress-bar');
                if (bar) bar.style.background = theme.progress;
                const popup = t.querySelector('.swal2-popup');
                if (popup) popup.style.borderColor = theme.border;
            }
        });
    }

    /**
     * Backward Compatibility Alias
     */
    window.showToast = function(message, type = 'success') {
        window.toast(type, type.charAt(0).toUpperCase() + type.slice(1), message);
    }
</script>

<style>
    /* Refined Premium Toast - The Perfect Middle Ground */
    .swal2-popup.swal2-slim-toast {
        padding: 0.875rem 1.125rem !important;
        align-items: center !important;
        width: auto !important;
        min-width: 320px;
        max-width: 420px;
        border-radius: 0.375rem !important;
    }
    
    .swal2-slim-toast .swal2-icon {
        margin: 0 0.875rem 0 0 !important;
        grid-column: 1 !important;
        grid-row: 1 / 3 !important;
        zoom: 0.7;
    }
    
    .swal2-slim-toast .swal2-title {
        margin: 0 !important;
        padding: 0 !important;
        font-size: 0.9375rem !important;
        font-weight: 700 !important;
        text-align: left !important;
        grid-column: 2 !important;
        line-height: 1.2 !important;
    }
    
    .swal2-slim-toast .swal2-html-container.swal2-slim-text {
        margin: 0.1875rem 0 0 0 !important;
        padding: 0 !important;
        font-size: 0.8rem !important;
        font-weight: 500 !important;
        text-align: left !important;
        grid-column: 2 !important;
        line-height: 1.35 !important;
        opacity: 0.85;
    }

    .dark .swal2-popup.swal2-slim-toast {
        box-shadow: 0 15px 20px -5px rgba(0, 0, 0, 0.35), 0 8px 8px -4px rgba(0, 0, 0, 0.25);
    }
</style>
