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
                'admin' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
                'approver' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
                'checker' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
                'operator' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
                'viewer' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
            ];
            $color = $colors[$roleCode] ?? 'bg-slate-100 text-slate-800';
            $badge = '<span class="px-2.5 py-0.5 rounded text-xs font-black ' . $color . '">' . $roleName . '</span>';
            
            $btn = '
                <div class="flex items-center justify-center gap-2">
                    <button class="edit-user-role-btn text-blue-600 hover:text-blue-900 dark:text-blue-400" data-id="' . $row->id . '">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </button>
                    <button class="user-permission-btn text-amber-600 hover:text-amber-900 dark:text-amber-400" data-id="' . $row->user_id . '" data-name="' . ($row->user->name ?? 'User') . '">
                        <i class="fa-solid fa-key"></i>
                    </button>
                    <button class="delete-access-btn text-red-600 hover:text-red-900 dark:text-red-400" data-id="' . $row->id . '">
                        <i class="fa-solid fa-trash"></i>
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
                <div class="flex items-center justify-center gap-2">
                    <button class="edit-role-btn text-blue-600 hover:text-blue-900 dark:text-blue-400" data-id="' . $row->id . '">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </button>
                    <button class="permission-role-btn text-purple-600 hover:text-purple-900 dark:text-purple-400" data-id="' . $row->id . '" data-name="' . $row->name . '">
                        <i class="fa-solid fa-key"></i>
                    </button>
                    <button class="delete-role-btn text-red-600 hover:text-red-900 dark:text-red-400" data-id="' . $row->id . '">
                        <i class="fa-solid fa-trash"></i>
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
                <div class="flex items-center justify-center gap-2">
                    <button class="edit-user-btn text-blue-600 hover:text-blue-900 dark:text-blue-400" data-id="' . $row->id . '">
                        <i class="fa-solid fa-user-pen"></i>
                    </button>
                    <button class="delete-user-btn text-red-600 hover:text-red-900 dark:text-red-400" data-id="' . $row->id . '">
                        <i class="fa-solid fa-user-minus"></i>
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
                ? '<span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 text-[10px] font-black rounded uppercase">Active</span>'
                : '<span class="px-2 py-0.5 bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-500 text-[10px] font-black rounded uppercase">Inactive</span>';

            $btn = '
                <div class="flex items-center justify-center gap-2">
                    <button class="edit-menu-btn text-blue-600 hover:text-blue-900 dark:text-blue-400" data-id="' . $row->id . '">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </button>
                    <button class="delete-menu-btn text-red-600 hover:text-red-900 dark:text-red-400" data-id="' . $row->id . '">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            ';
            return [
                'DT_RowIndex' => $index + 1,
                'title' => '<div><div class="font-black text-gray-900 dark:text-white">' . $row->title . '</div><div class="text-[10px] text-gray-400 font-mono italic">' . $row->route . '</div></div>',
                'parent' => $parentTitle,
                'status' => $statusBadge,
                'icon' => $iconHtml,
                'order' => '<span class="font-black text-gray-500">' . $row->order . '</span>',
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
