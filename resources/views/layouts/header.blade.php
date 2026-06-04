<!-- Header -->
<header class="bg-white dark:bg-gray-800 border-b border-slate-200 dark:border-gray-700 sticky top-0 z-30 h-16 flex-shrink-0">
    <div class="h-full px-6 flex items-center justify-between">
        
        <!-- Left Side -->
        <div class="flex items-center gap-4">
            <button @click="toggleSidebar()" class="w-10 h-10 flex items-center justify-center text-slate-500 dark:text-gray-400 hover:bg-slate-100 dark:hover:bg-gray-700 rounded-xs transition-colors">
                <i class="fa-solid fa-bars text-lg"></i>
            </button>
            
            <!-- Breadcrumb / Title -->
            <div class="hidden md:block">
                <h2 class="text-xl font-bold text-slate-800 dark:text-white tracking-wide">@yield('page_title', 'Inventory')</h2>
            </div>
        </div>

        <!-- Right Side -->
        <div class="flex items-center gap-4">

            <!-- Notifications -->
            <button 
                @click="$dispatch('open-stock-alert')"
                class="relative w-10 h-10 flex items-center justify-center text-slate-400 dark:text-gray-400 hover:text-slate-600 dark:hover:text-gray-200 hover:bg-slate-100 dark:hover:bg-gray-700 rounded-xs transition-colors"
                title="Stock Alerts">
                <i class="fa-regular fa-bell text-xl"></i>
                @if(isset($stockAlerts) && count($stockAlerts) > 0)
                <span class="absolute top-2 right-2 flex h-4 w-4">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-4 w-4 bg-rose-500 text-[10px] font-bold text-white items-center justify-center">{{ count($stockAlerts) }}</span>
                </span>
                @endif
            </button>
            
            <!-- Theme Toggle -->
            <button x-data="{ 
                        darkMode: localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches),
                        toggleTheme() {
                            this.darkMode = !this.darkMode;
                            if (this.darkMode) {
                                document.documentElement.classList.add('dark');
                                localStorage.setItem('theme', 'dark');
                            } else {
                                document.documentElement.classList.remove('dark');
                                localStorage.setItem('theme', 'light');
                            }
                        }
                    }" 
                    @click="toggleTheme()" 
                    class="w-10 h-10 flex items-center justify-center text-slate-400 dark:text-gray-400 hover:text-slate-600 dark:hover:text-gray-200 hover:bg-slate-100 dark:hover:bg-gray-700 rounded-xs transition-colors"
                    title="Toggle Dark Mode">
                <i class="fa-solid fa-sun text-xl" x-show="!darkMode"></i>
                <i class="fa-solid fa-moon text-xl" x-show="darkMode" style="display: none;"></i>
            </button>

            <!-- Apps Menu -->
            <div x-data="{ appsDropdownOpen: false }" class="relative ml-1 sm:ml-2 flex-shrink-0">
                <button @click="appsDropdownOpen = !appsDropdownOpen"
                    class="flex items-center justify-center w-10 h-10 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200 focus:outline-none text-gray-500 dark:text-gray-400" title="Apps Menu">
                    <i class="fa-solid fa-grip text-xl"></i>
                </button>

                <!-- Desktop Apps Dropdown -->
                <div x-show="appsDropdownOpen"
                    @click.away="appsDropdownOpen = false"
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="transform opacity-0 scale-95"
                    x-transition:enter-end="transform opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="transform opacity-100 scale-100"
                    x-transition:leave-end="transform opacity-0 scale-95"
                    class="hidden sm:block absolute right-0 mt-2 w-64 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-slate-100 dark:border-gray-700 p-3 z-50 origin-top-right"
                    style="display: none;">
                    
                    <div class="grid grid-cols-3 gap-1">
                        <a href="{{ env('APP_DRAWING_URL') }}"
                            class="flex flex-col items-center justify-center p-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 group text-center">
                            <div class="w-9 h-9 rounded-full flex items-center justify-center bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400 mb-1 group-hover:scale-105 transition-transform shadow-sm">
                                <i class="fa-solid fa-pen-ruler text-sm"></i>
                            </div>
                            <span class="text-[0.65rem] font-semibold text-gray-700 dark:text-gray-300 leading-tight">Drawing</span>
                        </a>

                        <a href="{{ env('APP_INVENTORY_URL') }}"
                            class="flex flex-col items-center justify-center p-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 group text-center">
                            <div class="w-9 h-9 rounded-full flex items-center justify-center bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400 mb-1 group-hover:scale-105 transition-transform shadow-sm">
                                <i class="fa-solid fa-boxes-stacked text-sm"></i>
                            </div>
                            <span class="text-[0.65rem] font-semibold text-gray-700 dark:text-gray-300 leading-tight">Inventory</span>
                        </a>

                        <a href="{{ env('APP_NPC_URL') }}"
                            class="flex flex-col items-center justify-center p-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 group text-center">
                            <div class="w-9 h-9 rounded-full flex items-center justify-center bg-purple-50 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400 mb-1 group-hover:scale-105 transition-transform shadow-sm">
                                <i class="fa-solid fa-users-gear text-sm"></i>
                            </div>
                            <span class="text-[0.65rem] font-semibold text-gray-700 dark:text-gray-300 leading-tight">NPC</span>
                        </a>

                        <a href="{{ env('APP_ALL_DASHBOARD_URL') }}"
                            class="flex flex-col items-center justify-center p-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 group text-center">
                            <div class="w-9 h-9 rounded-full flex items-center justify-center bg-teal-50 text-teal-600 dark:bg-teal-900/30 dark:text-teal-400 mb-1 group-hover:scale-105 transition-transform shadow-sm">
                                <i class="fa-solid fa-chart-pie text-sm"></i>
                            </div>
                            <span class="text-[0.65rem] font-semibold text-gray-700 dark:text-gray-300 leading-tight">All Dashboard</span>
                        </a>

                        <a href="{{ env('APP_MNG_URL') }}"
                            class="flex flex-col items-center justify-center p-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 group text-center">
                            <div class="w-9 h-9 rounded-full flex items-center justify-center bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400 mb-1 group-hover:scale-105 transition-transform shadow-sm">
                                <i class="fa-solid fa-briefcase text-sm"></i>
                            </div>
                            <span class="text-[0.65rem] font-semibold text-gray-700 dark:text-gray-300 leading-tight">Management</span>
                        </a>
                    </div>
                </div>

                <!-- Mobile Apps Drawer (Slide-over from Right) -->
                <div x-show="appsDropdownOpen" 
                     class="sm:hidden fixed inset-0 z-50" 
                     style="display: none;">
                    <!-- Backdrop -->
                    <div x-show="appsDropdownOpen"
                         x-transition:enter="transition-opacity ease-out duration-300"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         x-transition:leave="transition-opacity ease-in duration-200"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         @click="appsDropdownOpen = false"
                         class="fixed inset-0 bg-black/40"></div>

                    <!-- Drawer Content Panel -->
                    <div x-show="appsDropdownOpen"
                         x-transition:enter="transition-transform ease-out duration-300"
                         x-transition:enter-start="translate-x-full"
                         x-transition:enter-end="translate-x-0"
                         x-transition:leave="transition-transform ease-in duration-200"
                         x-transition:leave-start="translate-x-0"
                         x-transition:leave-end="translate-x-full"
                         class="fixed right-0 top-0 bottom-0 w-64 bg-white dark:bg-gray-800 p-4 shadow-2xl flex flex-col z-50">
                         
                        <!-- Drawer Header -->
                        <div class="flex items-center justify-between pb-4 mb-4 border-b border-gray-300 dark:border-gray-700">
                            <span class="text-sm font-bold text-gray-800 dark:text-white tracking-wider">Select App</span>
                            <button @click="appsDropdownOpen = false" class="p-1 rounded-full text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none">
                                <i class="fa-solid fa-xmark text-lg"></i>
                            </button>
                        </div>

                        <!-- Apps List (Vertical Stack) -->
                        <div class="flex flex-col gap-2.5">
                            <a href="{{ env('APP_DRAWING_URL') }}"
                                class="flex items-center justify-between p-3 rounded-none bg-white dark:bg-gray-800 border border-gray-200/80 dark:border-gray-700 hover:border-indigo-500/50 hover:bg-indigo-50/10 dark:hover:bg-indigo-950/10 transition-all duration-200 group">
                                <div class="flex items-center">
                                    <div class="w-9 h-9 rounded-none flex items-center justify-center bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 mr-4 flex-shrink-0 group-hover:scale-105 transition-transform border border-indigo-100 dark:border-indigo-900/20">
                                        <i class="fa-solid fa-pen-ruler text-sm"></i>
                                    </div>
                                    <span class="text-xs font-semibold text-gray-700 dark:text-gray-300 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">Drawing</span>
                                </div>
                                <i class="fa-solid fa-chevron-right text-[10px] text-gray-400 dark:text-gray-500 group-hover:translate-x-0.5 transition-transform mr-1"></i>
                            </a>

                            <a href="{{ env('APP_INVENTORY_URL') }}"
                                class="flex items-center justify-between p-3 rounded-none bg-white dark:bg-gray-800 border border-gray-200/80 dark:border-gray-700 hover:border-blue-500/50 hover:bg-blue-50/10 dark:hover:bg-blue-950/10 transition-all duration-200 group">
                                <div class="flex items-center">
                                    <div class="w-9 h-9 rounded-none flex items-center justify-center bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 mr-4 flex-shrink-0 group-hover:scale-105 transition-transform border border-blue-100 dark:border-blue-900/20">
                                        <i class="fa-solid fa-boxes-stacked text-sm"></i>
                                    </div>
                                    <span class="text-xs font-semibold text-gray-700 dark:text-gray-300 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Inventory</span>
                                </div>
                                <i class="fa-solid fa-chevron-right text-[10px] text-gray-400 dark:text-gray-500 group-hover:translate-x-0.5 transition-transform mr-1"></i>
                            </a>

                            <a href="{{ env('APP_NPC_URL') }}"
                                class="flex items-center justify-between p-3 rounded-none bg-white dark:bg-gray-800 border border-gray-200/80 dark:border-gray-700 hover:border-purple-500/50 hover:bg-purple-50/10 dark:hover:bg-purple-950/10 transition-all duration-200 group">
                                <div class="flex items-center">
                                    <div class="w-9 h-9 rounded-none flex items-center justify-center bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 mr-4 flex-shrink-0 group-hover:scale-105 transition-transform border border-purple-100 dark:border-purple-900/20">
                                        <i class="fa-solid fa-users-gear text-sm"></i>
                                    </div>
                                    <span class="text-xs font-semibold text-gray-700 dark:text-gray-300 group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors">NPC</span>
                                </div>
                                <i class="fa-solid fa-chevron-right text-[10px] text-gray-400 dark:text-gray-500 group-hover:translate-x-0.5 transition-transform mr-1"></i>
                            </a>

                            <a href="{{ env('APP_ALL_DASHBOARD_URL') }}"
                                class="flex items-center justify-between p-3 rounded-none bg-white dark:bg-gray-800 border border-gray-200/80 dark:border-gray-700 hover:border-teal-500/50 hover:bg-teal-50/10 dark:hover:bg-teal-950/10 transition-all duration-200 group">
                                <div class="flex items-center">
                                    <div class="w-9 h-9 rounded-none flex items-center justify-center bg-teal-50 dark:bg-teal-900/30 text-teal-600 dark:text-teal-400 mr-4 flex-shrink-0 group-hover:scale-105 transition-transform border border-teal-100 dark:border-teal-900/20">
                                        <i class="fa-solid fa-chart-pie text-sm"></i>
                                    </div>
                                    <span class="text-xs font-semibold text-gray-700 dark:text-gray-300 group-hover:text-teal-600 dark:group-hover:text-teal-400 transition-colors">All Dashboard</span>
                                </div>
                                <i class="fa-solid fa-chevron-right text-[10px] text-gray-400 dark:text-gray-500 group-hover:translate-x-0.5 transition-transform mr-1"></i>
                            </a>

                            <a href="{{ env('APP_MNG_URL') }}"
                                class="flex items-center justify-between p-3 rounded-none bg-white dark:bg-gray-800 border border-gray-200/80 dark:border-gray-700 hover:border-emerald-500/50 hover:bg-emerald-50/10 dark:hover:bg-emerald-950/10 transition-all duration-200 group">
                                <div class="flex items-center">
                                    <div class="w-9 h-9 rounded-none flex items-center justify-center bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 mr-4 flex-shrink-0 group-hover:scale-105 transition-transform border border-emerald-100 dark:border-emerald-900/20">
                                        <i class="fa-solid fa-briefcase text-sm"></i>
                                    </div>
                                    <span class="text-xs font-semibold text-gray-700 dark:text-gray-300 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">Management</span>
                                </div>
                                <i class="fa-solid fa-chevron-right text-[10px] text-gray-400 dark:text-gray-500 group-hover:translate-x-0.5 transition-transform mr-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Menu -->
            @auth
            <div x-data="{ open: false }" class="relative pl-4 border-l border-slate-200 dark:border-gray-700">
                
                <button @click="open = !open" @click.outside="open = false" 
                    class="flex items-center gap-3 hover:bg-primary-50 dark:hover:bg-gray-700 p-1.5 pr-3 rounded-full transition-colors border border-transparent hover:border-slate-100 dark:hover:border-gray-600">
                    <div class="h-9 w-9 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 flex items-center justify-center font-bold text-sm border border-primary-200 dark:border-primary-800">
                        {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                    </div>
                    <div class="hidden md:block text-right">
                        <p class="text-sm font-semibold text-slate-700 dark:text-gray-200 leading-none">{{ Auth::user()->name }}</p>
                        <p class="text-[11px] text-slate-400 dark:text-gray-500 mt-1">{{ Auth::user()->nik }}</p>
                    </div>
                    <i class="fa-solid fa-chevron-down text-xs text-slate-400 dark:text-gray-500 ml-1 transition-transform duration-200" :class="{'rotate-180': open}"></i>
                </button>

                <!-- Dropdown -->
                <div x-show="open" x-transition.origin.top.right
                    class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-xs border border-slate-100 dark:border-gray-700 py-1 shadow-lg" style="display: none;">
                    
                    <div class="px-4 py-3 border-b border-slate-50 dark:border-gray-700 md:hidden">
                        <p class="text-sm font-semibold text-slate-800 dark:text-gray-200">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-slate-500 dark:text-gray-400">{{ Auth::user()->nik }}</p>
                    </div>

                    <a href="{{ route('profile.index') }}" class="block px-4 py-2 text-sm text-slate-600 dark:text-gray-300 hover:bg-slate-50 dark:hover:bg-gray-700 hover:text-primary-600 dark:hover:text-primary-400 flex items-center gap-2">
                        <i class="fa-regular fa-user w-4"></i> Profile
                    </a>
                    
                    <a href="#" class="block px-4 py-2 text-sm text-slate-600 dark:text-gray-300 hover:bg-slate-50 dark:hover:bg-gray-700 hover:text-primary-600 dark:hover:text-primary-400 flex items-center gap-2">
                        <i class="fa-solid fa-gear w-4"></i> Settings
                    </a>
                    
                    <div class="border-t border-slate-50 dark:border-gray-700 my-1"></div>

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 flex items-center gap-2">
                            <i class="fa-solid fa-arrow-right-from-bracket w-4"></i> Logout
                        </button>
                    </form>
                </div>
            </div>
            @else
            <div class="pl-4 border-l border-slate-200 dark:border-gray-700">
                <a href="{{ route('login') }}" class="text-sm font-medium text-primary-600 hover:underline">Login</a>
            </div>
            @endauth

        </div>
    </div>
</header>
