/**
 * Global DataTable Helper
 * Standardizes all DataTables in the application
 */
window.defaultDataTable = function (selector, userConfig = {}) {
    if (typeof $ === 'undefined') {
        console.error('jQuery is not loaded. defaultDataTable requires jQuery.');
        return;
    }

    // Default configuration (styling, buttons, DOM, layout)
    const defaults = {
        processing: true,
        serverSide: false, // Default to client-side unless specified
        scrollCollapse: true,
        autoWidth: false,
        ordering: true,
        order: [[0, 'desc']], // Default order if not provided
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],

        // Standardized DOM Layout (Elegant & Spacious)
        dom: "<'flex flex-col sm:flex-row justify-between items-center mb-6 gap-4'<'flex items-center gap-3'l B><'w-full sm:w-auto'f>>r<'overflow-x-auto w-full relative border border-gray-200 dark:border-gray-700 rounded-md't><'flex flex-col md:flex-row justify-between items-center mt-6 gap-4 text-gray-500'i p>",

        // Default Buttons
        buttons: [
            {
                extend: 'excel',
                text: '<i class="fa-solid fa-file-excel"></i>',
                className: 'px-3 py-1.5 bg-green-50 text-green-600 border border-green-100 rounded-lg hover:bg-green-100 transition-colors',
                titleAttr: 'Export to Excel'
            },
            {
                extend: 'pdf',
                text: '<i class="fa-solid fa-file-pdf"></i>',
                className: 'px-3 py-1.5 bg-red-50 text-red-600 border border-red-100 rounded-lg hover:bg-red-100 transition-colors',
                titleAttr: 'Export to PDF'
            },
            {
                extend: 'csv',
                text: '<i class="fa-solid fa-file-csv"></i>',
                className: 'px-3 py-1.5 bg-blue-50 text-blue-600 border border-blue-100 rounded-lg hover:bg-blue-100 transition-colors',
                titleAttr: 'Export to CSV'
            },
            {
                extend: 'print',
                text: '<i class="fa-solid fa-print"></i>',
                className: 'px-3 py-1.5 bg-gray-50 text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-100 transition-colors',
                titleAttr: 'Print Table'
            }
        ],

        // Styling Language
        language: {
            processing: '<div class="inline-flex items-center"><span class="spinner-modern"></span> Loading...</div>',
            search: "_INPUT_",
            searchPlaceholder: "Search records...",
            paginate: {
                previous: '<i class="fa-solid fa-chevron-left"></i>',
                next: '<i class="fa-solid fa-chevron-right"></i>'
            },
            emptyTable: '<div class="dt-empty-state"><i class="fa-solid fa-folder-open mb-4 text-slate-300 dark:text-slate-600"></i><p class="dt-empty-title">Kept you waiting?</p><p class="dt-empty-desc">No data found in this category.</p></div>',
            zeroRecords: '<div class="dt-empty-state"><i class="fa-solid fa-magnifying-glass mb-4 text-slate-300 dark:text-slate-600"></i><p class="dt-empty-title">No matches found</p><p class="dt-empty-desc">Try adjusting your search filters.</p></div>',
            lengthMenu: "_MENU_"
        },

        // Default Error Handling for AJAX - Defined separately to avoid auto-triggering AJAX
    };

    const defaultAjaxError = function (xhr, error, thrown) {
        if (window.showToast) {
            window.showToast('Something went wrong while fetching data. Please try again.', 'error');
        }
        console.error('DataTable Error:', xhr, error, thrown);
    };

    // Callback Wrappers to preserve both default and user functionality
    const originalDrawCallback = userConfig.drawCallback;

    // Handle AJAX specifically if it's just a URL string or explicit override
    let finalAjax = undefined;

    if (userConfig.ajax) {
        if (typeof userConfig.ajax === 'string') {
            finalAjax = { url: userConfig.ajax, error: defaultAjaxError };
        } else {
            // If user provides object, ensure headers and error handler are present
            finalAjax = {
                error: defaultAjaxError, // default error handler
                ...userConfig.ajax, // overwrite with user settings
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    ...(userConfig.ajax.headers || {})
                }
            };
        }
    } else if (userConfig.url) {
        // Fallback support for legacy 'url' property key
        finalAjax = { url: userConfig.url, error: defaultAjaxError };
    }

    // Merge everything into final options
    const options = $.extend(true, {}, defaults, userConfig);

    // Only apply ajax if we actually constructed it
    if (finalAjax) {
        options.ajax = finalAjax;
    }

    // Override drawCallback to include pagination and length menu fixes
    options.drawCallback = function (settings) {
        const $wrapper = $(selector).closest('.dataTables_wrapper');

        // Fix pagination styling
        $wrapper.find('.dataTables_paginate .paginate_button').each(function () {
            $(this).addClass('flex items-center justify-center !w-[38px] !h-[38px] !p-0 !min-w-[38px] rounded-lg transition-all');
        });

        // Custom Alpine-styled Length Menu
        const $nativeSelect = $wrapper.find('.dataTables_length select');
        if ($nativeSelect.length && !$nativeSelect.parent().hasClass('dt-custom-length-wrapper')) {
            const currentValue = $nativeSelect.val();
            const options = [];
            $nativeSelect.find('option').each(function () {
                options.push({ value: $(this).val(), text: $(this).text() });
            });

            // Wrap native select and hide it
            $nativeSelect.addClass('hidden').wrap('<div class="dt-custom-length-wrapper flex items-center gap-2"></div>');

            // Inject Alpine Component
            const alpineHtml = `
                <div x-data="{ 
                    open: false, 
                    value: '${currentValue}',
                    options: ${JSON.stringify(options).replace(/"/g, '&quot;')},
                    select(val) {
                        this.value = val
                        this.open = false
                        const $sel = $(this.$el).closest('.dt-custom-length-wrapper').find('select')
                        $sel.val(val).trigger('change')
                    }
                }" class="relative inline-block text-left">
                    <button @click="open = !open" @click.away="open = false" type="button" 
                        class="flex items-center justify-between gap-2 px-3 py-1.5 min-w-[70px] bg-slate-50 border border-slate-200 rounded-lg text-sm font-semibold text-slate-700 hover:bg-white hover:border-blue-400 focus:outline-none focus:ring-4 focus:ring-blue-100 transition-all dark:bg-slate-800/50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                        id="menu-button" aria-expanded="true" aria-haspopup="true">
                        <span x-text="value"></span>
                        <i class="fa-solid fa-chevron-down text-[10px] transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                    </button>

                    <div x-show="open" 
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="transform opacity-0 scale-95"
                        x-transition:enter-end="transform opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="transform opacity-100 scale-100"
                        x-transition:leave-end="transform opacity-0 scale-95"
                        class="absolute left-0 z-50 mt-2 w-20 origin-top-left rounded-xl bg-white shadow-xl ring-1 ring-black ring-opacity-5 focus:outline-none dark:bg-slate-800 dark:ring-slate-700 overflow-hidden backdrop-blur-sm" 
                        role="menu" aria-orientation="vertical" aria-labelledby="menu-button" tabindex="-1">
                        <div class="py-1" role="none">
                            <template x-for="opt in options" :key="opt.value">
                                <button @click="select(opt.value)" 
                                    class="w-full text-left px-4 py-2 text-sm transition-colors hover:bg-blue-50 hover:text-blue-600 dark:hover:bg-slate-700 dark:hover:text-blue-400"
                                    :class="value == opt.value ? 'bg-blue-50 text-blue-700 font-bold dark:bg-blue-900/30' : 'text-slate-600 dark:text-slate-400'"
                                    role="menuitem" tabindex="-1">
                                    <span x-text="opt.text"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            `;
            $nativeSelect.after(alpineHtml);
        }


        // Call user's original callback
        if (originalDrawCallback) {
            originalDrawCallback.call(this, settings);
        }
    };

    // Auto-detect ID selector if hash is missing
    if (typeof selector === 'string' && !selector.startsWith('#') && !selector.startsWith('.') && !selector.includes(' ')) {
        selector = '#' + selector;
    }

    const $table = $(selector);
    if ($table.length === 0) {
        console.warn(`defaultDataTable: Selector "${selector}" not found in DOM.`);
        return;
    }

    // Initialize the DataTable
    const dt = $table.DataTable(options);

    // Call user's initComplete if provided
    options.initComplete = function (settings, json) {
        if (userConfig.initComplete) userConfig.initComplete.call(this, settings, json);
    };

    return dt;
};

// Global Select2 Initialization for a premium look everywhere
$(function () {
    const initSelect2 = (context = document) => {
        $(context).find('select.select2:not(.no-select2)').each(function () {
            // Skip if already initialized or if part of a DataTable wrapper (handled by defaultDataTable)
            if ($(this).hasClass('select2-hidden-accessible') || $(this).closest('.dataTables_length').length) return;

            const $this = $(this);
            const options = {
                width: '100%',
                dropdownAutoWidth: true,
                selectionCssClass: $this.hasClass('select2-sm') ? 'select2-sm' : '',
                placeholder: $this.data('placeholder') || $this.find('option[value=""]').text() || 'Select an option',
                allowClear: $this.data('allow-clear') === true || $this.data('allow-clear') === 'true',
            };

            // Auto-detect modal parent to fix visibility/focus issues
            const $modal = $this.closest('.fixed, .absolute, [role="dialog"]');
            if ($modal.length) {
                options.dropdownParent = $modal;
            }

            $this.select2(options);
        });
    };

    // Initial load
    initSelect2();

    // Re-init for dynamically added elements (like Alpine x-if or AJAX content)
    $(document).on('select2:reinit', (e, container) => {
        initSelect2(container || document);
    });

    // Fix for Select2 focus ring and search field focus in modals
    $(document).on('select2:open', (e) => {
        const openedEvent = e;
        setTimeout(() => {
            const searchField = document.querySelector('.select2-search__field');
            if (searchField) searchField.focus();
        }, 10);
    });
});

/**
 * Simple Toast Function
 */
window.showToast = function (message, type = 'success') {
    const container = document.getElementById('toast-container');
    if (!container) return;

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
    };

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
};
