<!-- Sidebar Content -->
<div class="flex flex-col h-full bg-white dark:bg-gray-800 text-slate-700 dark:text-gray-300">

    <!-- Logo -->
    <div class="flex items-center gap-3 p-4 border-b border-slate-200 dark:border-gray-700 h-16 transition-all duration-300"
        :class="sidebarExpanded ? 'justify-start' : 'justify-center'">
        <img src="{{ asset('assets/image/logo-promise.png') }}" alt="PROMISE" class="h-8 w-auto">
        <div x-show="sidebarExpanded" x-transition:enter="transition ease-out duration-200 delay-100" class="logo-label overflow-hidden whitespace-nowrap">
            <h1 class="text-sm font-bold text-slate-900 dark:text-white leading-tight">PROMISE</h1>
            <p class="text-[10px] text-slate-500 dark:text-gray-400 uppercase tracking-wider">Inventory</p>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 py-4 px-3 space-y-1 custom-scrollbar"
        :class="sidebarExpanded ? 'overflow-y-auto' : 'overflow-visible'">

        @foreach($sidebarMenus as $menu)
            @php
                $isParentActive = false;
                if ($menu->route !== '#' && request()->routeIs($menu->route.'*')) {
                    $isParentActive = true;
                }
                if ($menu->children->count() > 0) {
                    foreach($menu->children as $child) {
                        if (request()->routeIs($child->route.'*')) {
                            $isParentActive = true;
                            break;
                        }
                    }
                }
            @endphp
            
            @if($menu->children->count() > 0)
                {{-- PARENT MENU WITH DROPDOWN --}}
                <div x-data="{ open: {{ $isParentActive ? 'true' : 'false' }} }" class="relative">
                    <button @click="open = !open; sidebarExpanded = true"
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xs transition-all duration-200 group relative
                        {{ $isParentActive ? 'bg-primary-100 dark:bg-primary-900/40 text-primary-700 dark:text-primary-400 font-semibold' : 'text-slate-600 dark:text-gray-300 hover:bg-slate-50 dark:hover:bg-gray-700/50 hover:text-slate-900 dark:hover:text-white' }}"
                        :class="!sidebarExpanded ? 'justify-center' : ''">
                        
                        <i class="{{ $menu->icon }} w-6 text-center text-lg {{ $isParentActive ? 'text-primary-700 dark:text-primary-400' : 'text-slate-400 dark:text-gray-500 group-hover:text-slate-600 dark:group-hover:text-gray-300' }}"></i>
                        
                        <span x-show="sidebarExpanded" class="side-label flex-1 text-left text-sm whitespace-nowrap">{{ $menu->title }}</span>
                        
                        <i x-show="sidebarExpanded" class="side-label fa-solid fa-chevron-down text-xs transition-transform duration-200" 
                           :class="open ? 'rotate-180' : ''"></i>

                        {{-- Tooltip for Minimized --}}
                        <div x-show="!sidebarExpanded" x-cloak class="absolute left-full top-2 ml-2 bg-slate-800 dark:bg-black text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity z-50 pointer-events-none whitespace-nowrap">
                            {{ $menu->title }}
                        </div>
                    </button>

                    {{-- SUBMENU ITEMS --}}
                    <div x-show="open && sidebarExpanded" 
                         x-collapse 
                         class="submenu-container pl-4 space-y-1 mt-1"
                         style="display: {{ $isParentActive ? 'block' : 'none' }}">
                        @foreach($menu->children as $child)
                            <a href="{{ route($child->route) }}"
                                class="flex items-center gap-3 px-3 py-2 rounded-xs transition-all duration-200 text-sm
                                {{ request()->routeIs($child->route.'*') ? 'text-primary-700 dark:text-primary-400 font-medium bg-primary-100/50 dark:bg-primary-900/20' : 'text-slate-600 dark:text-gray-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-gray-700/50' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs($child->route.'*') ? 'bg-primary-700 dark:bg-primary-400' : 'bg-slate-400 dark:bg-gray-600' }}"></span>
                                <span class="side-label whitespace-nowrap">{{ $child->title }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @else
                {{-- SINGLE MENU ITEM --}}
                <a href="{{ $menu->route === '#' ? '#' : route($menu->route) }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xs transition-all duration-200 group relative
                {{ $isParentActive ? 'bg-primary-100 dark:bg-primary-900/40 text-primary-700 dark:text-primary-400 font-semibold' : 'text-slate-600 dark:text-gray-300 hover:bg-slate-50 dark:hover:bg-gray-700/50 hover:text-slate-900 dark:hover:text-white' }}"
                    :class="!sidebarExpanded ? 'justify-center' : ''">

                    <i class="{{ $menu->icon }} w-6 text-center text-lg
                        {{ $isParentActive ? 'text-primary-700 dark:text-primary-400' : 'text-slate-400 dark:text-gray-500 group-hover:text-slate-600 dark:group-hover:text-gray-300' }}">
                    </i>

                    <span x-show="sidebarExpanded" class="side-label text-sm whitespace-nowrap">{{ $menu->title }}</span>

                    {{-- Tooltip for Minimized --}}
                    <div x-show="!sidebarExpanded" x-cloak class="absolute left-full top-2 ml-2 bg-slate-800 dark:bg-black text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity z-50 pointer-events-none whitespace-nowrap">
                        {{ $menu->title }}
                    </div>
                </a>
            @endif
        @endforeach

    </nav>

    <!-- Footer Profile / Settings -->
    <div class="p-4 border-t border-slate-200 dark:border-gray-700">
        <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-xs hover:bg-slate-50 dark:hover:bg-gray-700 transition-colors group relative"
            :class="!sidebarExpanded ? 'justify-center' : ''">
            <i class="fa-solid fa-gear w-6 text-center text-lg text-slate-400 dark:text-gray-500 group-hover:text-slate-600 dark:group-hover:text-gray-300"></i>
            <span x-show="sidebarExpanded" class="side-label text-sm font-medium whitespace-nowrap text-slate-600 dark:text-gray-400 group-hover:text-slate-900 dark:group-hover:text-white">Settings</span>
            {{-- Tooltip for Minimized --}}
            <div x-show="!sidebarExpanded" x-cloak class="absolute left-full top-2 ml-2 bg-slate-800 dark:bg-black text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity z-50 pointer-events-none whitespace-nowrap">
                Settings
            </div>
        </a>
    </div>
</div>