@extends('layouts.app')

@section('title', 'Tool Information & Settings Catalog')

@section('content')
<div class="text-gray-900 dark:text-gray-100 flex flex-col">
    <!-- Header Area -->
    <div class="sm:flex sm:items-center sm:justify-between mb-6 border-b border-slate-100 dark:border-gray-800">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl tracking-tighter">Tool Information Center</h2>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-medium">Quick search, physical drawings, real-time stock levels, and machining parameter settings.</p>
        </div>
    </div>

    <!-- Main Dynamic Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 flex-1">
        
        <!-- Left Side: Catalog Search Panel (3 Cols - Narrower & Sleeker) -->
        <div class="lg:col-span-3 flex flex-col bg-white dark:bg-gray-900 border border-slate-100 dark:border-gray-800 rounded-xs shadow-xs">
            <!-- Search Inputs Header -->
            <div class="p-4 bg-slate-50/50 dark:bg-gray-800/40 border-b border-slate-100 dark:border-gray-800 flex flex-col gap-3">
                <div>
                    <label class="block mb-1.5 text-[10px] font-semibold text-slate-500 dark:text-gray-400 uppercase tracking-wider">Search Tool</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                            <i class="fa-solid fa-magnifying-glass text-xs"></i>
                        </span>
                        <input type="text" id="toolSearchInput" placeholder="Type name, brand..." class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full pr-2 py-2.5 transition-all" style="padding-left: 2.5rem !important;">
                    </div>
                </div>

                <div>
                    <label class="block mb-1.5 text-[10px] font-semibold text-slate-500 dark:text-gray-400 uppercase tracking-wider">Filter Category</label>
                    <select id="categoryFilter" class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs rounded-xs focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 transition-all">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }} ({{ strtoupper($category->moving_type) }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- List Results Container -->
            <div class="flex-1 overflow-y-auto custom-scrollbar p-2" id="searchResultsList">
                <div class="flex flex-col items-center justify-center h-full text-center p-6 text-slate-400" id="searchPlaceholder">
                    <i class="fa-solid fa-toolbox text-4xl mb-3 text-slate-200 dark:text-gray-800"></i>
                    <p class="text-xs font-semibold">Start typing above to search the tool catalog.</p>
                </div>
            </div>
        </div>

        <!-- Right Side: Details View Area (9 Cols - More Spacious) -->
        <div class="lg:col-span-9 flex flex-col h-[calc(100vh-265px)] min-h-[400px] overflow-hidden">
            
            <!-- Empty State Detail View -->
            <div id="detailEmptyState" class="flex-1 flex flex-col items-center justify-center bg-white dark:bg-gray-900 border border-slate-100 dark:border-gray-800 rounded-xs shadow-xs p-10 text-center">
                <div class="w-20 h-20 bg-slate-50 dark:bg-gray-800/40 rounded-full flex items-center justify-center mb-4 text-slate-300 dark:text-gray-700">
                    <i class="fa-solid fa-search-plus text-3xl"></i>
                </div>
                <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 uppercase tracking-widest">Select a Tool</h3>
                <p class="text-xs text-gray-400 max-w-sm mt-2">Click on any tool from the catalog list on the left to reveal its complete technical specifications, inventory stocks, and machining settings parameters.</p>
            </div>

            <!-- Loading State Detail View (Skeleton / Shimmer) -->
            <div id="detailLoadingState" class="hidden flex-1 flex flex-col gap-4 overflow-hidden pr-1 animate-pulse">
                <!-- Main Header Details Skeleton -->
                <div class="bg-white dark:bg-gray-900 border border-slate-100 dark:border-gray-800 rounded-xs p-4 shadow-xs">
                    <div class="flex flex-col gap-2.5">
                        <div class="flex gap-2">
                            <div class="h-4 w-16 bg-slate-200 dark:bg-gray-800 rounded-xs"></div>
                            <div class="h-4 w-12 bg-slate-200 dark:bg-gray-800 rounded-xs"></div>
                        </div>
                        <div class="h-6 w-1/3 bg-slate-200 dark:bg-gray-800 rounded-xs"></div>
                        <div class="h-4 w-1/2 bg-slate-200 dark:bg-gray-800 rounded-xs"></div>
                    </div>
                </div>

                <!-- Specs, Drawing & Stock Grid Skeleton -->
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <!-- Sketch Drawing Skeleton (4 Cols) -->
                    <div class="md:col-span-4 bg-white dark:bg-gray-900 border border-slate-100 dark:border-gray-800 rounded-xs p-5 shadow-xs flex flex-col justify-between h-[300px]">
                        <div class="h-4 w-1/2 bg-slate-200 dark:bg-gray-800 rounded-xs mb-4"></div>
                        <div class="flex-1 bg-slate-100 dark:bg-gray-800/40 rounded-xs flex items-center justify-center">
                            <i class="fa-solid fa-image text-slate-200 dark:text-gray-700 text-3xl"></i>
                        </div>
                        <div class="h-4 w-3/4 bg-slate-200 dark:bg-gray-800 rounded-xs mt-4"></div>
                    </div>

                    <!-- Specifications List Skeleton (4 Cols) -->
                    <div class="md:col-span-4 bg-white dark:bg-gray-900 border border-slate-100 dark:border-gray-800 rounded-xs p-5 shadow-xs h-[300px] flex flex-col justify-between">
                        <div>
                            <div class="h-4 w-1/2 bg-slate-200 dark:bg-gray-800 rounded-xs mb-6"></div>
                            <div class="space-y-4.5">
                                <div class="flex justify-between"><div class="h-3.5 w-1/3 bg-slate-200 dark:bg-gray-800 rounded-xs"></div><div class="h-3.5 w-1/4 bg-slate-200 dark:bg-gray-800 rounded-xs"></div></div>
                                <div class="flex justify-between"><div class="h-3.5 w-1/3 bg-slate-200 dark:bg-gray-800 rounded-xs"></div><div class="h-3.5 w-1/4 bg-slate-200 dark:bg-gray-800 rounded-xs"></div></div>
                                <div class="flex justify-between"><div class="h-3.5 w-1/3 bg-slate-200 dark:bg-gray-800 rounded-xs"></div><div class="h-3.5 w-1/4 bg-slate-200 dark:bg-gray-800 rounded-xs"></div></div>
                                <div class="flex justify-between"><div class="h-3.5 w-1/3 bg-slate-200 dark:bg-gray-800 rounded-xs"></div><div class="h-3.5 w-1/4 bg-slate-200 dark:bg-gray-800 rounded-xs"></div></div>
                            </div>
                        </div>
                    </div>

                    <!-- Warehouse Stock Skeleton (4 Cols) -->
                    <div class="md:col-span-4 bg-white dark:bg-gray-900 border border-slate-100 dark:border-gray-800 rounded-xs p-5 shadow-xs h-[300px] flex flex-col justify-between">
                        <div>
                            <div class="h-4 w-1/2 bg-slate-200 dark:bg-gray-800 rounded-xs mb-6"></div>
                            <div class="h-16 bg-slate-100 dark:bg-gray-800/40 rounded-xs flex items-center justify-center mb-6"></div>
                            <div class="space-y-3">
                                <div class="h-8 bg-slate-50 dark:bg-gray-800/20 rounded-xs"></div>
                                <div class="h-8 bg-slate-50 dark:bg-gray-800/20 rounded-xs"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Full Details Card Container (Hidden initially) -->
            <div id="detailContent" class="hidden flex-1 flex flex-col gap-4 overflow-y-auto custom-scrollbar pr-1">
                
                <!-- Main Header Details with Premium Card Background -->
                <div class="bg-white dark:bg-gray-900 border border-slate-100 dark:border-gray-800 rounded-xs p-4 shadow-xs relative">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 relative z-10">
                        <div>
                            <div class="flex items-center gap-2 mb-1.5">
                                <span id="dtCategoryBadge" class="px-2 py-0.5 bg-primary-50 dark:bg-primary-950/40 text-primary-600 dark:text-primary-400 border border-primary-100 dark:border-primary-900 text-[9px] font-bold uppercase tracking-wider rounded-xs">Category</span>
                                <span id="dtMovingBadge" class="px-2 py-0.5 bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-gray-300 border border-slate-200 dark:border-gray-700 text-[9px] font-bold uppercase tracking-wider rounded-xs">Fast</span>
                            </div>
                            <h3 id="dtToolName" class="text-xl font-bold text-slate-800 dark:text-white tracking-tight">Tool Name</h3>
                            <p class="text-xs text-slate-500 dark:text-gray-400 mt-1">Brand: <span id="dtBrand" class="font-semibold text-slate-700 dark:text-gray-200">-</span> | Spec Code: <span id="dtSpecCode" class="font-semibold text-slate-700 dark:text-gray-200">-</span></p>
                        </div>
                    </div>
                </div>

                <!-- Specs, Drawing & Stock Grid -->
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    
                    <!-- Sketch Drawing (4 Cols) -->
                    <div class="md:col-span-4 bg-white dark:bg-gray-900 border border-slate-100 dark:border-gray-800 rounded-xs p-5 shadow-xs flex flex-col justify-between">
                        <div>
                            <h4 class="text-xs font-bold text-slate-700 dark:text-slate-200 uppercase tracking-wider mb-4 pb-2 border-b border-slate-50 dark:border-gray-800">
                                Sketch & Drawing
                            </h4>
                            <div class="h-44 flex items-center justify-center border border-slate-100 dark:border-gray-800 rounded-xs bg-slate-50 dark:bg-slate-800/40 overflow-hidden relative group cursor-zoom-in" id="dtSketchContainer">
                                <i id="dtSketchPlaceholder" class="fa-solid fa-image text-3xl text-slate-200 dark:text-gray-700"></i>
                                <img id="dtSketchImg" class="w-full h-full object-contain group-hover:scale-110 transition-transform duration-300" style="display: none;">
                            </div>
                        </div>
                        <p id="dtSketchName" class="text-[9px] text-center text-slate-400 mt-2 truncate">-</p>
                    </div>

                    <!-- Specifications List (4 Cols) -->
                    <div class="md:col-span-4 bg-white dark:bg-gray-900 border border-slate-100 dark:border-gray-800 rounded-xs p-5 shadow-xs flex flex-col justify-between">
                        <div>
                            <h4 class="text-xs font-bold text-slate-700 dark:text-slate-200 uppercase tracking-wider mb-4 pb-2 border-b border-slate-50 dark:border-gray-800 flex items-center gap-1.5">
                                <i class="fa-solid fa-list-check text-primary-500"></i> Specifications
                            </h4>
                            <div class="space-y-2.5 text-xs">
                                <div class="flex justify-between items-center py-1 border-b border-slate-50/50 dark:border-gray-800/40">
                                    <span class="text-slate-400">Dimension (ø / T / Range)</span>
                                    <span id="dtDimension" class="font-bold text-slate-700 dark:text-gray-200">-</span>
                                </div>
                                <div class="flex justify-between items-center py-1 border-b border-slate-50/50 dark:border-gray-800/40">
                                    <span class="text-slate-400">Length (L)</span>
                                    <span id="dtLength" class="font-bold text-slate-700 dark:text-gray-200">-</span>
                                </div>
                                <div class="flex justify-between items-center py-1 border-b border-slate-50/50 dark:border-gray-800/40">
                                    <span class="text-slate-400">Material Type</span>
                                    <span id="dtMaterialType" class="font-bold text-slate-700 dark:text-gray-200">-</span>
                                </div>
                                <div class="flex justify-between items-center py-1 border-b border-slate-50/50 dark:border-gray-800/40">
                                    <span class="text-slate-400">HRC</span>
                                    <span id="dtHrc" class="font-bold text-slate-700 dark:text-gray-200">-</span>
                                </div>
                                <div class="flex justify-between items-center py-1 border-b border-slate-50/50 dark:border-gray-800/40">
                                    <span class="text-slate-400">UOM</span>
                                    <span id="dtUom" class="font-bold text-slate-700 dark:text-gray-200">-</span>
                                </div>
                                <div class="flex justify-between items-center py-1 border-b border-slate-50/50 dark:border-gray-800/40">
                                    <span class="text-slate-400">Safety Min/Max</span>
                                    <span class="font-bold text-slate-700 dark:text-gray-200"><span id="dtQtyMin">0</span> / <span id="dtQtyMax">0</span></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Inventory Stock (4 Cols) -->
                    <div class="md:col-span-4 bg-white dark:bg-gray-900 border border-slate-100 dark:border-gray-800 rounded-xs p-5 shadow-xs flex flex-col justify-between">
                        <div>
                            <h4 class="text-xs font-bold text-slate-700 dark:text-slate-200 uppercase tracking-wider mb-4 pb-2 border-b border-slate-50 dark:border-gray-800">
                                Warehouse Stock
                            </h4>
                            
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 rounded-xs bg-slate-50 dark:bg-gray-800 flex items-center justify-center text-slate-600 dark:text-gray-400 font-black" id="stockStatusIcon">
                                    <i class="fa-solid fa-warehouse"></i>
                                </div>
                                <div>
                                    <span class="text-[10px] text-slate-400 block font-medium uppercase tracking-wider">Total Quantity</span>
                                    <div class="flex items-baseline gap-1.5">
                                        <span id="dtTotalQty" class="text-2xl font-bold text-slate-800 dark:text-white">0</span>
                                        <span id="dtStockUom" class="text-[10px] text-slate-400 font-semibold">PCS</span>
                                    </div>
                                </div>
                                <div class="ml-auto">
                                    <span id="dtStockLegendBadge" class="px-2.5 py-1 bg-slate-100 text-slate-600 text-[9px] font-bold uppercase tracking-wider rounded-xs">Pending</span>
                                </div>
                            </div>

                            <!-- Location breakdowns -->
                            <div class="overflow-y-auto max-h-[140px] custom-scrollbar border border-slate-50 dark:border-gray-800 rounded-xs p-2">
                                <div class="space-y-1.5 text-[11px]" id="dtStockDetailsContainer">
                                    {{-- Dynamically populated stock breakdowns --}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Machining Settings parameters Table -->
                <div class="bg-white dark:bg-gray-900 border border-slate-100 dark:border-gray-800 rounded-xs p-6 shadow-xs">
                    <div class="mb-4">
                        <h4 class="text-xs font-bold text-slate-700 dark:text-slate-200 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="fa-solid fa-gears text-primary-500"></i> Machining Parameter Settings
                        </h4>
                        <p class="text-[10px] text-gray-500 dark:text-gray-400 mt-1">Recommended machining configuration per category of workpiece material.</p>
                    </div>

                    <div class="overflow-x-auto border border-slate-100 dark:border-gray-800 rounded-xs">
                        <table class="min-w-full divide-y divide-slate-100 dark:divide-gray-800 text-xs text-left" id="dtSettingsTable">
                            <thead class="bg-slate-50 dark:bg-slate-800/40 text-[9px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-wider">
                                <tr>
                                    <th scope="col" class="px-4 py-3 w-44">Material Category</th>
                                    <th scope="col" class="px-4 py-3 text-center w-28">Spindle Speed (n)<br><span class="text-[8px] font-normal lowercase">rev/min</span></th>
                                    <th scope="col" class="px-4 py-3 text-center w-28">Table Feed (Vf)<br><span class="text-[8px] font-normal lowercase">mm/min</span></th>
                                    <th scope="col" class="px-4 py-3 text-center w-28">Depth of Cut (ap)<br><span class="text-[8px] font-normal lowercase">mm</span></th>
                                    <th scope="col" class="px-4 py-3 text-center w-24">Step Over (%)</th>
                                    <th scope="col" class="px-3 py-3 text-center w-20">CNC Small<br><span class="text-[8px] font-normal uppercase">plant b</span></th>
                                    <th scope="col" class="px-3 py-3 text-center w-28">CNC Hartford<br><span class="text-[8px] font-normal uppercase">plant f</span></th>
                                    <th scope="col" class="px-4 py-3 text-center w-24">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-gray-800 bg-white dark:bg-gray-900" id="dtSettingsContainer">
                                {{-- settings parameters will be appended here --}}
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        const searchInput = $('#toolSearchInput');
        const categoryFilter = $('#categoryFilter');
        const resultsList = $('#searchResultsList');
        const placeholder = $('#searchPlaceholder');

        let debounceTimer;
        let selectedToolId = null;

        // Perform live AJAX search
        function performSearch() {
            const query = searchInput.val().trim();
            const categoryId = categoryFilter.val();

            if (query.length === 0 && !categoryId) {
                resultsList.html(placeholder);
                return;
            }

            resultsList.html(`
                <div class="flex flex-col items-center justify-center h-full text-center p-6 text-slate-400">
                    <i class="fa-solid fa-spinner fa-spin text-2xl mb-3 text-primary-500"></i>
                    <p class="text-xs">Searching tool catalog...</p>
                </div>
            `);

            $.ajax({
                url: "{{ route('inventory.tool.information.search') }}",
                type: 'GET',
                data: { q: query, category_id: categoryId },
                success: function(res) {
                    if (res.length === 0) {
                        resultsList.html(`
                            <div class="flex flex-col items-center justify-center h-full text-center p-6 text-slate-400">
                                <i class="fa-solid fa-magnifying-glass-minus text-3xl mb-3 text-slate-300"></i>
                                <p class="text-xs font-semibold">No tools found matching search criteria.</p>
                            </div>
                        `);
                        return;
                    }

                    let html = '<div class="space-y-1">';
                    res.forEach(item => {
                        const isActive = selectedToolId == item.id;
                        const cardClasses = isActive 
                            ? 'tool-search-card p-3 rounded-xs border border-primary-500 bg-blue-50/70 dark:bg-blue-950/30 ring-1 ring-primary-500 cursor-pointer transition-all flex items-center gap-3'
                            : 'tool-search-card p-3 rounded-xs border border-slate-100 dark:border-gray-800 hover:border-primary-100 hover:bg-primary-50/20 dark:hover:bg-primary-950/10 cursor-pointer transition-all flex items-center gap-3';
                        const titleClasses = isActive
                            ? 'text-xs font-bold text-primary-600 dark:text-primary-400 truncate'
                            : 'text-xs font-bold text-slate-700 dark:text-slate-200 truncate';

                        html += `
                            <div class="${cardClasses}" data-id="${item.id}">
                                <div class="w-10 h-10 rounded-xs border border-slate-100 dark:border-gray-800 bg-slate-50 dark:bg-gray-800 overflow-hidden flex-shrink-0 flex items-center justify-center">
                                    ${item.sketch_image ? `<img src="${item.sketch_image}" class="w-full h-full object-cover">` : `<i class="fa-solid fa-image text-[10px] text-slate-300"></i>`}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="${titleClasses}">${item.name}</h4>
                                    <p class="text-[10px] text-slate-400 truncate">Code: ${item.spec_code} | Brand: ${item.brand}</p>
                                    <div class="flex gap-1.5 mt-1.5">
                                        <span class="text-[8px] font-bold uppercase tracking-wider text-primary-500 bg-primary-50 dark:bg-primary-950/40 dark:text-primary-400 px-1 py-0.5 rounded-xs">${item.category_name}</span>
                                        <span class="text-[8px] font-bold uppercase tracking-wider text-slate-500 bg-slate-100 dark:bg-slate-800 dark:text-slate-300 px-1 py-0.5 rounded-xs">${item.moving_type}</span>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    html += '</div>';
                    resultsList.html(html);
                },
                error: function() {
                    resultsList.html(`
                        <div class="flex flex-col items-center justify-center h-full text-center p-6 text-red-400">
                            <i class="fa-solid fa-triangle-exclamation text-3xl mb-3"></i>
                            <p class="text-xs">Failed to search tools. Please try again.</p>
                        </div>
                    `);
                }
            });
        }

        // Debounced trigger
        searchInput.on('keyup', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(performSearch, 300);
        });

        categoryFilter.on('change', performSearch);

        // Load detailed tool info on click
        $(document).on('click', '.tool-search-card', function() {
            const toolId = $(this).data('id');
            selectedToolId = toolId;

            // Reset all search cards to default style
            $('.tool-search-card')
                .removeClass('border-primary-500 bg-primary-50/30 bg-blue-50/70 dark:bg-primary-950/20 dark:bg-blue-950/30 ring-1 ring-primary-500')
                .addClass('border-slate-100 dark:border-gray-800');
            $('.tool-search-card h4')
                .removeClass('text-primary-600 dark:text-primary-400')
                .addClass('text-slate-700 dark:text-slate-200');

            // Apply active visual styles to the clicked card
            $(this)
                .removeClass('border-slate-100 dark:border-gray-800')
                .addClass('border-primary-500 bg-blue-50/70 dark:bg-blue-950/30 ring-1 ring-primary-500');
            $(this).find('h4')
                .removeClass('text-slate-700 dark:text-slate-200')
                .addClass('text-primary-600 dark:text-primary-400');

            $('#detailEmptyState').addClass('hidden');
            $('#detailContent').addClass('hidden');
            $('#detailLoadingState').removeClass('hidden');

            $.ajax({
                url: `{{ url('inventory/tool/information') }}/${toolId}`,
                type: 'GET',
                success: function(data) {
                    const tool = data.tool;
                    const stock = data.stock;
                    const settings = data.settings;

                    // Header Info
                    $('#dtCategoryBadge').text(tool.category_name);
                    $('#dtMovingBadge').text(tool.moving_type.toUpperCase());
                    $('#dtToolName').text(tool.name);
                    $('#dtBrand').text(tool.brand);
                    $('#dtSpecCode').text(tool.spec_code);


                    // Set dimension value (only append ' mm' if it is purely numeric)
                    let dimVal = tool.dimension || '-';
                    if (dimVal !== '-' && !isNaN(dimVal)) {
                        dimVal += ' mm';
                    }
                    $('#dtDimension').text(dimVal);

                    $('#dtLength').text(tool.length !== '-' ? tool.length + ' mm' : '-');
                    $('#dtMaterialType').text(tool.material_type);
                    $('#dtHrc').text(tool.hrc);
                    $('#dtUom').text(tool.uom);
                    $('#dtQtyMin').text(tool.qty_min);
                    $('#dtQtyMax').text(tool.qty_max);

                    // Sketch Drawing Preview
                    if (tool.sketch_image) {
                        $('#dtSketchImg').attr('src', tool.sketch_image).show();
                        $('#dtSketchPlaceholder').hide();
                        $('#dtSketchName').text(tool.sketch_name);
                        $('#dtSketchContainer').off('click').on('click', function() {
                            window.previewImg(tool.sketch_image);
                        });
                    } else {
                        $('#dtSketchImg').hide();
                        $('#dtSketchPlaceholder').show();
                        $('#dtSketchName').text('No Drawing File');
                        $('#dtSketchContainer').off('click');
                    }

                    // Stock Info
                    $('#dtTotalQty').text(stock.total_qty);
                    $('#dtStockUom').text(tool.uom);

                    // Stock Legend logic:
                    // critical: qty < min
                    // warning: qty == min
                    // safe: qty >= min && qty <= max
                    // over: qty > max
                    const qty = stock.total_qty;
                    const min = tool.qty_min;
                    const max = tool.qty_max;
                    let legend = 'SAFE';
                    let legendClass = 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900';
                    let statusIconClass = 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400';

                    if (qty < min) {
                        legend = 'CRITICAL';
                        legendClass = 'bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 border border-red-100 dark:border-red-900';
                        statusIconClass = 'bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400';
                    } else if (qty === min) {
                        legend = 'WARNING';
                        legendClass = 'bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border border-amber-100 dark:border-amber-900';
                        statusIconClass = 'bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400';
                    } else if (qty > max) {
                        legend = 'OVER';
                        legendClass = 'bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400 border border-purple-100 dark:border-purple-900';
                        statusIconClass = 'bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400';
                    }

                    $('#dtStockLegendBadge').text(legend).attr('class', `px-2.5 py-1 text-[9px] font-bold uppercase tracking-wider rounded-xs ${legendClass}`);
                    $('#stockStatusIcon').attr('class', `w-10 h-10 rounded-xs flex items-center justify-center font-black ${statusIconClass}`);

                    // Stock Details breakdowns
                    let stockDetailsHtml = '';
                    if (stock.details.length === 0) {
                        stockDetailsHtml = '<div class="text-slate-400 text-center py-4">No physical stocks in locations.</div>';
                    } else {
                        stock.details.forEach(item => {
                            if (tool.moving_type === 'fast') {
                                stockDetailsHtml += `
                                    <div class="flex justify-between items-center py-1 border-b border-slate-50 dark:border-gray-800">
                                        <span class="text-slate-600 dark:text-gray-300 font-medium"><i class="fa-solid fa-location-dot text-slate-400 mr-1"></i> ${item.location}</span>
                                        <span class="font-bold text-slate-800 dark:text-white">${item.qty} ${tool.uom}</span>
                                    </div>
                                `;
                            } else {
                                stockDetailsHtml += `
                                    <div class="flex flex-col py-1.5 border-b border-slate-50 dark:border-gray-800">
                                        <div class="flex justify-between items-center">
                                            <span class="text-slate-700 dark:text-gray-200 font-bold">${item.id_number}</span>
                                            <span class="font-bold text-slate-800 dark:text-white">${item.qty} ${tool.uom}</span>
                                        </div>
                                        <div class="flex justify-between text-[9px] text-slate-400 mt-0.5">
                                            <span>Loc: ${item.location}</span>
                                            <span>Age: ${item.physical_rate} (${item.purchase_date})</span>
                                        </div>
                                    </div>
                                `;
                            }
                        });
                    }
                    $('#dtStockDetailsContainer').html(stockDetailsHtml);

                    // Machining parameters Settings Table
                    let settingsHtml = '';
                    if (settings.length === 0) {
                        settingsHtml = `
                            <tr>
                                <td colspan="8" class="px-4 py-6 text-center text-slate-400">
                                    <i class="fa-solid fa-screwdriver-wrench text-xl mb-2 block"></i>
                                    No machining parameter settings configured for this tool.
                                </td>
                            </tr>
                        `;
                    } else {
                        settings.forEach(item => {
                            const badgeSmall = item.cnc_small_plant_b 
                                ? '<span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400"><i class="fa-solid fa-circle-check"></i></span>'
                                : '<span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-slate-50 dark:bg-slate-800 text-slate-300"><i class="fa-solid fa-circle-minus"></i></span>';
                            const badgeHartford = item.cnc_big_hartford_plant_f
                                ? '<span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400"><i class="fa-solid fa-circle-check"></i></span>'
                                : '<span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-slate-50 dark:bg-slate-800 text-slate-300"><i class="fa-solid fa-circle-minus"></i></span>';
                            
                            const badgeStatus = item.status === 'USE'
                                ? '<span class="px-2 py-0.5 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900 text-[9px] font-bold uppercase rounded-xs">USE</span>'
                                : '<span class="px-2 py-0.5 bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-gray-700 text-[9px] font-bold uppercase rounded-xs">NOT USE</span>';

                            const stepOverDisplay = item.step_over 
                                ? (String(item.step_over).endsWith('%') ? item.step_over : item.step_over + '%') 
                                : '-';

                            settingsHtml += `
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-gray-800/30 transition-colors">
                                    <td class="px-4 py-3 font-bold text-slate-700 dark:text-gray-200">${item.material_category}</td>
                                    <td class="px-4 py-3 text-center text-slate-600 dark:text-gray-300 font-mono">${item.spindle_speed ? item.spindle_speed.toLocaleString() : '-'}</td>
                                    <td class="px-4 py-3 text-center text-slate-600 dark:text-gray-300 font-mono">${item.table_feed ? item.table_feed.toLocaleString() : '-'}</td>
                                    <td class="px-4 py-3 text-center text-slate-600 dark:text-gray-300 font-mono">${item.depth_of_cut ? item.depth_of_cut : '-'}</td>
                                    <td class="px-4 py-3 text-center text-slate-600 dark:text-gray-300 font-semibold">${stepOverDisplay}</td>
                                    <td class="px-3 py-3 text-center">${badgeSmall}</td>
                                    <td class="px-3 py-3 text-center">${badgeHartford}</td>
                                    <td class="px-4 py-3 text-center">${badgeStatus}</td>
                                </tr>
                            `;
                        });
                    }
                    $('#dtSettingsContainer').html(settingsHtml);

                    // Hide loading and show content
                    $('#detailLoadingState').addClass('hidden');
                    $('#detailContent').removeClass('hidden').addClass('animate-fade-in');
                },
                error: function() {
                    $('#detailLoadingState').addClass('hidden');
                    $('#detailEmptyState').removeClass('hidden');
                    toast('error', 'Error', 'Failed to load tool details.');
                }
            });
        });
    });
</script>

<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
        height: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 2px;
    }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #334155;
    }
</style>
@endpush
