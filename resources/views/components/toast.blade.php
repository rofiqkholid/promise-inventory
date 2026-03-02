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
            customClass: { popup: 'swal2-toast border' },
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
    /* Custom Swiper/Swal Overrides for Dark Mode */
    .dark .swal2-popup.swal2-toast {
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3), 0 4px 6px -2px rgba(0, 0, 0, 0.2);
    }
</style>
