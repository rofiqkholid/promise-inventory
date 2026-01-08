<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - PROMISE Inventory</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/image/favicon.ico') }}">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    {{-- Icons --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    {{-- Tailwind CSS (CDN) --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                    },
                    colors: {
                        primary: '#3b82f6', 
                        secondary: '#64748b',
                        sidebar: '#ffffff',
                        background: '#f1f5f9', 
                    }
                }
            }
        }
    </script>
    
    {{-- Third Party CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.tailwindcss.min.css">

    <style>
        body { font-family: 'Outfit', sans-serif; }
        
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #a8a8a8; }
        
        .select2-container--bootstrap-5 .select2-selection {
            border-color: #e2e8f0;
            padding: 0.5rem;
            height: auto;
        }
        /* Sinkronisasi Select2 dengan Tailwind Input */
    .select2-container--default .select2-selection--single {
        height: 42px !important; /* Sama dengan h-[42px] atau p-2.5 */
        background-color: rgb(249 250 251) !important; /* bg-gray-50 */
        border: 1px solid rgb(209 213 219) !important; /* border-gray-300 */
        border-radius: 0.5rem !important; /* rounded-lg */
        display: flex;
        align-items: center;
        transition: border-color 0.15s ease-in-out;
    }

    /* Dark Mode support */
    .dark .select2-container--default .select2-selection--single {
        background-color: rgb(55 65 81) !important; /* dark:bg-gray-700 */
        border-color: rgb(75 85 99) !important; /* dark:border-gray-600 */
    }

    /* Text & Placeholder */
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 42px !important;
        padding-left: 0.75rem !important;
        color: rgb(17 24 39) !important; /* text-gray-900 */
    }

    .dark .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: rgb(243 244 246) !important; /* text-gray-100 */
    }

    /* Focus State */
    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #3b82f6 !important; /* ring-blue-500 */
        outline: none !important;
        box-shadow: 0 0 0 1px #3b82f6;
    }

    /* Dropdown Panel */
    .select2-dropdown {
        background-color: white !important;
        border-color: rgb(209 213 219) !important;
        border-radius: 0.5rem !important;
        z-index: 9999;
    }

    .dark .select2-dropdown {
        background-color: rgb(55 65 81) !important;
        border-color: rgb(75 85 99) !important;
        color: white !important;
    }

    /* Search Field inside Dropdown */
    .select2-container--default .select2-search--dropdown .select2-search__field {
        border-radius: 0.375rem !important;
        background-color: rgb(249 250 251) !important;
        border: 1px solid rgb(209 213 219) !important;
    }

    .dark .select2-container--default .select2-search--dropdown .select2-search__field {
        background-color: rgb(31 41 55) !important;
        border-color: rgb(75 85 99) !important;
        color: white !important;
    }

    /* Hilangkan panah default select2 yang kecil, ganti style jika perlu */
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px !important;
        right: 8px !important;
    }
    /* Menyamakan rounded pada item list saat di-hover atau dipilih */
.select2-results__options {
    border-radius: 0.5rem !important; /* rounded-lg */
}

.select2-results__option:first-child {
    border-top-left-radius: 0.5rem !important;
    border-top-right-radius: 0.5rem !important;
}

.select2-results__option:last-child {
    border-bottom-left-radius: 0.5rem !important;
    border-bottom-right-radius: 0.5rem !important;
}

/* Mengatur warna highlight agar lebih modern (opsional) */
.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background-color: #3b82f6 !important; /* Blue-500 Tailwind */
    color: white !important;
}
    </style>

    @yield('css')
</head>

<body class="bg-background text-slate-800 antialiased">

    {{-- Layout Container --}}
    <div x-data="{ 
            sidebarExpanded: localStorage.getItem('sidebarExpanded') !== 'false',
            sidebarMobileOpen: false,
            toggleSidebar() {
                if (window.innerWidth >= 1024) {
                    this.sidebarExpanded = !this.sidebarExpanded;
                    localStorage.setItem('sidebarExpanded', this.sidebarExpanded);
                } else {
                    this.sidebarMobileOpen = !this.sidebarMobileOpen;
                }
            }
        }" 
        class="flex h-screen overflow-hidden">
        
        {{-- Sidebar --}}
        <aside class="fixed inset-y-0 left-0 z-50 bg-sidebar border-r border-slate-200 transition-all duration-300 ease-in-out"
            :class="{
                'w-64': sidebarExpanded && window.innerWidth >= 1024, 
                'w-20': !sidebarExpanded && window.innerWidth >= 1024,
                'translate-x-0 w-64': sidebarMobileOpen,
                '-translate-x-full lg:translate-x-0': !sidebarMobileOpen
            }">
            @include('layouts.sidebar')
        </aside>

        {{-- Main Content --}}
        <div class="flex flex-col flex-1 min-w-0 overflow-hidden transition-all duration-300"
             :class="{
                 'lg:pl-64': sidebarExpanded,
                 'lg:pl-20': !sidebarExpanded
             }">
            
            {{-- Header --}}
            @include('layouts.header')

            {{-- Page Content --}}
            <main class="flex-1 overflow-y-auto p-6 scroll-smooth">
                @include('components.toast') 
                @yield('content')
            </main>

            {{-- Footer (Optional, usually simple text) --}}
            <footer class="bg-white border-t border-slate-200 p-4 text-center text-xs text-slate-500">
                &copy; {{ date('Y') }} Promise Inventory. All rights reserved.
            </footer>
        </div>

        {{-- Mobile Overlay --}}
        <div x-show="sidebarMobileOpen" @click="sidebarMobileOpen = false" x-transition.opacity 
            class="fixed inset-0 bg-slate-900 bg-opacity-50 z-40 lg:hidden" style="display: none;"></div>
    </div>

    {{-- Toast Container --}}
    <div id="toast-container" class="fixed top-5 right-5 z-[100] flex flex-col gap-2"></div>

    {{-- Scripts --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    {{-- AlpineJS --}}
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.tailwindcss.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Simple Toast Function
        window.showToast = function(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            
            const colors = {
                success: 'bg-green-50 border-green-200 text-green-700',
                error: 'bg-red-50 border-red-200 text-red-700',
                warning: 'bg-yellow-50 border-yellow-200 text-yellow-700',
                info: 'bg-blue-50 border-blue-200 text-blue-700'
            };
            const icons = {
                success: '<i class="fa-solid fa-circle-check"></i>',
                error: '<i class="fa-solid fa-circle-xmark"></i>',
                warning: '<i class="fa-solid fa-triangle-exclamation"></i>',
                info: '<i class="fa-solid fa-circle-info"></i>'
            }

            const styles = colors[type] || colors.success;
            const icon = icons[type] || icons.success;

            toast.className = `flex items-center gap-3 w-80 p-4 border rounded-lg shadow-md transition-all duration-300 transform translate-x-full opacity-0 ${styles}`;
            toast.innerHTML = `
                <span class="text-xl">${icon}</span>
                <span class="text-sm font-medium flex-1">${message}</span>
                <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            `;

            container.appendChild(toast);

            // Animate In
            requestAnimationFrame(() => toast.classList.remove('translate-x-full', 'opacity-0'));

            // Auto Remove
            setTimeout(() => {
                toast.classList.add('translate-x-full', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
    </script>
    
    @yield('js')
    @stack('scripts')
</body>
</html>
