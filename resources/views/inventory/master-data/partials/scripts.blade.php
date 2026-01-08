<script>
    $(document).ready(function() {
        const csrf = $('meta[name="csrf-token"]').attr('content');
        let tables = {};
        let currentTab = 'coil-center';
        let deleteUrl = '';
        let deleteTable = '';

        // Tab Configuration
        const tabConfig = {
            'coil-center': {
                table: 'coilCenterTable',
                dataUrl: '{{ route("inventory.coilCenter.data") }}',
                apiBase: '/inventory/master/coil-center',
                columns: [{
                        data: null,
                        render: (d, t, r, meta) => meta.row + meta.settings._iDisplayStart + 1
                    },
                    {
                        data: 'code'
                    },
                    {
                        data: 'name'
                    },
                    {
                        data: 'address'
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        width: '100px',
                        render: (d, t, r) => `
                    <div class="flex items-center justify-center gap-2">
                        <button class="edit-btn h-8 w-8 inline-flex items-center justify-center text-blue-600 rounded-lg bg-blue-50 hover:bg-blue-100 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-blue-400 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800" data-id="${r.id}" data-type="coil-center" title="Edit">
                            <i class="fa-solid fa-pen-to-square text-sm"></i>
                        </button>
                        <button class="delete-btn h-8 w-8 inline-flex items-center justify-center text-red-600 rounded-lg bg-red-50 hover:bg-red-100 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-red-400 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800" data-id="${r.id}" data-type="coil-center" title="Delete">
                            <i class="fa-solid fa-trash-can text-sm"></i>
                        </button>
                    </div>`
                    }
                ]
            },
            'material-spec': {
                table: 'materialSpecTable',
                dataUrl: '{{ route("inventory.materialSpec.data") }}',
                apiBase: '/inventory/master/material-spec',
                columns: [{
                        data: null,
                        render: (d, t, r, meta) => meta.row + meta.settings._iDisplayStart + 1
                    },
                    {
                        data: 'spec_name'
                    },
                    {
                        data: 'coating_type'
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        width: '100px',
                        render: (d, t, r) => `
                    <div class="flex items-center justify-center gap-2">
                        <button class="edit-btn h-8 w-8 inline-flex items-center justify-center text-blue-600 rounded-lg bg-blue-50 hover:bg-blue-100 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-blue-400 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800" data-id="${r.id}" data-type="material-spec" title="Edit">
                            <i class="fa-solid fa-pen-to-square text-sm"></i>
                        </button>
                        <button class="delete-btn h-8 w-8 inline-flex items-center justify-center text-red-600 rounded-lg bg-red-50 hover:bg-red-100 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-red-400 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800" data-id="${r.id}" data-type="material-spec" title="Delete">
                            <i class="fa-solid fa-trash-can text-sm"></i>
                        </button>
                    </div>`
                    }
                ]
            },
            'unit': {
                table: 'unitTable',
                dataUrl: '{{ route("inventory.unit.data") }}',
                apiBase: '/inventory/master/unit',
                columns: [{
                        data: null,
                        render: (d, t, r, meta) => meta.row + meta.settings._iDisplayStart + 1
                    },
                    {
                        data: 'code'
                    },
                    {
                        data: 'name'
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        width: '100px',
                        render: (d, t, r) => `
                    <div class="flex items-center justify-center gap-2">
                        <button class="edit-btn h-8 w-8 inline-flex items-center justify-center text-blue-600 rounded-lg bg-blue-50 hover:bg-blue-100 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-blue-400 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800" data-id="${r.id}" data-type="unit" title="Edit">
                            <i class="fa-solid fa-pen-to-square text-sm"></i>
                        </button>
                        <button class="delete-btn h-8 w-8 inline-flex items-center justify-center text-red-600 rounded-lg bg-red-50 hover:bg-red-100 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-red-400 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800" data-id="${r.id}" data-type="unit" title="Delete">
                            <i class="fa-solid fa-trash-can text-sm"></i>
                        </button>
                    </div>`
                    }
                ]
            },
            'rank': {
                table: 'rankTable',
                dataUrl: '{{ route("inventory.rank.data") }}',
                apiBase: '/inventory/master/rank',
                columns: [{
                        data: null,
                        render: (d, t, r, meta) => meta.row + meta.settings._iDisplayStart + 1
                    },
                    {
                        data: 'code'
                    },
                    {
                        data: 'limit_value'
                    },
                    {
                        data: 'description'
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        width: '100px',
                        render: (d, t, r) => `
                    <div class="flex items-center justify-center gap-2">
                        <button class="edit-btn h-8 w-8 inline-flex items-center justify-center text-blue-600 rounded-lg bg-blue-50 hover:bg-blue-100 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-blue-400 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800" data-id="${r.id}" data-type="rank" title="Edit">
                            <i class="fa-solid fa-pen-to-square text-sm"></i>
                        </button>
                        <button class="delete-btn h-8 w-8 inline-flex items-center justify-center text-red-600 rounded-lg bg-red-50 hover:bg-red-100 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-red-400 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800" data-id="${r.id}" data-type="rank" title="Delete">
                            <i class="fa-solid fa-trash-can text-sm"></i>
                        </button>
                    </div>`
                    }
                ]
            },
            'sub-contractor': {
                table: 'subContractorTable',
                dataUrl: '{{ route("inventory.subContractor.data") }}',
                apiBase: '/inventory/master/sub-contractor',
                columns: [{
                        data: null,
                        render: (d, t, r, meta) => meta.row + meta.settings._iDisplayStart + 1
                    },
                    {
                        data: 'code'
                    },
                    {
                        data: 'name'
                    },
                    {
                        data: 'service_type'
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        width: '100px',
                        render: (d, t, r) => `
                    <div class="flex items-center justify-center gap-2">
                        <button class="edit-btn h-8 w-8 inline-flex items-center justify-center text-blue-600 rounded-lg bg-blue-50 hover:bg-blue-100 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-blue-400 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800" data-id="${r.id}" data-type="sub-contractor" title="Edit">
                            <i class="fa-solid fa-pen-to-square text-sm"></i>
                        </button>
                        <button class="delete-btn h-8 w-8 inline-flex items-center justify-center text-red-600 rounded-lg bg-red-50 hover:bg-red-100 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-red-400 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800" data-id="${r.id}" data-type="sub-contractor" title="Delete">
                            <i class="fa-solid fa-trash-can text-sm"></i>
                        </button>
                    </div>`
                    }
                ]
            },
            'transaction-category': {
                table: 'transactionCategoryTable',
                dataUrl: '{{ route("inventory.transactionCategory.data") }}',
                apiBase: '/inventory/master/transaction-category',
                columns: [{
                        data: null,
                        render: (d, t, r, meta) => meta.row + meta.settings._iDisplayStart + 1
                    },
                    {
                        data: 'code'
                    },
                    {
                        data: 'name'
                    },
                    {
                        data: 'effect',
                        render: (data) => {
                            return data == 1 ?
                                '<span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">IN (+)</span>' :
                                '<span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">OUT (-)</span>';
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        width: '100px',
                        render: (d, t, r) => `
                    <div class="flex items-center justify-center gap-2">
                        <button class="edit-btn h-8 w-8 inline-flex items-center justify-center text-blue-600 rounded-lg bg-blue-50 hover:bg-blue-100 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-blue-400 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800" data-id="${r.id}" data-type="transaction-category" title="Edit">
                            <i class="fa-solid fa-pen-to-square text-sm"></i>
                        </button>
                        <button class="delete-btn h-8 w-8 inline-flex items-center justify-center text-red-600 rounded-lg bg-red-50 hover:bg-red-100 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-red-400 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800" data-id="${r.id}" data-type="transaction-category" title="Delete">
                            <i class="fa-solid fa-trash-can text-sm"></i>
                        </button>
                    </div>`
                    }
                ]
            },
            'pic': {
                table: 'picTable',
                dataUrl: '{{ route("inventory.pic.data") }}',
                apiBase: '/inventory/master/pic',
                columns: [{
                        data: null,
                        render: (d, t, r, meta) => meta.row + meta.settings._iDisplayStart + 1
                    },
                    {
                        data: 'name'
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        width: '100px',
                        render: (d, t, r) => `
                    <div class="flex items-center justify-center gap-2">
                        <button class="edit-btn h-8 w-8 inline-flex items-center justify-center text-blue-600 rounded-lg bg-blue-50 hover:bg-blue-100 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-blue-400 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800" data-id="${r.id}" data-type="pic" title="Edit">
                            <i class="fa-solid fa-pen-to-square text-sm"></i>
                        </button>
                        <button class="delete-btn h-8 w-8 inline-flex items-center justify-center text-red-600 rounded-lg bg-red-50 hover:bg-red-100 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-red-400 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800" data-id="${r.id}" data-type="pic" title="Delete">
                            <i class="fa-solid fa-trash-can text-sm"></i>
                        </button>
                    </div>`
                    }
                ]
            }
        };

        // Lazy initialization for DataTables (only init when tab is shown)
        let initializedTabs = {};

        function initTable(tabName) {
            const config = tabConfig[tabName];
            if (!config || initializedTabs[tabName]) return;

            tables[config.table] = window.defaultDataTable(`#${config.table}`, {
                url: config.dataUrl,
                columns: config.columns,
                order: [[1, 'asc']]
            });

            initializedTabs[tabName] = true;
        }

        // Listener untuk transisi sidebar
        // 1. Deteksi klik pada tombol hamburger menu Anda
        $(document).on('click', 'button[\\@click*="toggleSidebar"]', function() {
            // Beri waktu 300ms agar animasi transisi sidebar Tailwind selesai dulu
            setTimeout(function() {
                if ($.fn.dataTable) {
                    $.fn.dataTable.tables({
                        visible: true,
                        api: true
                    }).columns.adjust();
                }
            }, 300);
        });

        // 2. Tetap simpan Resize Listener (untuk jaga-jaga jika layar di-resize)
        $(window).on('resize', function() {
            if ($.fn.dataTable) {
                $.fn.dataTable.tables({
                    visible: true,
                    api: true
                }).columns.adjust();
            }
        });

        // Update tab visual/ARIA state
        function updateActiveTabVisual(tabName) {
            const $btn = $(`.tab-button[data-tab="${tabName}"]`);
            $('.tab-button').removeClass('active').attr('aria-selected', 'false').attr('tabindex', '-1');
            $btn.addClass('active').attr('aria-selected', 'true').attr('tabindex', '0');

            // Move highlight
            if ($btn.length) {
                const $highlight = $('#tab-highlight');
                $highlight.show().css({
                    width: $btn.outerWidth() + 'px',
                    height: $btn.outerHeight() + 'px',
                    left: $btn[0].offsetLeft + 'px',
                    top: $btn[0].offsetTop + 'px'
                });
            }
        }

        // Tab Switching
        function switchTab(tabName) {
            $('.tab-content').addClass('hidden');
            $(`#tab-${tabName}`).removeClass('hidden');

            // Ensure active visuals and accessibility attributes are in sync
            updateActiveTabVisual(tabName);

            currentTab = tabName;
            const config = tabConfig[tabName];

            if (!config) return;

            if (!initializedTabs[tabName]) {
                // Initialize the table the first time the tab is shown (lazy load)
                initTable(tabName);
            } else if (tables[config.table]) {
                // Ensure we fetch fresh data when switching back to a tab
                tables[config.table].ajax.reload(null, false);
            }

            // Adjust columns after a small delay so layout is correct when tab becomes visible
            setTimeout(() => {
                if (tables[config.table]) tables[config.table].columns.adjust();
            }, 50);
        }

        $('.tab-button').on('click', function() {
            const tab = $(this).data('tab');
            switchTab(tab);
            history.pushState(null, '', `?tab=${tab}`);
        });

        // Initialize first tab
        const urlParams = new URLSearchParams(window.location.search);
        const initialTab = urlParams.get('tab') || 'coil-center';
        switchTab(initialTab);

        // Toast Notifications
        function toast(icon, title, text) {
            const isDark = document.documentElement.classList.contains('dark');
            const theme = isDark ? {
                bg: 'rgba(30, 41, 59, 0.95)',
                fg: '#E5E7EB',
                border: 'rgba(71, 85, 105, 0.5)',
                progress: 'rgba(255,255,255,.9)',
                icon: {
                    success: '#22c55e',
                    error: '#ef4444'
                }
            } : {
                bg: 'rgba(255, 255, 255, 0.98)',
                fg: '#0f172a',
                border: 'rgba(226, 232, 240, 1)',
                progress: 'rgba(15,23,42,.8)',
                icon: {
                    success: '#16a34a',
                    error: '#dc2626'
                }
            };
            Swal.fire({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2600,
                timerProgressBar: true,
                icon,
                title,
                text,
                iconColor: theme.icon[icon],
                background: theme.bg,
                color: theme.fg,
                customClass: {
                    popup: 'swal2-toast border'
                },
                didOpen: (t) => {
                    const bar = t.querySelector('.swal2-timer-progress-bar');
                    if (bar) bar.style.background = theme.progress;
                    const popup = t.querySelector('.swal2-popup');
                    if (popup) popup.style.borderColor = theme.border;
                }
            });
        }

        // Modal Handling
        function showModal(id) {
            $('.modal-container').addClass('hidden'); // Hide all modals first
            $(`#${id}`).removeClass('hidden');
        }

        function hideModal(id) {
            $(`#${id}`).addClass('hidden');
        }

        function hideAllModals() {
            $('.modal-container').addClass('hidden');
        }
        $('.close-modal').on('click', function() {
            $(this).closest('.modal-container').addClass('hidden');
        });

        // Add Button
        $('.add-button').on('click', function() {
            const type = $(this).data('target');
            $(`#modal-${type}-add form`)[0].reset();
            $(`#modal-${type}-add .error-msg`).addClass('hidden');
            showModal(`modal-${type}-add`);
        });

        // Form Submit
        $('.modal-form').on('submit', function(e) {
            e.preventDefault();
            const $form = $(this);
            const action = $form.data('action') || $form.attr('action');
            const tableName = $form.data('table');
            const formData = new FormData(this);

            $.ajax({
                url: action,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf
                },
                data: formData,
                processData: false,
                contentType: false,
                success: (data) => {
                    if (data.success) {
                        if (tables[tableName]) {
                            tables[tableName].ajax.reload();
                        } else {
                            // If table hasn't been initialized yet, initialize it and then reload
                            const tabName = Object.keys(tabConfig).find(k => tabConfig[k].table === tableName);
                            if (tabName) {
                                initTable(tabName);
                                setTimeout(() => {
                                    if (tables[tableName]) tables[tableName].ajax.reload();
                                }, 300);
                            }
                        }

                        $form.closest('.modal-container').addClass('hidden');
                        toast('success', 'Success', data.message);
                    }
                },
                error: (xhr) => {
                    const errors = xhr.responseJSON?.errors;
                    if (errors) {
                        Object.keys(errors).forEach(key => {
                            const $input = $form.find(`[name="${key}"]`);
                            $input.next('.error-msg').text(errors[key][0]).removeClass('hidden');
                        });
                    }
                    toast('error', 'Error', xhr.responseJSON?.message || 'Operation failed');
                }
            });
        });

        // Edit Button
        $(document).on('click', '.edit-btn', function() {
            const id = $(this).data('id');
            const type = $(this).data('type');
            const config = tabConfig[type];

            $.get(`${config.apiBase}/${id}`, (data) => {
                const $modal = $(`#modal-${type}-edit`);
                $modal.find('.error-msg').addClass('hidden');
                Object.keys(data).forEach(key => {
                    $modal.find(`[name="${key}"]`).val(data[key]);
                });
                $modal.find('form').attr('action', `${config.apiBase}/${id}`).data('table', config.table);
                showModal(`modal-${type}-edit`);
            });
        });

        // Delete Button
        $(document).on('click', '.delete-btn', function() {
            const id = $(this).data('id');
            const type = $(this).data('type');
            const config = tabConfig[type];
            deleteUrl = `${config.apiBase}/${id}`;
            deleteTable = config.table;
            showModal('modal-delete');
        });

        $('#confirmDelete').on('click', function() {
            $.ajax({
                url: deleteUrl,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrf
                },
                success: (data) => {
                    if (data.success) {
                        if (tables[deleteTable]) {
                            tables[deleteTable].ajax.reload();
                        } else {
                            const tabName = Object.keys(tabConfig).find(k => tabConfig[k].table === deleteTable);
                            if (tabName) initTable(tabName);
                        }

                        hideModal('modal-delete');
                        toast('success', 'Success', data.message);
                    }
                },
                error: (xhr) => {
                    toast('error', 'Error', xhr.responseJSON?.message || 'Delete failed');
                }
            });
        });
    });
</script>