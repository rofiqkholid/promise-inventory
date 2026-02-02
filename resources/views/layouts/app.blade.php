<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - PROMISE Inventory</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/image/favicon.ico') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/litepicker/dist/css/litepicker.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('assets/css/app.css?v=2') }}">

    <script>
        // On page load or when changing themes, best to add inline in `head` to avoid FOUC
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <style>
        [x-cloak] {
            display: none !important;
        }

        body {
            font-family: 'Outfit', sans-serif;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        /* Standardize SweetAlert Backdrop - Modals Only */
        /* Targets the backdrop when a modal is shown */
        .swal2-container.swal2-backdrop-show,
        .swal2-container.swal2-center.swal2-backdrop-show {
            background: rgba(15, 23, 42, 0.5) !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
        }

        /* Explicitly remove backdrop for Toasts */
        body.swal2-toast-shown .swal2-container.swal2-backdrop-show,
        .swal2-container.swal2-top-end.swal2-backdrop-show {
            background: transparent !important;
        }

        .swal2-popup {
            border-radius: 0.75rem !important;
            /* rounded-xl */
        }

        #filter_date_range {
            padding-left: 2.5rem !important;
            /* = pl-10 */
        }
    </style>

    @yield('css')
    @stack('styles')
</head>

<body class="bg-background dark:bg-gray-900 text-slate-800 dark:text-gray-200 antialiased">
    <div x-data="{ 
            sidebarReady: false,
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
        x-init="sidebarExpanded = localStorage.getItem('sidebarExpanded') !== 'false'"
        class="flex h-screen overflow-hidden">

        <aside class="fixed inset-y-0 left-0 z-50 bg-sidebar dark:bg-gray-800 border-r border-slate-200 dark:border-gray-700 transition-all duration-300 ease-in-out w-64"
            :class="{
                'w-64': sidebarExpanded && window.innerWidth >= 1024, 
                'w-20': !sidebarExpanded && window.innerWidth >= 1024,
                'translate-x-0 w-64': sidebarMobileOpen,
                '-translate-x-full lg:translate-x-0': !sidebarMobileOpen
            }">
            @include('layouts.sidebar')
        </aside>

        <div class="flex flex-col flex-1 min-w-0 overflow-hidden transition-all duration-300 lg:pl-64"
            :class="{
                 'lg:pl-64': sidebarExpanded,
                 'lg:pl-20': !sidebarExpanded
             }">

            @include('layouts.header')

            <main class="bg-gray-100 dark:bg-gray-900 flex-1 overflow-y-auto p-4 md:p-6 lg:p-8 scroll-smooth">
                <div class="flex flex-col min-h-full">
                    @include('components.toast')

                    @yield('content')

                    @include('layouts.footer')
                </div>
            </main>
        </div>

        <div x-show="sidebarMobileOpen" @click="sidebarMobileOpen = false" x-transition.opacity
            class="fixed inset-0 bg-slate-900/50 z-40 lg:hidden" style="display: none;"></div>

        <!-- Global Stock Alert Modal -->
        @include('components.stock-alert-modal')
    </div>

    <div id="toast-container" class="fixed top-5 right-5 z-[100] flex flex-col gap-2"></div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/litepicker/dist/litepicker.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @yield('js')
    @stack('scripts')
</body>

</html>