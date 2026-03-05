<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserAccessController extends Controller
{
    public function index()
    {
        $roles = \App\Models\InventoryModel\InvRole::all();
        $allMenus = \App\Models\InventoryModel\Menu::whereNull('parent_id')->with('children')->orderBy('order')->get();
        return view('inventory.user_access.index', compact('roles', 'allMenus'));
    }

    public function data(Request $request)
    {
        $draw = (int) $request->input('draw');
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $search = $request->input('search.value');
        
        $query = \App\Models\InventoryModel\UserAppRole::with(['user', 'role']);

        $recordsTotal = (clone $query)->count();

        // Filtering
        if (!empty($search)) {
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->orWhereHas('role', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $recordsFiltered = $query->count();

        // Sorting
        $order = $request->input('order.0');
        if ($order) {
            $colIdx = $order['column'];
            $dir = $order['dir'];
            if ($colIdx == 1) {
                $query->join('users', 'inv_user_roles.user_id', '=', 'users.id')
                      ->orderBy('users.name', $dir)
                      ->select('inv_user_roles.*'); 
            } elseif ($colIdx == 2) {
                $query->join('users', 'inv_user_roles.user_id', '=', 'users.id')
                      ->orderBy('users.email', $dir)
                      ->select('inv_user_roles.*');
            } elseif ($colIdx == 3) {
                $query->join('inv_m_roles', 'inv_user_roles.role_id', '=', 'inv_m_roles.id')
                      ->orderBy('inv_m_roles.name', $dir)
                      ->select('inv_user_roles.*');
            } else {
                $query->orderBy('created_at', 'desc');
            }
        } else {
             $query->orderBy('created_at', 'desc');
        }

        $data = $query->skip($start)->take($length)->get();
        
        $formattedData = $data->map(function ($row, $index) use ($start) {
            $roleCode = $row->role->code ?? '';
            $roleName = $row->role->name ?? '';

            $colors = [
                'admin' => 'bg-rose-50 text-rose-700 border-rose-100 dark:bg-rose-900/20 dark:text-rose-400 dark:border-rose-800/50',
                'approver' => 'bg-purple-50 text-purple-700 border-purple-100 dark:bg-purple-900/20 dark:text-purple-400 dark:border-purple-800/50',
                'checker' => 'bg-amber-50 text-amber-700 border-amber-100 dark:bg-amber-900/20 dark:text-amber-400 dark:border-amber-800/50',
                'operator' => 'bg-primary-50 text-primary-700 border-primary-100 dark:bg-primary-900/20 dark:text-primary-400 dark:border-primary-800/50',
                'viewer' => 'bg-slate-50 text-slate-600 border-slate-200 dark:bg-slate-900/20 dark:text-slate-400 dark:border-slate-800/50',
            ];
            $colorClass = $colors[$roleCode] ?? 'bg-gray-50 text-gray-600 border-gray-100 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700';
            $badge = '<span class="px-2 py-1 border rounded-xs text-[10px] font-bold uppercase tracking-wide ' . $colorClass . '">' . $roleName . '</span>';
            
            $btn = '
                <div class="flex items-center justify-center gap-1.5">
                    <button class="edit-user-role-btn h-8 w-8 inline-flex items-center justify-center text-primary-600 rounded-xs bg-primary-50 hover:bg-primary-100 dark:bg-primary-900/20 dark:text-primary-400 dark:hover:bg-primary-900/30 transition-colors" data-id="' . $row->id . '" title="Edit">
                        <i class="fa-solid fa-pen-to-square text-sm"></i>
                    </button>
                    <button class="user-permission-btn h-8 w-8 inline-flex items-center justify-center text-amber-600 rounded-xs bg-amber-50 hover:bg-amber-100 dark:bg-amber-900/20 dark:text-amber-400 dark:hover:bg-amber-900/30 transition-colors" data-id="' . $row->user_id . '" data-name="' . ($row->user->name ?? 'User') . '" title="Permissions">
                        <i class="fa-solid fa-key text-xs"></i>
                    </button>
                    <button class="delete-access-btn h-8 w-8 inline-flex items-center justify-center text-red-600 rounded-xs bg-red-50 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/30 transition-colors" data-id="' . $row->id . '" title="Revoke">
                        <i class="fa-solid fa-trash-can text-sm"></i>
                    </button>
                </div>
            ';

            return [
                'DT_RowIndex' => $start + $index + 1,
                'user_name' => $row->user->name ?? '-',
                'user_email' => $row->user->email ?? '-',
                'role_badge' => $badge,
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

    public function getUserRole($id)
    {
        $allocation = \App\Models\InventoryModel\UserAppRole::with('user')->findOrFail($id);
        return response()->json([
            'id' => $allocation->id,
            'user_id' => $allocation->user_id,
            'user_name' => $allocation->user->name ?? 'Unknown',
            'role_id' => $allocation->role_id
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
            $existingUserIds = \App\Models\InventoryModel\UserAppRole::pluck('user_id');
            if ($excludeId) {
                $existingUserIds = $existingUserIds->reject(fn($id) => $id == $excludeId);
            }
            $query->whereNotIn('id', $existingUserIds);
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
        $id = $request->id;
        $request->validate([
            'user_id' => 'required|exists:users,id|unique:inv_user_roles,user_id,' . $id,
            'role_id' => 'required|exists:inv_m_roles,id',
        ]);

        \App\Models\InventoryModel\UserAppRole::updateOrCreate(
            ['id' => $id],
            [
                'user_id' => $request->user_id,
                'role_id' => $request->role_id,
            ]
        );

        return response()->json(['success' => true, 'message' => 'User role allocation saved.']);
    }

    public function destroy($id)
    {
        $role = \App\Models\InventoryModel\UserAppRole::findOrFail($id);
        $role->delete();

        return response()->json(['success' => true, 'message' => 'User access revoked.']);
    }

    public function userMenuData($userId)
    {
        $user = \App\Models\User::with(['specificMenus', 'appRole.role.menus'])->findOrFail($userId);
        
        $specificMenuIds = $user->specificMenus->pluck('id')->toArray();
        $roleMenuIds = optional($user->appRole->role->menus ?? null)->pluck('id')->toArray() ?? [];
        
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
        $roles = \App\Models\InventoryModel\InvRole::all();
        $formattedData = $roles->map(function ($row, $index) {
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
                'DT_RowIndex' => $index + 1,
                'code' => strtoupper($row->code),
                'name' => $row->name,
                'description' => $row->description ?? '-',
                'action' => $btn
            ];
        });

        return response()->json(['data' => $formattedData]);
    }

    public function storeRole(Request $request)
    {
        $id = $request->id;
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:inv_m_roles,code,' . $id,
            'description' => 'nullable|string',
        ]);

        \App\Models\InventoryModel\InvRole::updateOrCreate(
            ['id' => $id],
            $request->only(['name', 'code', 'description'])
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
        
        // Prevent deleting core roles if needed, or check if used
        if (\App\Models\InventoryModel\UserAppRole::where('role_id', $id)->exists()) {
            return response()->json(['success' => false, 'message' => 'Role is currently assigned to users.'], 422);
        }

        $role->delete();
        return response()->json(['success' => true, 'message' => 'Role deleted successfully.']);
    }

    public function roleMenuData($roleId)
    {
        $role = \App\Models\InventoryModel\InvRole::with('menus')->findOrFail($roleId);
        $activeMenuIds = $role->menus->pluck('id')->toArray();

        return response()->json(['active_menus' => array_values($activeMenuIds)]);
    }

    public function updateRoleMenu(Request $request)
    {
        $role = \App\Models\InventoryModel\InvRole::findOrFail($request->role_id);
        $menuIds = $request->input('menu_ids', []);

        $role->menus()->sync($menuIds);

        return response()->json(['success' => true, 'message' => 'Role permissions updated.']);
    }

    #Region Users Management
    public function userData(Request $request)
    {
        $users = \App\Models\User::orderBy('name')->get();
        $formattedData = $users->map(function ($row, $index) {
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
                'DT_RowIndex' => $index + 1,
                'nik' => $row->nik ?? '-',
                'name' => $row->name,
                'email' => $row->email,
                'action' => $btn
            ];
        });

        return response()->json(['data' => $formattedData]);
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
        ];

        if (!$id || $request->filled('password')) {
            $rules['password'] = 'required|string|min:6|confirmed';
        }

        $request->validate($rules);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'nik' => $request->nik,
        ];

        if ($request->filled('password')) {
            $data['password'] = \Illuminate\Support\Facades\Hash::make($request->password);
        }

        \App\Models\User::updateOrCreate(['id' => $id], $data);

        return response()->json(['success' => true, 'message' => 'User account saved successfully.']);
    }

    public function destroyUser($id)
    {
        if (auth()->id() == $id) {
            return response()->json(['success' => false, 'message' => 'Cannot delete your own account.'], 422);
        }

        $user = \App\Models\User::findOrFail($id);
        
        // Check if user has associated inventory records that would break on deletion
        // For now, only check app role
        if ($user->appRole()->exists()) {
             return response()->json(['success' => false, 'message' => 'User has an assigned role. Revoke access first.'], 422);
        }

        $user->delete();
        return response()->json(['success' => true, 'message' => 'User account deleted.']);
    }
    #Region Menu Management
    public function menuData(Request $request)
    {
        $menus = \App\Models\InventoryModel\Menu::with('parent')->orderBy('order')->get();
        $formattedData = $menus->map(function ($row, $index) {
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
                'DT_RowIndex' => $index + 1,
                'title' => '<div><div class="font-black text-gray-900 dark:text-white">' . $row->title . '</div><div class="text-[10px] text-gray-400 font-mono italic">' . $row->route . '</div></div>',
                'parent' => $parentTitle,
                'status' => $statusBadge,
                'icon' => $iconHtml,
                'order' => '<span class="font-bold text-gray-500">' . $row->order . '</span>',
                'action' => $btn
            ];
        });

        return response()->json(['data' => $formattedData]);
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
            'parent_id' => 'nullable|exists:inv_m_menus,id',
            'is_active' => 'nullable|boolean',
        ]);

        \App\Models\InventoryModel\Menu::updateOrCreate(
            ['id' => $id],
            [
                'title' => $request->title,
                'route' => $request->route,
                'icon' => $request->icon,
                'order' => $request->order,
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
