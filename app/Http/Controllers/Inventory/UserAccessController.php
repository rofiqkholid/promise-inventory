<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserAccessController extends Controller
{
    public function index()
    {
        $roles = \App\Models\InventoryModel\InvRole::all();
        $allMenus = \App\Models\InventoryModel\Menu::whereNull('parent_id')->with('children')->orderBy('sort_order')->get();
        $departments = \App\Models\Department::orderBy('name')->get();
        return view('inventory.user_access.index', compact('roles', 'allMenus', 'departments'));
    }

    public function data(Request $request)
    {
        $draw = (int) $request->input('draw');
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $search = $request->input('search.value');
        
        $query = \App\Models\User::has('roles')->with('roles');

        $recordsTotal = (clone $query)->count();

        // Filtering
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $recordsFiltered = $query->count();

        // Sorting
        $order = $request->input('order.0');
        if ($order) {
            $colIdx = $order['column'];
            $dir = $order['dir'];
            if ($colIdx == 1) {
                $query->orderBy('name', $dir);
            } elseif ($colIdx == 2) {
                $query->orderBy('email', $dir);
            } else {
                $query->orderBy('created_at', 'desc');
            }
        } else {
             $query->orderBy('created_at', 'desc');
        }

        $data = $query->skip($start)->take($length)->get();
        
        $formattedData = $data->map(function ($row, $index) use ($start) {
            $badges = $row->roles->map(function($role) {
                $roleCode = $role->code ?? '';
                $roleName = $role->name ?? '';

                $colors = [
                    'admin' => 'bg-rose-50 text-rose-700 border-rose-100 dark:bg-rose-900/20 dark:text-rose-400 dark:border-rose-800/50',
                    'approver' => 'bg-purple-50 text-purple-700 border-purple-100 dark:bg-purple-900/20 dark:text-purple-400 dark:border-purple-800/50',
                    'checker' => 'bg-amber-50 text-amber-700 border-amber-100 dark:bg-amber-900/20 dark:text-amber-400 dark:border-amber-800/50',
                    'operator' => 'bg-primary-50 text-primary-700 border-primary-100 dark:bg-primary-900/20 dark:text-primary-400 dark:border-primary-800/50',
                    'viewer' => 'bg-slate-50 text-slate-600 border-slate-200 dark:bg-slate-900/20 dark:text-slate-400 dark:border-slate-800/50',
                ];
                $colorClass = $colors[$roleCode] ?? 'bg-gray-50 text-gray-600 border-gray-100 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700';
                return '<span class="px-2 py-1 border rounded-xs text-[10px] font-bold uppercase tracking-wide ' . $colorClass . '">' . $roleName . '</span>';
            })->implode(' ');
            
            $btn = '
                <div class="flex items-center justify-center gap-1.5">
                    <button class="edit-user-role-btn h-8 w-8 inline-flex items-center justify-center text-primary-600 rounded-xs bg-primary-50 hover:bg-primary-100 dark:bg-primary-900/20 dark:text-primary-400 dark:hover:bg-primary-900/30 transition-colors" data-id="' . $row->id . '" title="Configure Roles">
                        <i class="fa-solid fa-user-gear text-sm"></i>
                    </button>
                    <button class="user-permission-btn h-8 w-8 inline-flex items-center justify-center text-amber-600 rounded-xs bg-amber-50 hover:bg-amber-100 dark:bg-amber-900/20 dark:text-amber-400 dark:hover:bg-amber-900/30 transition-colors" data-id="' . $row->id . '" data-name="' . ($row->name ?? 'User') . '" title="Specific Menus">
                        <i class="fa-solid fa-key text-xs"></i>
                    </button>
                    <button class="delete-access-btn h-8 w-8 inline-flex items-center justify-center text-red-600 rounded-xs bg-red-50 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/30 transition-colors" data-id="' . $row->id . '" title="Revoke All">
                        <i class="fa-solid fa-trash-can text-sm"></i>
                    </button>
                </div>
            ';

            return [
                'DT_RowIndex' => $start + $index + 1,
                'user_name' => $row->name ?? '-',
                'user_email' => $row->email ?? '-',
                'role_badge' => '<div class="flex flex-wrap justify-center gap-1">' . $badges . '</div>',
                'action' => $btn
            ];
        });

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $formattedData,
        ]);
    }

    public function getUserRole($userId)
    {
        $user = \App\Models\User::with('roles')->findOrFail($userId);
        return response()->json([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'role_ids' => $user->roles->pluck('id')->toArray()
        ]);
    }

    public function searchUsers(Request $request)
    {
        $search = $request->term;
        $excludeId = $request->exclude_id; // For edit mode
        
        $query = \App\Models\User::where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%");
            });

        if (!$request->include_existing) {
            // No longer exclude entirely, but maybe exclude users who already have a specific role
            // if we want to avoid duplicates of the same user-role pair.
            // But usually a simple search is fine since we removed the unique index.
        }

        $users = $query->limit(10)->get(['id', 'name', 'email', 'nik']);

        $results = [];
        foreach ($users as $user) {
            $results[] = [
                'id' => $user->id,
                'text' => $user->name . ' (' . ($user->nik ?? 'No NIK') . ')'
            ];
        }

        return response()->json(['results' => $results]);
    }

    public function store(Request $request)
    {
        $userId = $request->user_id ?: $request->id;
        
        $request->merge(['user_id' => $userId]);

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_ids' => 'required|array',
            'role_ids.*' => 'exists:roles,id',
        ]);

        $user = \App\Models\User::findOrFail($userId);

        // Remove existing inventory scope roles for this user
        \DB::table('user_scope_roles')
            ->where('user_id', $userId)
            ->where('scope_id', 'app_inventory')
            ->delete();

        // Re-insert with the scope_id properly set
        $insertData = [];
        foreach ($request->role_ids as $roleId) {
            $insertData[] = [
                'user_id'  => $userId,
                'scope_id' => 'app_inventory',
                'role_id'  => $roleId,
            ];
        }

        if (!empty($insertData)) {
            \DB::table('user_scope_roles')->insert($insertData);
        }

        return response()->json(['success' => true, 'message' => 'User roles updated successfully.']);
    }

    public function destroy($userId)
    {
        \App\Models\User::findOrFail($userId);

        \DB::table('user_scope_roles')
            ->where('user_id', $userId)
            ->where('scope_id', 'app_inventory')
            ->delete();

        return response()->json(['success' => true, 'message' => 'All user access revoked.']);
    }

    public function userMenuData($userId)
    {
        $user = \App\Models\User::with(['specificMenus', 'roles.menus'])->findOrFail($userId);
        
        $specificMenuIds = $user->specificMenus->pluck('id')->toArray();
        $roleMenuIds = $user->roles->pluck('menus')->flatten()->pluck('id')->toArray();
        
        $activeMenuIds = array_unique(array_merge($specificMenuIds, $roleMenuIds));

        return response()->json(['active_menus' => array_values($activeMenuIds)]);
    }

    public function updateUserMenu(Request $request)
    {
        $user = \App\Models\User::findOrFail($request->user_id);
        $menuIds = $request->input('menu_ids', []);

        $user->specificMenus()->sync($menuIds);

        return response()->json(['success' => true, 'message' => 'User-specific permissions updated.']);
    }

    #Region Roles Management
    public function roleData(Request $request)
    {
        $draw = (int) $request->input('draw');
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $search = $request->input('search.value');
        
        $query = \App\Models\InventoryModel\InvRole::query();

        $recordsTotal = (clone $query)->count();

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $recordsFiltered = $query->count();

        $order = $request->input('order.0');
        if ($order) {
            $colIdx = $order['column'];
            $dir = $order['dir'];
            $columns = [1 => 'code', 2 => 'name', 3 => 'description'];
            if (isset($columns[$colIdx])) {
                $query->orderBy($columns[$colIdx], $dir);
            }
        }

        $data = $query->skip($start)->take($length)->get();

        $formattedData = $data->map(function ($row, $index) use ($start) {
            $btn = '
                <div class="flex items-center justify-center gap-1.5">
                    <button class="edit-role-btn h-8 w-8 inline-flex items-center justify-center text-primary-600 rounded-xs bg-primary-50 hover:bg-primary-100 dark:bg-primary-900/20 dark:text-primary-400 dark:hover:bg-primary-900/30 transition-colors" data-id="' . $row->id . '" title="Edit">
                        <i class="fa-solid fa-pen-to-square text-sm"></i>
                    </button>
                    <button class="permission-role-btn h-8 w-8 inline-flex items-center justify-center text-purple-600 rounded-xs bg-purple-50 hover:bg-purple-100 dark:bg-purple-900/20 dark:text-purple-400 dark:hover:bg-purple-900/30 transition-colors" data-id="' . $row->id . '" data-name="' . $row->name . '" title="Privileges">
                        <i class="fa-solid fa-key text-sm"></i>
                    </button>
                    <button class="delete-role-btn h-8 w-8 inline-flex items-center justify-center text-red-600 rounded-xs bg-red-50 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/30 transition-colors" data-id="' . $row->id . '" title="Delete">
                        <i class="fa-solid fa-trash-can text-sm"></i>
                    </button>
                </div>
            ';
            return [
                'DT_RowIndex' => $start + $index + 1,
                'code' => strtoupper($row->code),
                'name' => $row->name,
                'description' => $row->description ?? '-',
                'action' => $btn
            ];
        });

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $formattedData,
        ]);
    }

    public function storeRole(Request $request)
    {
        $id = $request->id;
        $request->validate([
            'role_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
        ]);

        \App\Models\InventoryModel\InvRole::updateOrCreate(
            ['id' => $id],
            [
                'role_name' => $request->role_name,
                'description' => $request->description,
                'scope_id'  => 'app_inventory',
            ]
        );

        return response()->json(['success' => true, 'message' => 'Role saved successfully.']);
    }

    public function getRole($id)
    {
        $role = \App\Models\InventoryModel\InvRole::findOrFail($id);
        return response()->json($role);
    }

    public function destroyRole($id)
    {
        $role = \App\Models\InventoryModel\InvRole::findOrFail($id);
        
        // Prevent deleting roles that are assigned to users in this scope
        if (\DB::table('user_scope_roles')->where('role_id', $id)->where('scope_id', 'app_inventory')->exists()) {
            return response()->json(['success' => false, 'message' => 'Role is currently assigned to users.'], 422);
        }

        $role->delete();
        return response()->json(['success' => true, 'message' => 'Role deleted successfully.']);
    }

    public function roleMenuData($roleId)
    {
        $rolePermissions = \DB::table('role_scope_permissions')
            ->where('role_id', $roleId)
            ->where('scope_id', 'app_inventory')
            ->get();

        $activeMenuIds = $rolePermissions->pluck('menu_id')->unique()->values()->toArray();
        $permissions = [];
        foreach ($rolePermissions as $rp) {
            if (!isset($permissions[$rp->menu_id])) {
                $permissions[$rp->menu_id] = [
                    'view' => false,
                    'create' => false,
                    'edit' => false,
                    'delete' => false,
                ];
            }
            if ($rp->permission_id == 1) $permissions[$rp->menu_id]['view'] = true;
            if ($rp->permission_id == 2) $permissions[$rp->menu_id]['create'] = true;
            if ($rp->permission_id == 3) $permissions[$rp->menu_id]['edit'] = true;
            if ($rp->permission_id == 4) $permissions[$rp->menu_id]['delete'] = true;
        }

        return response()->json([
            'active_menus' => $activeMenuIds,
            'permissions' => $permissions
        ]);
    }

    public function updateRoleMenu(Request $request)
    {
        $roleId = $request->role_id;
        $menuIds = $request->input('menu_ids', []);
        $permissions = $request->input('permissions', []);

        \DB::transaction(function () use ($roleId, $menuIds, $permissions) {
            // Delete existing inventory role permissions
            \DB::table('role_scope_permissions')
                ->where('role_id', $roleId)
                ->where('scope_id', 'app_inventory')
                ->delete();

            $insertData = [];
            foreach ($menuIds as $menuId) {
                $menuPerm = $permissions[$menuId] ?? [];
                
                $mappings = [
                    'view' => 1,
                    'create' => 2,
                    'edit' => 3,
                    'delete' => 4,
                ];

                foreach ($mappings as $key => $permId) {
                    if (isset($menuPerm[$key]) && $menuPerm[$key] == 1) {
                        $insertData[] = [
                            'role_id' => $roleId,
                            'scope_id' => 'app_inventory',
                            'menu_id' => $menuId,
                            'permission_id' => $permId,
                        ];
                    }
                }
            }

            if (!empty($insertData)) {
                \DB::table('role_scope_permissions')->insert($insertData);
            }
        });

        return response()->json(['success' => true, 'message' => 'Role permissions updated.']);
    }

    #Region Users Management
    public function userData(Request $request)
    {
        $draw = (int) $request->input('draw');
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $search = $request->input('search.value');
        
        $query = \App\Models\User::with('department');

        $recordsTotal = (clone $query)->count();

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%")
                  ->orWhereHas('department', function($dq) use ($search) {
                      $dq->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                  });
            });
        }

        $recordsFiltered = $query->count();

        $order = $request->input('order.0');
        if ($order) {
            $colIdx = $order['column'];
            $dir = $order['dir'];
            $columns = [1 => 'nik', 2 => 'name', 3 => 'email'];
            if (isset($columns[$colIdx])) {
                $query->orderBy($columns[$colIdx], $dir);
            }
        } else {
            $query->orderBy('name', 'asc');
        }

        $data = $query->skip($start)->take($length)->get();

        $formattedData = $data->map(function ($row, $index) use ($start) {
            $btn = '
                <div class="flex items-center justify-center gap-1.5">
                    <button class="edit-user-btn h-8 w-8 inline-flex items-center justify-center text-primary-600 rounded-xs bg-primary-50 hover:bg-primary-100 dark:bg-primary-900/20 dark:text-primary-400 dark:hover:bg-primary-900/30 transition-colors" data-id="' . $row->id . '" title="Edit">
                        <i class="fa-solid fa-user-pen text-sm"></i>
                    </button>
                    <button class="delete-user-btn h-8 w-8 inline-flex items-center justify-center text-red-600 rounded-xs bg-red-50 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/30 transition-colors" data-id="' . $row->id . '" title="Wipe">
                        <i class="fa-solid fa-user-minus text-sm"></i>
                    </button>
                </div>
            ';
            return [
                'DT_RowIndex' => $start + $index + 1,
                'nik' => $row->nik ?? '-',
                'name' => $row->name,
                'email' => $row->email,
                'department' => $row->department ? ($row->department->code . ' - ' . $row->department->name) : '-',
                'action' => $btn
            ];
        });

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $formattedData,
        ]);
    }

    public function getUser($id)
    {
        $user = \App\Models\User::findOrFail($id);
        return response()->json($user);
    }

    public function storeUser(Request $request)
    {
        $id = $request->id;
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $id,
            'nik' => 'required|string|max:50|unique:users,nik,' . $id,
            'id_dept' => 'nullable|integer|exists:departments,id',
        ];

        if (!$id || $request->filled('password')) {
            $rules['password'] = 'required|string|min:6|confirmed';
        }

        $request->validate($rules);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'nik' => $request->nik,
            'id_dept' => $request->id_dept,
        ];

        if ($request->filled('password')) {
            $data['password'] = \Illuminate\Support\Facades\Hash::make($request->password);
        }

        \App\Models\User::updateOrCreate(['id' => $id], $data);

        return response()->json(['success' => true, 'message' => 'User account saved successfully.']);
    }

    public function destroyUser($id)
    {
        if (auth()->user()->id == $id) {
            return response()->json(['success' => false, 'message' => 'Cannot delete your own account.'], 422);
        }

        $user = \App\Models\User::findOrFail($id);
        
        // Check if user has associated inventory records that would break on deletion
        // For now, only check app role
        if ($user->roles()->exists()) {
             return response()->json(['success' => false, 'message' => 'User has assigned roles. Revoke access first.'], 422);
        }

        $user->delete();
        return response()->json(['success' => true, 'message' => 'User account deleted.']);
    }
    #Region Menu Management
    public function menuData(Request $request)
    {
        $draw = (int) $request->input('draw');
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $search = $request->input('search.value');
        
        $query = \App\Models\InventoryModel\Menu::with('parent');

        $recordsTotal = (clone $query)->count();

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('route', 'like', "%{$search}%")
                  ->orWhereHas('parent', function($pq) use ($search) {
                      $pq->where('title', 'like', "%{$search}%");
                  });
            });
        }

        $recordsFiltered = $query->count();

        $order = $request->input('order.0');
        if ($order) {
            $colIdx = $order['column'];
            $dir = $order['dir'];
            $columns = [1 => 'title', 2 => 'parent_id', 5 => 'sort_order'];
            if (isset($columns[$colIdx])) {
                if ($columns[$colIdx] === 'parent_id') {
                    // Sorting by parent title would be better but simple parent_id for now
                    $query->orderBy('parent_id', $dir);
                } else {
                    $query->orderBy($columns[$colIdx], $dir);
                }
            }
        } else {
            $query->orderBy('sort_order', 'asc');
        }

        $data = $query->skip($start)->take($length)->get();

        $formattedData = $data->map(function ($row, $index) use ($start) {
            $parentTitle = $row->parent ? $row->parent->title : '<span class="text-gray-400 italic font-normal">None (Root)</span>';
            $iconHtml = '<div class="flex items-center justify-center bg-gray-50 dark:bg-gray-700/50 w-8 h-8 rounded-md"><i class="' . $row->icon . ' text-blue-500"></i></div>';
            
            $statusBadge = $row->is_active 
                ? '<span class="px-2 py-1 border border-emerald-100 bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400 dark:border-emerald-800/50 text-[10px] font-bold rounded-xs uppercase tracking-wide">Active</span>'
                : '<span class="px-2 py-1 border border-gray-100 bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-500 text-[10px] font-bold rounded-xs uppercase tracking-wide">Inactive</span>';

            $btn = '
                <div class="flex items-center justify-center gap-1.5">
                    <button class="edit-menu-btn h-8 w-8 inline-flex items-center justify-center text-primary-600 rounded-xs bg-primary-50 hover:bg-primary-100 dark:bg-primary-900/20 dark:text-primary-400 dark:hover:bg-primary-900/30 transition-colors" data-id="' . $row->id . '" title="Edit">
                        <i class="fa-solid fa-pen-to-square text-sm"></i>
                    </button>
                    <button class="delete-menu-btn h-8 w-8 inline-flex items-center justify-center text-red-600 rounded-xs bg-red-50 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/30 transition-colors" data-id="' . $row->id . '" title="Delete">
                        <i class="fa-solid fa-trash-can text-sm"></i>
                    </button>
                </div>
            ';
            return [
                'DT_RowIndex' => $start + $index + 1,
                'title' => '<div><div class="font-black text-gray-900 dark:text-white">' . $row->title . '</div><div class="text-[10px] text-gray-400 font-mono italic">' . $row->route . '</div></div>',
                'parent' => $parentTitle,
                'status' => $statusBadge,
                'icon' => $iconHtml,
                'order' => '<span class="font-bold text-gray-500">' . $row->sort_order . '</span>',
                'action' => $btn
            ];
        });

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $formattedData,
        ]);
    }

    public function getMenu($id)
    {
        $menu = \App\Models\InventoryModel\Menu::findOrFail($id);
        return response()->json($menu);
    }

    public function storeMenu(Request $request)
    {
        $id = $request->id;
        $request->validate([
            'title' => 'required|string|max:255',
            'route' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
            'order' => 'required|integer',
            'parent_id' => 'nullable|exists:menus,id',
            'is_active' => 'nullable|boolean',
        ]);

        \App\Models\InventoryModel\Menu::updateOrCreate(
            ['id' => $id],
            [
                'title' => $request->title,
                'route' => $request->route,
                'icon' => $request->icon,
                'sort_order' => $request->order,
                'parent_id' => $request->parent_id,
                'is_active' => $request->has('is_active') ? true : false,
            ]
        );

        return response()->json(['success' => true, 'message' => 'Menu structure saved successfully.']);
    }

    public function destroyMenu($id)
    {
        $menu = \App\Models\InventoryModel\Menu::findOrFail($id);
        
        if ($menu->children()->exists()) {
            return response()->json(['success' => false, 'message' => 'Cannot delete menu with sub-items.'], 422);
        }

        $menu->delete();
        return response()->json(['success' => true, 'message' => 'Menu item deleted.']);
    }
    #Endregion
}
