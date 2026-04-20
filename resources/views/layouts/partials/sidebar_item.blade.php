@php
    if (!function_exists('isMenuActiveRecursive')) {
        function isMenuActiveRecursive($menu) {
            if ($menu->route !== '#' && request()->routeIs($menu->route.'*')) {
                return true;
            }
            foreach($menu->children as $child) {
                if (isMenuActiveRecursive($child)) {
                    return true;
                }
            }
            return false;
        }
    }
    
    $isActive = isMenuActiveRecursive($menu);
    $hasChildren = $menu->children->count() > 0;
@endphp

@if(isset($menu->type) && $menu->type === 'header')
    {{-- SECTION HEADER (COLLAPSIBLE) --}}
    <div x-data="{ sectionOpen: localStorage.getItem('section_' + {{ $menu->id }}) === 'true' || {{ $isActive ? 'true' : 'false' }} }" 
         class="section-container mb-1 {{ ($depth ?? 0) === 0 ? 'border-t border-slate-200 dark:border-gray-700/50 mt-2 first:border-0 first:mt-0' : '' }}">
        <button x-show="sidebarExpanded" @click="sectionOpen = !sectionOpen; localStorage.setItem('section_' + {{ $menu->id }}, sectionOpen); sidebarExpanded = true" 
            class="section-header-button w-full flex items-center justify-between px-3 pt-4 pb-2 group cursor-pointer hover:bg-slate-50 dark:hover:bg-gray-700/30 rounded-xs transition-colors">
            
            <span x-show="sidebarExpanded" class="text-[10px] font-bold text-slate-500 dark:text-gray-400 uppercase tracking-widest whitespace-nowrap transition-all duration-300 text-left">
                {{ $menu->title }}
            </span>

            <i x-show="sidebarExpanded" class="fa-solid fa-chevron-down text-[10px] text-slate-400 transition-transform duration-200"
               :class="sectionOpen ? 'rotate-180' : ''"></i>
        </button>
        
        <div x-show="sectionOpen" 
             x-collapse 
             id="collapsible-section-{{ $menu->id }}"
             style="display: {{ $isActive ? 'block' : 'none' }}"
             class="space-y-1">
            <script>
                if (localStorage.getItem('section_{{ $menu->id }}') === 'true' || {{ $isActive ? 'true' : 'false' }}) {
                    document.getElementById('collapsible-section-{{ $menu->id }}').style.display = 'block';
                }
            </script>
            {{-- Render Children --}}
            @foreach($menu->children as $child)
                @include('layouts.partials.sidebar_item', ['menu' => $child, 'depth' => ($depth ?? 0)])
            @endforeach
        </div>
    </div>

@elseif($hasChildren)
    {{-- PARENT MENU WITH DROPDOWN (RECURSIVE) --}}
    <div x-data="{ open: localStorage.getItem('menu_open_' + {{ $menu->id }}) === 'true' || {{ $isActive ? 'true' : 'false' }} }" class="relative w-full">
        <button @click="open = !open; localStorage.setItem('menu_open_' + {{ $menu->id }}, open); sidebarExpanded = true"
            class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xs transition-all duration-200 group relative
            {{ $isActive ? 'bg-primary-100 dark:bg-primary-900/40 text-primary-700 dark:text-primary-400 font-semibold' : 'text-slate-600 dark:text-gray-300 hover:bg-slate-50 dark:hover:bg-gray-700/50 hover:text-slate-900 dark:hover:text-white' }}"
            :class="!sidebarExpanded ? 'justify-center' : ''">
            
            @if($menu->icon)
                <i class="{{ $menu->icon }} w-6 text-center text-lg {{ $isActive ? 'text-primary-700 dark:text-primary-400' : 'text-slate-400 dark:text-gray-500 group-hover:text-slate-600 dark:group-hover:text-gray-300' }}"></i>
            @else
                <div class="w-6 flex justify-center text-[10px] opacity-70"><i class="fa-regular fa-circle"></i></div>
            @endif
            
            <span x-show="sidebarExpanded" class="side-label flex-1 text-left text-sm whitespace-nowrap">{{ $menu->title }}</span>
            
            <i x-show="sidebarExpanded" class="side-label fa-solid fa-chevron-down text-xs transition-transform duration-200" 
                :class="open ? 'rotate-180' : ''"></i>

            {{-- Tooltip for Minimized --}}
            <div x-show="!sidebarExpanded" x-cloak class="absolute left-full top-2 ml-2 bg-slate-800 dark:bg-black text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity z-50 pointer-events-none whitespace-nowrap">
                {{ $menu->title }}
            </div>
        </button>

        {{-- SUBMENU ITEMS (RECURSIVE CALL) --}}
        <div x-show="open && sidebarExpanded" 
             x-collapse 
             id="collapsible-menu-{{ $menu->id }}"
             class="submenu-container space-y-1 mt-1 transition-all duration-300 {{ ($depth ?? 0) === 0 ? 'pl-4' : 'pl-3' }}"
             :class="!sidebarExpanded ? 'pl-0' : ''"
             style="display: {{ $isActive ? 'block' : 'none' }}">
            <script>
                if (localStorage.getItem('menu_open_{{ $menu->id }}') === 'true' || {{ $isActive ? 'true' : 'false' }}) {
                    document.getElementById('collapsible-menu-{{ $menu->id }}').style.display = 'block';
                }
            </script>
            @foreach($menu->children as $child)
                @include('layouts.partials.sidebar_item', ['menu' => $child, 'depth' => ($depth ?? 0) + 1])
            @endforeach
        </div>
    </div>
@else
    {{-- SINGLE MENU ITEM --}}
    <a href="{{ $menu->route === '#' ? '#' : route($menu->route) }}"
        class="flex items-center gap-3 px-3 py-2 rounded-xs transition-all duration-200 group relative text-sm w-full
        {{ $isActive ? 'text-primary-700 dark:text-primary-400 font-medium bg-primary-100/50 dark:bg-primary-900/20' : 'text-slate-600 dark:text-gray-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-gray-700/50' }}"
        :class="!sidebarExpanded ? 'justify-center py-2.5' : ''">

        @if(($depth ?? 0) > 0)
            <span x-show="sidebarExpanded" class="w-1.5 h-1.5 rounded-full {{ $isActive ? 'bg-primary-700 dark:bg-primary-400' : 'bg-slate-400 dark:bg-gray-600' }}"></span>
        @elseif($menu->icon)
            <i class="{{ $menu->icon }} w-6 text-center text-lg {{ $isActive ? 'text-primary-700 dark:text-primary-400' : 'text-slate-400 dark:text-gray-500 group-hover:text-slate-600 dark:group-hover:text-gray-300' }}"></i>
        @endif

        <span x-show="sidebarExpanded" class="side-label whitespace-nowrap">{{ $menu->title }}</span>

        {{-- Tooltip for Minimized --}}
        <div x-show="!sidebarExpanded" x-cloak class="absolute left-full top-2 ml-2 bg-slate-800 dark:bg-black text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity z-50 pointer-events-none whitespace-nowrap">
            {{ $menu->title }}
        </div>
    </a>
@endif
