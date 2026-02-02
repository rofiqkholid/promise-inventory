@extends('layouts.app')

@section('title', 'System Access & User Management')
@section('page_title', 'Access Management')

@section('content')
<div class="p-4 sm:p-6 lg:p-8 text-gray-900 dark:text-gray-100">
    <div class="mb-6">
        <h2 class="text-2xl font-black text-gray-900 dark:text-gray-100 sm:text-3xl tracking-tighter">Access Control Center</h2>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-medium">Manage Roles, Permissions, User Accounts, and Application Menus.</p>
    </div>

    <!-- TABS NAVIGATION -->
    <div class="mb-6 border-b border-gray-200 dark:border-gray-700">
        <ul class="flex flex-wrap -mb-px text-sm font-bold text-center" id="accessTabs" role="tablist">
            <li class="mr-4" role="presentation">
                <button class="inline-flex items-center p-3 border-b-2 rounded-t-md transition-all gap-2" id="users-tab" data-tabs-target="#users-panel" type="button" role="tab">
                    <div class="p-1.5 bg-blue-50 dark:bg-blue-900/30 rounded-md">
                        <i class="fa-solid fa-users text-blue-600 dark:text-blue-400"></i>
                    </div>
                    User Allocation
                </button>
            </li>
            <li class="mr-4" role="presentation">
                <button class="inline-flex items-center p-3 border-b-2 border-transparent rounded-t-md hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300 transition-all gap-2" id="roles-tab" data-tabs-target="#roles-panel" type="button" role="tab">
                    <div class="p-1.5 bg-purple-50 dark:bg-purple-900/30 rounded-md">
                        <i class="fa-solid fa-shield-halved text-purple-600 dark:text-purple-400"></i>
                    </div>
                    Role Management
                </button>
            </li>
            <li class="mr-4" role="presentation">
                <button class="inline-flex items-center p-3 border-b-2 border-transparent rounded-t-md hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300 transition-all gap-2" id="accounts-tab" data-tabs-target="#accounts-panel" type="button" role="tab">
                    <div class="p-1.5 bg-amber-50 dark:bg-amber-900/30 rounded-md">
                        <i class="fa-solid fa-user-gear text-amber-600 dark:text-amber-400"></i>
                    </div>
                    User Accounts
                </button>
            </li>
            <li class="mr-4" role="presentation">
                <button class="inline-flex items-center p-3 border-b-2 border-transparent rounded-t-md hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300 transition-all gap-2" id="menus-tab" data-tabs-target="#menus-panel" type="button" role="tab">
                    <div class="p-1.5 bg-emerald-50 dark:bg-emerald-900/30 rounded-md">
                        <i class="fa-solid fa-bars text-emerald-600 dark:text-emerald-400"></i>
                    </div>
                    Menu Management
                </button>
            </li>
        </ul>
    </div>

    <!-- TABS CONTENT -->
    <div id="accessTabsContent">
        <!-- TAB 1: USER ALLOCATION -->
        <div class="hidden animate-fadeIn" id="users-panel" role="tabpanel">
            <div class="mb-6 p-4 bg-white dark:bg-gray-800 rounded-md border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
                <div>
                    <h3 class="font-bold text-gray-900 dark:text-white">Active Permissions</h3>
                    <p class="text-xs text-gray-500">Currently assigned roles and custom permissions.</p>
                </div>
                <button onclick="openAddUserRoleModal()" class="w-full md:w-auto inline-flex items-center justify-center px-6 py-2 bg-slate-900 dark:bg-white text-white dark:text-slate-900 text-sm font-bold rounded-md transition-all shadow-sm hover:bg-slate-800 gap-2">
                    <i class="fa-solid fa-user-plus text-xs"></i> Grant New Access
                </button>
            </div>
            <x-table id="UserAccessTable">
                <thead>
                    <tr>
                        <th class="w-16">No</th>
                        <th class="text-left">Employee Name</th>
                        <th class="text-left">Email Address</th>
                        <th class="text-center">Role Assigned</th>
                        <th class="w-32 text-center">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </x-table>
        </div>

        <!-- TAB 2: ROLES MANAGEMENT -->
        <div class="hidden animate-fadeIn" id="roles-panel" role="tabpanel">
            <div class="mb-6 p-4 bg-white dark:bg-gray-800 rounded-md border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
                <div>
                    <h3 class="font-bold text-gray-900 dark:text-white">Role Definitions</h3>
                    <p class="text-xs text-gray-500">Define access levels and associated menus.</p>
                </div>
                <button onclick="openAddRoleModal()" class="w-full md:w-auto inline-flex items-center justify-center px-6 py-2 bg-purple-600 text-white text-sm font-bold rounded-md transition-all shadow-sm hover:bg-purple-700 gap-2">
                    <i class="fa-solid fa-plus text-xs"></i> Create Role
                </button>
            </div>
            <x-table id="RolesTable">
                <thead>
                    <tr>
                        <th class="w-16">No</th>
                        <th class="text-center w-32">Code</th>
                        <th class="text-left">Display Name</th>
                        <th class="text-left">Description</th>
                        <th class="w-40 text-center">Configuration</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </x-table>
        </div>

        <!-- TAB 3: USER ACCOUNTS -->
        <div class="hidden animate-fadeIn" id="accounts-panel" role="tabpanel">
            <div class="mb-6 p-4 bg-white dark:bg-gray-800 rounded-md border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
                <div>
                    <h3 class="font-bold text-gray-900 dark:text-white">Employee Accounts</h3>
                    <p class="text-xs text-gray-500">Manage credentials and ID data.</p>
                </div>
                <button onclick="openAddAccountModal()" class="w-full md:w-auto inline-flex items-center justify-center px-6 py-2 bg-amber-600 text-white text-sm font-bold rounded-md transition-all shadow-sm hover:bg-amber-700 gap-2">
                    <i class="fa-solid fa-user-plus text-xs"></i> Create Account
                </button>
            </div>
            <x-table id="AccountsTable">
                <thead>
                    <tr>
                        <th class="w-16">No</th>
                        <th class="text-center w-32">NIK</th>
                        <th class="text-left">Full Name</th>
                        <th class="text-left">Email</th>
                        <th class="w-32 text-center">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </x-table>
        </div>

        <!-- TAB 4: MENU MANAGEMENT -->
        <div class="hidden animate-fadeIn" id="menus-panel" role="tabpanel">
            <div class="mb-6 p-4 bg-white dark:bg-gray-800 rounded-md border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
                <div>
                    <h3 class="font-bold text-gray-900 dark:text-white">Application Menus</h3>
                    <p class="text-xs text-gray-500">Organize sidebar structure and hierarchy.</p>
                </div>
                <button onclick="openAddMenuModal()" class="w-full md:w-auto inline-flex items-center justify-center px-6 py-2 bg-emerald-600 text-white text-sm font-bold rounded-md transition-all shadow-sm hover:bg-emerald-700 gap-2">
                    <i class="fa-solid fa-plus text-xs"></i> Add Menu Item
                </button>
            </div>
            <x-table id="MenusTable">
                <thead>
                    <tr>
                        <th class="w-16">No</th>
                        <th class="w-16 text-center">Icon</th>
                        <th class="text-left">Menu Title / Route</th>
                        <th class="text-left">Parent Menu</th>
                        <th class="w-24 text-center">Status</th>
                        <th class="w-16 text-center">Order</th>
                        <th class="w-32 text-center">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </x-table>
        </div>
    </div>
</div>

<!-- MODALS -->

<!-- 1. USER ROLE ALLOCATION MODAL -->
<div id="addUserRoleModal" tabindex="-1" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
    <div class="relative w-full max-w-sm">
        <div class="bg-white dark:bg-gray-800 rounded-md shadow-xl overflow-hidden border border-gray-200 dark:border-gray-700">
            <div class="px-5 py-4 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h3 id="addUserRoleModalTitle" class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-3">
                    <i class="fa-solid fa-user-shield text-blue-600"></i> Allocation Details
                </h3>
                <button onclick="closeModal('addUserRoleModal')" class="text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <form id="addUserRoleForm" class="p-5">
                @csrf
                <input type="hidden" name="id" id="user_allocation_id">
                <div class="space-y-4">
                    <div>
                        <label class="block mb-1.5 text-xs font-bold text-gray-500 uppercase tracking-wider">Employee Account</label>
                        <select name="user_id" id="user_id_select" class="select2-theme w-full" required></select>
                        <input type="hidden" id="edit_mode_user_id">
                    </div>
                    <div>
                        <label class="block mb-1.5 text-xs font-bold text-gray-500 uppercase tracking-wider">Permission Level</label>
                        <select name="role_id" id="role_id_select" class="w-full p-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-md text-sm font-bold outline-none" required>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mt-6 flex gap-3">
                    <button type="button" onclick="closeModal('addUserRoleModal')" class="flex-1 py-2 text-sm font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-md transition-all">Cancel</button>
                    <button type="submit" class="flex-1 py-2 text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-md shadow-sm transition-all active:scale-95">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 2. ROLE CRUD MODAL -->
<div id="roleModal" tabindex="-1" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
    <div class="relative w-full max-w-sm">
        <div class="bg-white dark:bg-gray-800 rounded-md shadow-xl overflow-hidden border border-gray-200 dark:border-gray-700">
            <div class="px-5 py-4 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h3 id="roleModalTitle" class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-3">
                    <i class="fa-solid fa-shield-halved text-purple-600"></i> Role Details
                </h3>
                <button onclick="closeModal('roleModal')" class="text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <form id="roleForm" class="p-5">
                @csrf
                <input type="hidden" name="id" id="role_id_input">
                <div class="space-y-3">
                    <div>
                        <label class="block mb-1.5 text-xs font-bold text-gray-500 uppercase tracking-wider">Role Name</label>
                        <input type="text" name="name" id="role_name_input" class="w-full p-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-md text-sm font-bold" required>
                    </div>
                    <div>
                        <label class="block mb-1.5 text-xs font-bold text-gray-500 uppercase tracking-wider">Code (Unique)</label>
                        <input type="text" name="code" id="role_code_input" class="w-full p-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-md text-sm font-mono" required>
                    </div>
                    <div>
                        <label class="block mb-1.5 text-xs font-bold text-gray-500 uppercase tracking-wider">Description</label>
                        <textarea name="description" id="role_description_input" rows="2" class="w-full p-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-md text-sm font-medium"></textarea>
                    </div>
                </div>
                <div class="mt-6 flex gap-3">
                    <button type="button" onclick="closeModal('roleModal')" class="flex-1 py-2 text-sm font-bold text-gray-500 bg-gray-100 hover:bg-gray-200 rounded-md transition-all">Cancel</button>
                    <button type="submit" class="flex-1 py-2 text-sm font-bold text-white bg-purple-600 hover:bg-purple-700 rounded-md shadow-sm active:scale-95 transition-all">Save Role</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 3. PERMISSIONS MODAL -->
<div id="permissionModal" tabindex="-1" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
    <div class="relative w-full max-w-xl">
        <div class="bg-white dark:bg-gray-800 rounded-md shadow-xl overflow-hidden border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <div>
                    <h3 id="permissionModalTitle" class="text-lg font-bold text-gray-900 dark:text-white uppercase tracking-tight">Access Control</h3>
                    <p class="text-[10px] font-bold text-blue-600 dark:text-blue-400 uppercase tracking-widest mt-0.5" id="permissionSubjectName"></p>
                </div>
                <button onclick="closeModal('permissionModal')" class="text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>
            <form id="permissionForm" class="p-6">
                @csrf
                <input type="hidden" name="role_id" id="permission_role_id">
                <input type="hidden" name="user_id" id="permission_user_id">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-[50vh] overflow-y-auto px-1 py-2 custom-scrollbar-minimal">
                    @foreach($allMenus as $menu)
                        <div class="p-3 bg-gray-50/50 dark:bg-gray-900/30 rounded-md border border-gray-100 dark:border-gray-800">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="menu_ids[]" value="{{ $menu->id }}" class="menu-checkbox w-4 h-4 rounded-sm border-gray-300 text-blue-600 focus:ring-0 transition-all cursor-pointer">
                                <div class="flex items-center gap-2">
                                    <i class="{{ $menu->icon }} text-blue-600 dark:text-blue-400 text-sm"></i>
                                    <span class="text-sm font-bold text-gray-900 dark:text-white">{{ $menu->title }}</span>
                                </div>
                            </label>
                            
                            @if($menu->children->count() > 0)
                                <div class="mt-2 ml-6 space-y-1.5 border-l border-slate-200 dark:border-slate-700 pl-4">
                                    @foreach($menu->children as $child)
                                        <label class="flex items-center gap-2.5 cursor-pointer group/child">
                                            <input type="checkbox" name="menu_ids[]" value="{{ $child->id }}" class="menu-checkbox w-3.5 h-3.5 rounded-sm border-gray-300 text-blue-500 focus:ring-0 transition-all cursor-pointer">
                                            <span class="text-[11px] font-bold text-gray-500 dark:text-gray-400 group-hover/child:text-blue-600 transition-colors uppercase">{{ $child->title }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
                <div class="mt-8 flex gap-3">
                    <button type="button" onclick="closeModal('permissionModal')" class="flex-1 py-2 text-sm font-bold text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all rounded-md">Cancel</button>
                    <button type="submit" class="flex-1 py-2 text-sm font-bold text-white bg-slate-900 dark:bg-white dark:text-slate-950 rounded-md shadow-sm transition-all active:scale-95 uppercase">Sync Permissions</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 4. USER ACCOUNT CRUD MODAL -->
<div id="accountModal" tabindex="-1" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
    <div class="relative w-full max-w-md">
        <div class="bg-white dark:bg-gray-800 rounded-md shadow-xl overflow-hidden border border-gray-200 dark:border-gray-700">
            <div class="px-5 py-4 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h3 id="accountModalTitle" class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-3">
                    <i class="fa-solid fa-id-card text-amber-500"></i> Account Identity
                </h3>
                <button onclick="closeModal('accountModal')" class="text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <form id="accountForm" class="p-5">
                @csrf
                <input type="hidden" name="id" id="account_id_input">
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block mb-1 text-xs font-bold text-gray-400 uppercase">NIK</label>
                            <input type="text" name="nik" id="account_nik_input" class="w-full p-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-md text-sm font-bold" required>
                        </div>
                        <div>
                            <label class="block mb-1 text-xs font-bold text-gray-400 uppercase">Email</label>
                            <input type="email" name="email" id="account_email_input" class="w-full p-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-md text-sm font-bold" required>
                        </div>
                    </div>
                    <div>
                        <label class="block mb-1 text-xs font-bold text-gray-400 uppercase">Full Name</label>
                        <input type="text" name="name" id="account_name_input" class="w-full p-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-md text-sm font-bold" required>
                    </div>
                    
                    <div class="pt-3 border-t border-gray-100 dark:border-gray-700 mt-2">
                        <p class="text-[10px] font-black uppercase text-amber-600 mb-3">Credentials</p>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block mb-1 text-xs font-bold text-gray-400 uppercase">Password</label>
                                <input type="password" name="password" id="account_password_input" class="w-full p-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-md text-sm font-bold">
                                <p id="password_hint" class="mt-1 text-[9px] text-gray-400 italic hidden">Empty to keep safe.</p>
                            </div>
                            <div>
                                <label class="block mb-1 text-xs font-bold text-gray-400 uppercase">Confirm</label>
                                <input type="password" name="password_confirmation" id="account_password_confirm_input" class="w-full p-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-md text-sm font-bold">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex gap-3">
                    <button type="button" onclick="closeModal('accountModal')" class="flex-1 py-2 text-sm font-bold text-gray-500 bg-gray-100 hover:bg-gray-200 rounded-md transition-all">Abort</button>
                    <button type="submit" class="flex-1 py-2 text-sm font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-md shadow-sm active:scale-95 transition-all">Commit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 5. MENU MANAGEMENT MODAL -->
<div id="menuModal" tabindex="-1" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
    <div class="relative w-full max-w-md">
        <div class="bg-white dark:bg-gray-800 rounded-md shadow-xl overflow-hidden border border-gray-200 dark:border-gray-700">
            <div class="px-5 py-4 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h3 id="menuModalTitle" class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-3">
                    <i class="fa-solid fa-bars text-emerald-600"></i> Menu Configuration
                </h3>
                <button onclick="closeModal('menuModal')" class="text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <form id="menuForm" class="p-5">
                @csrf
                <input type="hidden" name="id" id="menu_id_input">
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="col-span-1">
                            <label class="block mb-1 text-xs font-bold text-gray-400 uppercase">Title</label>
                            <input type="text" name="title" id="menu_title_input" class="w-full p-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-md text-sm font-bold" required>
                        </div>
                        <div class="col-span-1">
                            <label class="block mb-1 text-xs font-bold text-gray-400 uppercase">Icon Class</label>
                            <input type="text" name="icon" id="menu_icon_input" placeholder="fa-solid fa-house" class="w-full p-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-md text-sm font-mono">
                        </div>
                    </div>
                    <div>
                        <label class="block mb-1 text-xs font-bold text-gray-400 uppercase">Route Name</label>
                        <input type="text" name="route" id="menu_route_input" placeholder="dashboard" class="w-full p-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-md text-sm font-bold">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="col-span-1">
                            <label class="block mb-1 text-xs font-bold text-gray-400 uppercase">Parent Menu</label>
                            <select name="parent_id" id="menu_parent_id_input" class="w-full p-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-md text-sm font-bold">
                                <option value="">No Parent (Root)</option>
                                @foreach(\App\Models\InventoryModel\Menu::whereNull('parent_id')->get() as $p)
                                    <option value="{{ $p->id }}">{{ $p->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-1">
                            <label class="block mb-1 text-xs font-bold text-gray-400 uppercase">Display Order</label>
                            <input type="number" name="order" id="menu_order_input" class="w-full p-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-md text-sm font-bold" required>
                        </div>
                    </div>
                    <div class="pt-3 border-t border-gray-100 dark:border-gray-700 flex items-center gap-3">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" id="menu_is_active_input" value="1" class="sr-only peer" checked>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-emerald-600"></div>
                        </label>
                        <span class="text-sm font-black text-gray-700 dark:text-gray-300 uppercase italic">Is Visible / Active</span>
                    </div>
                </div>
                <div class="mt-6 flex gap-3">
                    <button type="button" onclick="closeModal('menuModal')" class="flex-1 py-2 text-sm font-bold text-gray-500 bg-gray-100 hover:bg-gray-200 rounded-md transition-all">Cancel</button>
                    <button type="submit" class="flex-1 py-2 text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-md shadow-sm active:scale-95 transition-all">Save Structure</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    let userTable, rolesTable, accountsTable, menusTable;

    $(document).ready(function() {
        initTables();
        initTabs();
        initSelect2();
        initForms();
    });

    function initTabs() {
        const tabBtns = $('#accessTabs button');
        const panels = $('#accessTabsContent > div');

        tabBtns.on('click', function() {
            const target = $(this).data('tabs-target');
            tabBtns.removeClass('border-blue-600 dark:border-blue-500 text-blue-600 dark:text-white').addClass('border-transparent text-gray-500 dark:text-gray-400');
            $(this).removeClass('border-transparent text-gray-500 dark:text-gray-400').addClass('border-blue-600 dark:border-blue-500 text-blue-600 dark:text-white');
            panels.addClass('hidden');
            $(target).removeClass('hidden');

            if(target === '#roles-panel' && rolesTable) rolesTable.columns.adjust().draw();
            if(target === '#users-panel' && userTable) userTable.columns.adjust().draw();
            if(target === '#accounts-panel' && accountsTable) accountsTable.columns.adjust().draw();
            if(target === '#menus-panel' && menusTable) menusTable.columns.adjust().draw();
        });
        tabBtns.first().click();
    }

    function initTables() {
        userTable = window.defaultDataTable('UserAccessTable', {
            processing: true, serverSide: true,
            ajax: "{{ route('inventory.userAccess.data') }}",
            columns: [
                { data: 'DT_RowIndex', className: 'text-center font-bold text-gray-400' },
                { data: 'user_name', className: 'font-bold text-gray-900 dark:text-white' },
                { data: 'user_email', className: 'text-gray-500 text-sm' },
                { data: 'role_badge', className: 'text-center' },
                { data: 'action', className: 'text-center', orderable: false, searchable: false }
            ]
        });

        rolesTable = window.defaultDataTable('RolesTable', {
            ajax: "{{ route('inventory.roles.data') }}",
            columns: [
                { data: 'DT_RowIndex', className: 'text-center font-bold text-gray-400' },
                { data: 'code', className: 'text-center font-mono text-[10px] bg-slate-50 dark:bg-slate-900 px-2 py-1 rounded border border-slate-100 dark:border-slate-800' },
                { data: 'name', className: 'font-bold text-slate-900 dark:text-slate-100' },
                { data: 'description', className: 'text-xs text-slate-500 font-medium' },
                { data: 'action', className: 'text-center', orderable: false }
            ]
        });

        accountsTable = window.defaultDataTable('AccountsTable', {
            ajax: "{{ route('inventory.users.data') }}",
            columns: [
                { data: 'DT_RowIndex', className: 'text-center font-bold text-gray-400' },
                { data: 'nik', className: 'text-center font-bold text-xs text-amber-600 bg-amber-50 dark:bg-amber-900/40 px-2 py-1 rounded border border-amber-100 dark:border-amber-800' },
                { data: 'name', className: 'font-bold' },
                { data: 'email', className: 'text-gray-500 text-sm' },
                { data: 'action', className: 'text-center', orderable: false }
            ]
        });

        menusTable = window.defaultDataTable('MenusTable', {
            ajax: "{{ route('inventory.menus.data') }}",
            columns: [
                { data: 'DT_RowIndex', className: 'text-center font-bold text-gray-400' },
                { data: 'icon', className: 'text-center' },
                { data: 'title', className: 'font-bold' },
                { data: 'parent', className: 'text-sm font-bold text-emerald-600' },
                { data: 'status', className: 'text-center' },
                { data: 'order', className: 'text-center' },
                { data: 'action', className: 'text-center', orderable: false }
            ]
        });

        setupTableListeners();
    }

    function setupTableListeners() {
        // User Allocation
        $('#UserAccessTable').on('click', '.edit-user-role-btn', function() {
            const id = $(this).data('id');
            $.get("{{ url('inventory/user-access') }}/" + id, function(data) {
                $('#user_allocation_id').val(data.id);
                $('#role_id_select').val(data.role_id);
                $('#edit_mode_user_id').val(data.user_id);
                const option = new Option(data.user_name, data.user_id, true, true);
                $('#user_id_select').append(option).trigger('change');
                $('#addUserRoleModalTitle').html('<i class="fa-solid fa-pen-to-square text-blue-500"></i> Edit Allocation');
                $('#addUserRoleModal').removeClass('hidden').addClass('flex');
            });
        });

        $('#UserAccessTable').on('click', '.user-permission-btn', function() {
            const id = $(this).data('id'); const name = $(this).data('name');
            $('#permissionSubjectName').text(id ? 'USER: ' + name : '');
            $('#permission_user_id').val(id); $('#permission_role_id').val('');
            $('#permissionModalTitle').text('User Specific Menu');
            $('.menu-checkbox').prop('checked', false);
            $.get("{{ url('inventory/user-menus') }}/" + id, function(data) {
                data.active_menus.forEach(menuId => $(`.menu-checkbox[value="${menuId}"]`).prop('checked', true));
                $('#permissionModal').removeClass('hidden').addClass('flex');
            });
        });

        $('#UserAccessTable').on('click', '.delete-access-btn', function() {
            deleteItem("{{ url('inventory/user-access') }}/" + $(this).data('id'), userTable, 'Revoke Access?', 'Allocation will be removed.');
        });

        // Roles
        $('#RolesTable').on('click', '.edit-role-btn', function() {
            $.get("{{ url('inventory/roles') }}/" + $(this).data('id'), function(data) {
                $('#role_id_input').val(data.id); $('#role_name_input').val(data.name); $('#role_code_input').val(data.code); $('#role_description_input').val(data.description);
                $('#roleModalTitle').html('<i class="fa-solid fa-pen-to-square text-blue-500"></i> Edit Role');
                $('#roleModal').removeClass('hidden').addClass('flex');
            });
        });

        $('#RolesTable').on('click', '.permission-role-btn', function() {
            const id = $(this).data('id'); const name = $(this).data('name');
            $('#permissionSubjectName').text('ROLE: ' + name);
            $('#permission_role_id').val(id); $('#permission_user_id').val('');
            $('#permissionModalTitle').text('Role Basic Menu');
            $('.menu-checkbox').prop('checked', false);
            $.get("{{ url('inventory/role-menus') }}/" + id, function(data) {
                data.active_menus.forEach(menuId => $(`.menu-checkbox[value="${menuId}"]`).prop('checked', true));
                $('#permissionModal').removeClass('hidden').addClass('flex');
            });
        });

        $('#RolesTable').on('click', '.delete-role-btn', function() {
            deleteItem("{{ url('inventory/roles') }}/" + $(this).data('id'), rolesTable, 'Delete Role?', 'Used roles cannot be deleted.');
        });

        // Accounts Table
        $('#AccountsTable').on('click', '.edit-user-btn', function() {
            $.get("{{ url('inventory/users') }}/" + $(this).data('id'), function(data) {
                $('#account_id_input').val(data.id); $('#account_nik_input').val(data.nik); $('#account_name_input').val(data.name); $('#account_email_input').val(data.email);
                $('#account_password_input, #account_password_confirm_input').val(''); $('#password_hint').show();
                $('#accountModalTitle').html('<i class="fa-solid fa-user-pen text-amber-500"></i> Edit Account');
                $('#accountModal').removeClass('hidden').addClass('flex');
            });
        });
        $('#AccountsTable').on('click', '.delete-user-btn', function() {
            deleteItem("{{ url('inventory/users') }}/" + $(this).data('id'), accountsTable, 'Delete Account?', 'Record will be wiped.');
        });

        // Menus Table
        $('#MenusTable').on('click', '.edit-menu-btn', function() {
            $.get("{{ url('inventory/menus') }}/" + $(this).data('id'), function(data) {
                $('#menu_id_input').val(data.id);
                $('#menu_title_input').val(data.title);
                $('#menu_icon_input').val(data.icon);
                $('#menu_route_input').val(data.route);
                $('#menu_parent_id_input').val(data.parent_id || '');
                $('#menu_order_input').val(data.order);
                $('#menu_is_active_input').prop('checked', data.is_active == 1);
                $('#menuModalTitle').html('<i class="fa-solid fa-pen-to-square text-emerald-500"></i> Edit Menu Structure');
                $('#menuModal').removeClass('hidden').addClass('flex');
            });
        });
        $('#MenusTable').on('click', '.delete-menu-btn', function() {
            deleteItem("{{ url('inventory/menus') }}/" + $(this).data('id'), menusTable, 'Delete Menu?', 'Ensure no sub-menus exist before deleting.');
        });
    }

    function initSelect2() {
        $('#user_id_select').select2({
            dropdownParent: $('#addUserRoleModal'), width: '100%', placeholder: 'Search...',
            ajax: {
                url: "{{ route('inventory.userAccess.search') }}", dataType: 'json', delay: 250,
                data: params => ({ term: params.term, exclude_id: $('#edit_mode_user_id').val() }),
                processResults: data => ({ results: data.results }), cache: true
            },
            minimumInputLength: 0
        });
    }

    function initForms() {
        const handle = (e, url, modId, tab) => {
            e.preventDefault();
            $.ajax({
                url: url, type: 'POST', data: $(e.target).serialize(),
                success: res => {
                    Swal.fire({ icon: 'success', title: 'Done', text: res.message, timer: 1200, showConfirmButton: false });
                    closeModal(modId); if(tab) tab.ajax.reload();
                },
                error: xhr => Swal.fire('Error', xhr.responseJSON?.message || 'Failed', 'error')
            });
        };

        $('#addUserRoleForm').on('submit', e => handle(e, "{{ route('inventory.userAccess.store') }}", 'addUserRoleModal', userTable));
        $('#roleForm').on('submit', e => handle(e, "{{ route('inventory.roles.store') }}", 'roleModal', rolesTable));
        $('#accountForm').on('submit', e => handle(e, "{{ route('inventory.users.store') }}", 'accountModal', accountsTable));
        $('#menuForm').on('submit', e => handle(e, "{{ route('inventory.menus.store') }}", 'menuModal', menusTable));
        
        $('#permissionForm').on('submit', function(e) {
            e.preventDefault();
            const userId = $('#permission_user_id').val();
            const url = userId ? "{{ route('inventory.userMenus.update') }}" : "{{ route('inventory.roleMenus.update') }}";
            handle(e, url, 'permissionModal', null);
        });
    }

    function deleteItem(url, table, title, text) {
        Swal.fire({
            title: title, text: text, icon: 'warning', showCancelButton: true, confirmButtonColor: '#ef4444', confirmButtonText: 'Yes'
        }).then(res => {
            if (res.isConfirmed) {
                $.ajax({ url: url, type: 'DELETE', data: { _token: "{{ csrf_token() }}" },
                    success: res => { Swal.fire('Deleted', res.message, 'success'); table.ajax.reload(); },
                    error: xhr => Swal.fire('Error', xhr.responseJSON?.message || 'Failed', 'error')
                });
            }
        });
    }

    function openAddUserRoleModal() { $('#addUserRoleForm')[0].reset(); $('#user_allocation_id, #edit_mode_user_id').val(''); $('#user_id_select').val(null).empty().trigger('change'); $('#residueModalTitle').html('<i class="fa-solid fa-user-plus text-blue-600"></i> New Allocation'); $('#addUserRoleModal').removeClass('hidden').addClass('flex'); }
    function openAddRoleModal() { $('#roleForm')[0].reset(); $('#role_id_input').val(''); $('#roleModalTitle').html('<i class="fa-solid fa-plus text-purple-600"></i> Add Role'); $('#roleModal').removeClass('hidden').addClass('flex'); }
    function openAddAccountModal() { $('#accountForm')[0].reset(); $('#account_id_input').val(''); $('#password_hint').hide(); $('#accountModalTitle').html('<i class="fa-solid fa-user-plus text-amber-600"></i> Add Account'); $('#accountModal').removeClass('hidden').addClass('flex'); }
    function openAddMenuModal() { $('#menuForm')[0].reset(); $('#menu_id_input').val(''); $('#menu_is_active_input').prop('checked', true); $('#menuModalTitle').html('<i class="fa-solid fa-plus text-emerald-600"></i> Add Menu Item'); $('#menuModal').removeClass('hidden').addClass('flex'); }
    function closeModal(id) { $(`#${id}`).addClass('hidden').removeClass('flex'); }
</script>

<style>
    @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
    .animate-fadeIn { animation: fadeIn 0.3s ease-out forwards; }
    .custom-scrollbar-minimal::-webkit-scrollbar { width: 3px; }
    .custom-scrollbar-minimal::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar-minimal::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    .dark .custom-scrollbar-minimal::-webkit-scrollbar-thumb { background: #334155; }
    
    .select2-container--default .select2-selection--single { height: 38px !important; background-color: rgb(249 250 251) !important; border: 1px solid rgb(229 231 235) !important; border-radius: 0.375rem !important; display: flex !important; align-items: center !important; }
    .dark .select2-container--default .select2-selection--single { background-color: rgb(55 65 81) !important; border: 1px solid rgb(75 85 99) !important; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { color: rgb(17 24 39) !important; font-size: 0.875rem !important; font-weight: 700 !important; padding-left: 10px !important; }
    .dark .select2-container--default .select2-selection--single .select2-selection__rendered { color: white !important; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { top: 6px !important; }
</style>
@endpush
