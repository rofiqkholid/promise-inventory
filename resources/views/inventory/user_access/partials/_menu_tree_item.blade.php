@foreach($menus as $menu)
    <div class="menu-node {{ $depth === 0 ? 'bg-white dark:bg-gray-800/40 rounded-xs border border-slate-200 dark:border-gray-700 mb-2 overflow-hidden shadow-sm' : 'ml-4 mt-1 border-l border-slate-100 dark:border-gray-700 pl-3' }}" 
         data-id="{{ $menu->id }}" 
         data-parent="{{ $menu->parent_id ?? '' }}">
        
        <div class="flex items-center justify-between p-2 {{ $depth === 0 ? 'bg-slate-50/50 dark:bg-gray-700/20' : '' }}">
            <label class="flex items-center gap-2.5 cursor-pointer group flex-1">
                <input type="checkbox" name="menu_ids[]" value="{{ $menu->id }}" 
                    class="menu-checkbox w-3.5 h-3.5 rounded-xs border-gray-300 text-primary-600 focus:ring-0 transition-all cursor-pointer"
                    data-id="{{ $menu->id }}"
                    data-parent="{{ $menu->parent_id ?? '' }}">
                
                <div class="flex items-center gap-2">
                    @if($menu->icon)
                        <div class="w-7 h-7 rounded-xs flex items-center justify-center {{ $depth === 0 ? 'bg-white dark:bg-gray-800 border border-slate-100 dark:border-gray-700' : 'bg-transparent' }}">
                            <i class="{{ $menu->icon }} {{ $depth === 0 ? 'text-primary-600 dark:text-primary-400' : 'text-slate-400 dark:text-gray-500' }} text-[10px]"></i>
                        </div>
                    @endif
                    <div class="flex flex-col leading-none">
                        <span class="{{ $depth === 0 ? 'text-[11px] font-bold text-slate-800 dark:text-white uppercase tracking-wide' : 'text-xs font-semibold text-slate-600 dark:text-gray-300' }}">
                            {{ $menu->title }}
                        </span>
                    </div>
                </div>
            </label>

            @if($menu->children->count() > 0)
                <button type="button" class="select-all-children px-2 py-0.5 text-[9px] font-black text-primary-600 bg-primary-50 hover:bg-primary-100 dark:bg-primary-900/30 dark:text-primary-400 rounded-xs transition-colors border border-primary-100 dark:border-primary-800/50" data-id="{{ $menu->id }}">
                    ALL
                </button>
            @endif
        </div>
        
        @if($menu->children->count() > 0)
            <div class="pb-1 pr-2">
                @include('inventory.user_access.partials._menu_tree_item', ['menus' => $menu->children, 'depth' => $depth + 1])
            </div>
        @endif
    </div>
@endforeach
