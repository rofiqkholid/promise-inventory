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
        serverSide: true,
        scrollCollapse: true,
        autoWidth: false,
        ordering: true,
        order: [[0, 'desc']], // Default order if not provided
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],

        // Standardized DOM Layout
        dom: "<'flex flex-col md:flex-row justify-between items-center mb-6 gap-2 sm:gap-4 px-1 sm:px-2'<'flex items-center gap-2 sm:gap-4'l B> f>rt<'flex flex-col md:flex-row justify-between items-center mt-6 gap-4 px-2'i p>",

        // Default Buttons
        buttons: [
            {
                extend: 'excel',
                text: '<i class="fa-solid fa-file-excel"></i>',
                className: 'btn-export-icon btn-excel',
                titleAttr: 'Export to Excel'
            },
            {
                extend: 'pdf',
                text: '<i class="fa-solid fa-file-pdf"></i>',
                className: 'btn-export-icon btn-pdf',
                titleAttr: 'Export to PDF'
            },
            {
                extend: 'csv',
                text: '<i class="fa-solid fa-file-csv"></i>',
                className: 'btn-export-icon btn-csv',
                titleAttr: 'Export to CSV'
            },
            {
                extend: 'print',
                text: '<i class="fa-solid fa-print"></i>',
                className: 'btn-export-icon btn-print',
                titleAttr: 'Print Table'
            }
        ],

        // Styling Language
        language: {
            processing: '<div class="overlay-loader"><i class="fa-solid fa-spinner fa-spin text-blue-500 mr-2"></i> Processing...</div>',
            search: "_INPUT_",
            searchPlaceholder: "Search records...",
            lengthMenu: "Show _MENU_",
            paginate: {
                previous: '<i class="fa-solid fa-chevron-left"></i>',
                next: '<i class="fa-solid fa-chevron-right"></i>'
            }
        },

        // Default Error Handling for AJAX
        ajax: {
            error: function (xhr, error, thrown) {
                if (window.showToast) window.showToast('Failed to load data: ' + (xhr.responseJSON?.message || error), 'error');
                console.error('DataTable Error:', xhr, error, thrown);
            }
        }
    };

    // Callback Wrappers to preserve both default and user functionality
    const originalDrawCallback = userConfig.drawCallback;

    // Handle AJAX specifically if it's just a URL string or explicit override
    let finalAjax = defaults.ajax;
    if (userConfig.ajax) {
        if (typeof userConfig.ajax === 'string') {
            finalAjax = { ...defaults.ajax, url: userConfig.ajax };
        } else {
            // If user provides object, ensure headers are present
            finalAjax = {
                ...defaults.ajax, // keep default error handler
                ...userConfig.ajax, // overwrite with user settings
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    ...(userConfig.ajax.headers || {})
                }
            };
        }
    } else if (userConfig.url) {
        // Fallback support for legacy 'url' property key
        finalAjax = { ...defaults.ajax, url: userConfig.url };
    }

    // Merge everything into final options
    const options = $.extend(true, {}, defaults, userConfig);

    // Re-apply the smart AJAX configuration we built
    options.ajax = finalAjax;

    // Override drawCallback to include pagination fix
    options.drawCallback = function (settings) {
        // Fix pagination styling
        const $pagination = $(selector).closest('.dataTables_wrapper').find('.dataTables_paginate');
        $pagination.find('.paginate_button').each(function () {
            $(this).addClass('flex items-center justify-center !w-[38px] !h-[38px] !p-0 !min-w-[38px] rounded-lg transition-all');
        });

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

    return $table.DataTable(options);
};

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
