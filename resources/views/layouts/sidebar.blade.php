<!-- Sidebar Content -->
<div class="flex flex-col h-full bg-white dark:bg-gray-800 text-slate-700 dark:text-gray-300">
    
    <!-- Logo -->
    <div class="flex items-center gap-3 p-4 border-b border-slate-200 dark:border-gray-700 h-16 transition-all duration-300"
         :class="sidebarExpanded ? 'justify-start' : 'justify-center'">
        <img src="{{ asset('assets/image/logo-promise.png') }}" alt="PROMISE" class="h-8 w-auto">
        <div x-show="sidebarExpanded" x-transition:enter="transition ease-out duration-200 delay-100" class="overflow-hidden whitespace-nowrap">
            <h1 class="text-sm font-bold text-slate-900 dark:text-white leading-tight">PROMISE</h1>
            <p class="text-[10px] text-slate-500 dark:text-gray-400 uppercase tracking-wider">Inventory</p>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 py-4 px-3 space-y-1 custom-scrollbar"
         :class="sidebarExpanded ? 'overflow-y-auto' : 'overflow-visible'">
        
        {{-- DASHBOARD --}}
        <a href="{{ route('dashboard') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-md transition-all duration-200 group relative
           {{ request()->routeIs('dashboard') ? 'bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 font-medium' : 'hover:bg-slate-50 dark:hover:bg-gray-700/50 hover:text-slate-900 dark:hover:text-white' }}"
           :class="!sidebarExpanded ? 'justify-center' : ''">
            <i class="fa-solid fa-chart-pie w-6 text-center text-lg {{ request()->routeIs('dashboard') ? 'text-blue-600 dark:text-blue-400' : 'text-slate-400 dark:text-gray-500 group-hover:text-slate-600 dark:group-hover:text-gray-300' }}"></i>
            <span x-show="sidebarExpanded" class="text-sm whitespace-nowrap">Dashboard</span>
            
            {{-- Tooltip for Minimized --}}
            <div x-show="!sidebarExpanded" class="absolute left-full top-2 ml-2 bg-slate-800 dark:bg-black text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity z-50 pointer-events-none whitespace-nowrap">
                Dashboard
            </div>
        </a>

        {{-- INVENTORY GROUP --}}
        <div x-data="{ open: {{ request()->routeIs('inventory.*') ? 'true' : 'false' }} }" class="pt-2">
            
            <button @click="if(!sidebarExpanded) { sidebarExpanded = true; open = true; } else { open = !open; }"
                class="w-full flex items-center justify-between px-3 py-2.5 rounded-md transition-all duration-200 group relative
                {{ request()->routeIs('inventory.*') ? 'bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 font-medium' : 'hover:bg-slate-50 dark:hover:bg-gray-700/50 hover:text-slate-900 dark:hover:text-white' }}"
                :class="!sidebarExpanded ? 'justify-center' : ''">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-box-archive w-6 text-center text-lg {{ request()->routeIs('inventory.*') ? 'text-blue-600 dark:text-blue-400' : 'text-slate-400 dark:text-gray-500 group-hover:text-slate-600 dark:group-hover:text-gray-300' }}"></i>
                    <span x-show="sidebarExpanded" class="text-sm whitespace-nowrap">Inventory</span>
                </div>
                <i x-show="sidebarExpanded" class="fa-solid fa-chevron-down text-xs transition-transform duration-200" :class="{'rotate-180': open}"></i>

                {{-- Tooltip for Minimized --}}
                <div x-show="!sidebarExpanded" class="absolute left-full top-2 ml-2 bg-slate-800 dark:bg-black text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity z-50 pointer-events-none whitespace-nowrap">
                    Inventory
                </div>
            </button>

            <div x-show="open && sidebarExpanded" x-cloak 
                 style="display: {{ request()->routeIs('inventory.*') ? 'block' : 'none' }};" 
                 class="pl-10 pr-2 space-y-1 mt-1 border-l-2 border-slate-100 dark:border-gray-700 ml-6">
                
                <a href="{{ route('inventory.master') }}" 
                   class="block py-2 px-3 rounded-md text-sm transition-colors whitespace-nowrap
                   {{ request()->routeIs('inventory.master*') ? 'text-blue-600 dark:text-blue-400 font-medium bg-blue-50/50 dark:bg-blue-900/40' : 'text-slate-500 dark:text-gray-400 hover:text-slate-800 dark:hover:text-gray-200' }}">
                   Master Data
                </a>

                <a href="{{ route('inventory.product') }}" 
                   class="block py-2 px-3 rounded-md text-sm transition-colors whitespace-nowrap
                   {{ request()->routeIs('inventory.product*') ? 'text-blue-600 dark:text-blue-400 font-medium bg-blue-50/50 dark:bg-blue-900/40' : 'text-slate-500 dark:text-gray-400 hover:text-slate-800 dark:hover:text-gray-200' }}">
                   Product
                </a>

                <a href="{{ route('inventory.transaction') }}" 
                   class="block py-2 px-3 rounded-md text-sm transition-colors whitespace-nowrap
                   {{ request()->routeIs('inventory.transaction*') ? 'text-blue-600 dark:text-blue-400 font-medium bg-blue-50/50 dark:bg-blue-900/40' : 'text-slate-500 dark:text-gray-400 hover:text-slate-800 dark:hover:text-gray-200' }}">
                   Transaction
                </a>

                <a href="{{ route('inventory.stockMonitoring') }}" 
                   class="block py-2 px-3 rounded-md text-sm transition-colors whitespace-nowrap
                   {{ request()->routeIs('inventory.stockMonitoring') || request()->routeIs('inventory.stockMonitoring.data') || request()->routeIs('inventory.stockMonitoring.printBalance') ? 'text-blue-600 dark:text-blue-400 font-medium bg-blue-50/50 dark:bg-blue-900/40' : 'text-slate-500 dark:text-gray-400 hover:text-slate-800 dark:hover:text-gray-200' }}">
                   Stock Monitoring
                </a>

                <a href="{{ route('inventory.sto.index') }}" 
                   class="block py-2 px-3 rounded-md text-sm transition-colors whitespace-nowrap
                   {{ request()->routeIs('inventory.sto.index') || request()->routeIs('inventory.sto.show') || request()->routeIs('inventory.sto.scan') || request()->routeIs('inventory.sto.saveCount') || request()->routeIs('inventory.sto.finalize') || request()->routeIs('inventory.sto.reopen') || request()->routeIs('inventory.sto.exportExcel') || request()->routeIs('inventory.sto.deleteDetail') ? 'text-blue-600 dark:text-blue-400 font-medium bg-blue-50/50 dark:bg-blue-900/40' : 'text-slate-500 dark:text-gray-400 hover:text-slate-800 dark:hover:text-gray-200' }}">
                   Stock Opname (STO)
                </a>

            </div>
        </div>

        {{-- TRANSACTION HISTORY --}}
         <a href="{{ route('transactionHistory') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-md transition-all duration-200 group relative
           {{ request()->routeIs('transactionHistory') ? 'bg-blue-50 text-blue-600 font-medium' : 'hover:bg-slate-50 hover:text-slate-900' }}"
           :class="!sidebarExpanded ? 'justify-center' : ''">
            <i class="fa-solid fa-arrow-right-arrow-left w-6 text-center text-lg {{ request()->routeIs('transactionHistory') ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-600' }}"></i>
            <span x-show="sidebarExpanded" class="text-sm whitespace-nowrap">Transaction History</span>
            
            {{-- Tooltip for Minimized --}}
            <div x-show="!sidebarExpanded" class="absolute left-full top-2 ml-2 bg-slate-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity z-50 pointer-events-none whitespace-nowrap">
                Transaction History
            </div>
        </a>

        {{-- REPORTS --}}
        <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-md transition-all duration-200 group relative hover:bg-slate-50 dark:hover:bg-gray-700 hover:text-slate-900 dark:hover:text-white"
            :class="!sidebarExpanded ? 'justify-center' : ''">
            <i class="fa-solid fa-file-invoice w-6 text-center text-lg text-slate-400 dark:text-gray-500 group-hover:text-slate-600 dark:group-hover:text-gray-300"></i>
            <span x-show="sidebarExpanded" class="text-sm">Reports</span>
             {{-- Tooltip for Minimized --}}
             <div x-show="!sidebarExpanded" class="absolute left-full top-2 ml-2 bg-slate-800 dark:bg-black text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity z-50 pointer-events-none whitespace-nowrap">
                Reports
            </div>
        </a>
        
    </nav>

    <!-- Footer Profile / Settings -->
    <div class="p-4 border-t border-slate-200 dark:border-gray-700">
        <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-md hover:bg-slate-50 dark:hover:bg-gray-700 transition-colors group relative"
            :class="!sidebarExpanded ? 'justify-center' : ''">
            <i class="fa-solid fa-gear w-6 text-center text-lg text-slate-400 dark:text-gray-500 group-hover:text-slate-600 dark:group-hover:text-gray-300"></i>
            <span x-show="sidebarExpanded" class="text-sm font-medium whitespace-nowrap text-slate-600 dark:text-gray-400 group-hover:text-slate-900 dark:group-hover:text-white">Settings</span>
             {{-- Tooltip for Minimized --}}
             <div x-show="!sidebarExpanded" class="absolute left-full top-2 ml-2 bg-slate-800 dark:bg-black text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity z-50 pointer-events-none whitespace-nowrap">
                Settings
            </div>
        </a>
    </div>
</div>
