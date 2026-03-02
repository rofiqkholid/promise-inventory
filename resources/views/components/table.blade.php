<div class="bg-white dark:bg-gray-800 rounded-md border border-gray-200 dark:border-gray-700 overflow-hidden relative">
    <div class="p-4 bg-white dark:bg-gray-800">
        <table {{ $attributes->merge(['class' => 'custom-table w-full text-left border-collapse']) }}>
            {{ $slot }}
        </table>
    </div>
</div>

@once
<script>
    /**
     * Global DataTable Helper
     */
    window.defaultDataTable = function (selector, userConfig = {}) {
        if (typeof $ === 'undefined') return console.error('jQuery required');

        const defaults = {
            processing: true,
            serverSide: false,
            scrollCollapse: true,
            autoWidth: false,
            ordering: true,
            order: [[0, 'desc']],
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            dom: "<'flex flex-col sm:flex-row justify-between items-center mb-6 gap-4'<'flex items-center gap-3'l B><'w-full sm:w-auto'f>>r<'overflow-x-auto w-full relative border border-gray-200 dark:border-gray-700 rounded-md't><'flex flex-col md:flex-row justify-between items-center mt-6 gap-4 text-gray-500'i p>",
            buttons: [
                { extend: 'excel', text: '<i class="fa-solid fa-file-excel"></i>', className: 'px-3 py-1.5 bg-green-50 text-green-600 border border-green-100 rounded-lg hover:bg-green-100 dark:bg-green-900/30 dark:text-green-400 dark:border-green-900/50 transition-colors' },
                { extend: 'pdf', text: '<i class="fa-solid fa-file-pdf"></i>', className: 'px-3 py-1.5 bg-red-50 text-red-600 border border-red-100 rounded-lg hover:bg-red-100 dark:bg-red-900/30 dark:text-red-400 dark:border-red-900/50 transition-colors' },
                { extend: 'print', text: '<i class="fa-solid fa-print"></i>', className: 'px-3 py-1.5 bg-gray-50 text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-100 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 transition-colors' }
            ],
            language: {
                processing: '<div class="inline-flex items-center"><span class="animate-spin mr-2"></span> Loading...</div>',
                search: "_INPUT_",
                searchPlaceholder: "Search records...",
                paginate: { previous: '<i class="fa-solid fa-chevron-left"></i>', next: '<i class="fa-solid fa-chevron-right"></i>' },
                emptyTable: `
                    <div class="dt-empty-state">
                        <i class="fa-solid fa-folder-open"></i>
                        <h4 class="dt-empty-title">No Data Available</h4>
                        <p class="dt-empty-desc">It looks like there are no records matching your criteria. Try adding a new record or expanding your search filters.</p>
                    </div>
                `,
                zeroRecords: `
                    <div class="dt-empty-state">
                        <i class="fa-solid fa-folder-open"></i>
                        <h4 class="dt-empty-title">No Data Available</h4>
                        <p class="dt-empty-desc">It looks like there are no records matching your criteria. Try adding a new record or expanding your search filters.</p>
                    </div>
                `,
                lengthMenu: "_MENU_"
            }
        };

        const options = $.extend(true, {}, defaults, userConfig);
        
        // CSRF Token Injection
        if (options.ajax && typeof options.ajax === 'object') {
            options.ajax.headers = { ...options.ajax.headers, 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') };
        }

        const dt = $(selector).DataTable(options);

        // Fix Pagination Styles on Draw
        $(selector).on('draw.dt', function() {
            $(this).closest('.dataTables_wrapper').find('.paginate_button').addClass('inline-flex items-center justify-center w-8 h-8 rounded-md mx-0.5 transition-colors');
        });

        return dt;
    };
</script>
@endonce

