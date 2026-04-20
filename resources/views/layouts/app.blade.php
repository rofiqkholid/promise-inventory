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

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/litepicker/dist/css/litepicker.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script>
        // Theme initialization
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }

        // Sidebar state initialization (Prevent Flickering)
        (function() {
            const isExpanded = localStorage.getItem('sidebarExpanded') !== 'false';
            document.documentElement.style.setProperty('--sidebar-width', isExpanded ? '16rem' : '5rem');
            document.documentElement.classList.toggle('sidebar-collapsed', !isExpanded);
        })();
    </script>

    @yield('css')
    @stack('styles')
</head>

<body class="bg-gray-100 dark:bg-gray-900 text-slate-800 dark:text-gray-200 antialiased">
    <div x-data="{ 
            sidebarExpanded: localStorage.getItem('sidebarExpanded') !== 'false',
            sidebarMobileOpen: false,
            toggleSidebar() {
                if (window.innerWidth >= 1024) {
                    this.sidebarExpanded = !this.sidebarExpanded;
                } else {
                    this.sidebarMobileOpen = !this.sidebarMobileOpen;
                }
            }
        }"
        x-init="
            $watch('sidebarExpanded', value => {
                localStorage.setItem('sidebarExpanded', value);
                document.documentElement.style.setProperty('--sidebar-width', value ? '16rem' : '5rem');
                document.documentElement.classList.toggle('sidebar-collapsed', !value);
            });
        "
        class="flex h-screen overflow-hidden">

        <aside class="fixed inset-y-0 left-0 z-50 bg-sidebar dark:bg-gray-800 border-r border-slate-200 dark:border-gray-700 transition-all duration-300 ease-in-out w-[var(--sidebar-width)]"
            :class="{
                'w-64': sidebarExpanded && window.innerWidth >= 1024, 
                'w-20': !sidebarExpanded && window.innerWidth >= 1024,
                'translate-x-0 w-64': sidebarMobileOpen,
                '-translate-x-full lg:translate-x-0': !sidebarMobileOpen
            }">
            @include('layouts.sidebar')
        </aside>

        <div class="flex flex-col flex-1 min-w-0 overflow-hidden transition-all duration-300 lg:pl-[var(--sidebar-width)]"
            :class="{
                 'lg:pl-64': sidebarExpanded,
                 'lg:pl-20': !sidebarExpanded
             }">

            @include('layouts.header')

            <main class="flex-1 overflow-y-auto scroll-smooth flex flex-col">
                <div class="flex-1 p-4 md:p-6 lg:p-8">
                    @include('components.toast')
                    @yield('content')
                </div>
                @include('layouts.footer')
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

    <script>
        /**
         * Global Select2 Initialization
         */
        $(function () {
            const initSelect2 = (context = document) => {
                $(context).find('select.select2:not(.no-select2)').each(function () {
                    if ($(this).hasClass('select2-hidden-accessible') || $(this).closest('.dataTables_length').length) return;

                    const $this = $(this);
                    const options = {
                        width: '100%',
                        dropdownAutoWidth: true,
                        selectionCssClass: $this.hasClass('select2-sm') ? 'select2-sm' : '',
                        placeholder: $this.data('placeholder') || $this.find('option[value=""]').text() || 'Select an option',
                        allowClear: $this.data('allow-clear') === true || $this.data('allow-clear') === 'true',
                    };

                    const $modal = $this.closest('.fixed, .absolute, [role="dialog"], .modal-container');
                    if ($modal.length) {
                        options.dropdownParent = $modal;
                    }

                    $this.select2(options);
                });
            };

            initSelect2();
            
            // Re-init for dynamic content
            $(document).on('select2:reinit', (e, container) => {
                initSelect2(container || document);
            });

            // Auto focus search field
            $(document).on('select2:open', () => {
                setTimeout(() => {
                    const searchField = document.querySelector('.select2-search__field');
                    if (searchField) searchField.focus();
                }, 10);
            });
        });
    </script>
</body>

</html>