<!-- Toast Container -->
<div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2 max-w-sm">
</div>

<style>
    .toast-success {
        @apply bg-green-50 border border-green-200 text-green-800;
    }

    .toast-error {
        @apply bg-red-50 border border-red-200 text-red-800;
    }

    .toast-warning {
        @apply bg-yellow-50 border border-yellow-200 text-yellow-800;
    }

    .toast-info {
        @apply bg-blue-50 border border-blue-200 text-blue-800;
    }

    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    .animate-slide-in {
        animation: slideIn 0.3s ease-out;
    }
</style>
