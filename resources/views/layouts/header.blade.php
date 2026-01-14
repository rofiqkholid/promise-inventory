<!-- Header -->
<header class="bg-white border-b border-slate-200 sticky top-0 z-30 h-16 flex-shrink-0">
    <div class="h-full px-6 flex items-center justify-between">
        
        <!-- Left Side -->
        <div class="flex items-center gap-4">
            <button @click="toggleSidebar()" class="p-2 text-slate-500 hover:bg-slate-100 rounded-lg">
                <i class="fa-solid fa-bars text-lg"></i>
            </button>
            
            <!-- Breadcrumb / Title -->
            <div>
                <h2 class="text-xl font-bold text-slate-800 tracking-wide">@yield('page_title', 'Dashboard')</h2>
            </div>
        </div>

        <!-- Right Side -->
        <div class="flex items-center gap-4">
            
            <!-- Notifications -->
            <button class="relative p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
                <i class="fa-regular fa-bell text-xl"></i>
                <span class="absolute top-2 right-2 h-2 w-2 bg-red-500 rounded-full border border-white"></span>
            </button>

            <!-- User Menu -->
            @auth
            <div x-data="{ open: false }" class="relative pl-4 border-l border-slate-200">
                
                <button @click="open = !open" @click.outside="open = false" 
                    class="flex items-center gap-3 hover:bg-slate-50 p-1.5 pr-3 rounded-full transition-colors border border-transparent hover:border-slate-100">
                    <div class="h-9 w-9 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm border border-blue-200">
                        {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                    </div>
                    <div class="hidden md:block text-right">
                        <p class="text-sm font-semibold text-slate-700 leading-none">{{ Auth::user()->name }}</p>
                        <p class="text-[11px] text-slate-400 mt-1">{{ Auth::user()->role ?? 'User' }}</p>
                    </div>
                    <i class="fa-solid fa-chevron-down text-xs text-slate-400 ml-1 transition-transform duration-200" :class="{'rotate-180': open}"></i>
                </button>

                <!-- Dropdown -->
                <div x-show="open" x-transition.origin.top.right
                    class="absolute right-0 mt-2 w-48 bg-white rounded-lg border border-slate-100 py-1" style="display: none;">
                    
                    <div class="px-4 py-3 border-b border-slate-50 md:hidden">
                        <p class="text-sm font-semibold text-slate-800">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-slate-500">{{ Auth::user()->email }}</p>
                    </div>

                    <a href="#" class="block px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 hover:text-blue-600 flex items-center gap-2">
                        <i class="fa-regular fa-user w-4"></i> Profile
                    </a>
                    
                    <a href="#" class="block px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 hover:text-blue-600 flex items-center gap-2">
                        <i class="fa-solid fa-gear w-4"></i> Settings
                    </a>
                    
                    <div class="border-t border-slate-50 my-1"></div>

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 flex items-center gap-2">
                            <i class="fa-solid fa-arrow-right-from-bracket w-4"></i> Logout
                        </button>
                    </form>
                </div>
            </div>
            @else
            <div class="pl-4 border-l border-slate-200">
                <a href="{{ route('login') }}" class="text-sm font-medium text-blue-600 hover:underline">Login</a>
            </div>
            @endauth

        </div>
    </div>
</header>
