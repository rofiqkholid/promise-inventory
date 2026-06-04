<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'nik',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getAuthPassword()
    {
        return $this->password;
    }

    public function getAuthIdentifierName()
    {
        return 'nik';
    }

    public function roles()
    {
        return $this->belongsToMany(\App\Models\InventoryModel\InvRole::class, 'user_scope_roles', 'user_id', 'role_id')
            ->wherePivot('scope_id', 'app_inventory');
    }

    public function hasAppRole($roleCode)
    {
        return $this->roles->contains('code', $roleCode);
    }

    public function hasRole($roleString)
    {
        $roles = explode('|', $roleString);
        foreach ($roles as $role) {
            if ($this->hasAppRole(trim($role))) {
                return true;
            }
        }
        return false;
    }

    public function specificMenus()
    {
        return $this->belongsToMany(\App\Models\InventoryModel\Menu::class, 'user_scope_permissions', 'user_id', 'menu_id')
            ->wherePivot('scope_id', 'app_inventory');
    }

    public function hasMenuPermission($routeName, $permissionColumn = 'can_view')
    {
        if ($this->hasRole('admin')) {
            return true;
        }

        $permissionMap = [
            'can_view' => 'view',
            'can_create' => 'create',
            'can_edit' => 'edit',
            'can_delete' => 'delete',
            'can_upload' => 'upload',
            'can_download' => 'download',
        ];
        $permName = $permissionMap[$permissionColumn] ?? 'view';

        // 1. Direct User Override Check
        $directOverrides = \DB::table('user_scope_permissions')
            ->join('permissions', 'permissions.id', '=', 'user_scope_permissions.permission_id')
            ->join('menus', 'menus.id', '=', 'user_scope_permissions.menu_id')
            ->where('user_scope_permissions.user_id', $this->id)
            ->where('user_scope_permissions.scope_id', 'app_inventory')
            ->where('permissions.permission_name', $permName)
            ->where('menus.is_active', 1)
            ->select('menus.route', 'user_scope_permissions.access_type')
            ->get();

        foreach ($directOverrides as $override) {
            if ($this->routeMatches($routeName, $override->route)) {
                return $override->access_type === 'ALLOW';
            }
        }

        // 2. Role-based Permission Check
        $roleRoutes = \DB::table('user_scope_roles')
            ->join('role_scope_permissions', function($join) {
                $join->on('user_scope_roles.role_id', '=', 'role_scope_permissions.role_id')
                     ->on('user_scope_roles.scope_id', '=', 'role_scope_permissions.scope_id');
            })
            ->join('permissions', 'permissions.id', '=', 'role_scope_permissions.permission_id')
            ->join('menus', 'menus.id', '=', 'role_scope_permissions.menu_id')
            ->where('user_scope_roles.user_id', $this->id)
            ->where('user_scope_roles.scope_id', 'app_inventory')
            ->where('permissions.permission_name', $permName)
            ->where('menus.is_active', 1)
            ->pluck('menus.route')
            ->filter()
            ->toArray();

        return $this->routeMatchesAny($routeName, $roleRoutes);
    }

    private function routeMatches($routeName, $allowed): bool
    {
        if (!$allowed) return false;
        if ($routeName === $allowed) return true;
        if (str_starts_with($routeName, $allowed . '.')) return true;

        if (str_ends_with($allowed, '.index')) {
            $base = substr($allowed, 0, -6);
            if ($routeName === $base || str_starts_with($routeName, $base . '.')) {
                return true;
            }

            if ($allowed === 'inventory.userAccess.index') {
                $userAccessPrefixes = [
                    'inventory.roles.',
                    'inventory.menus.',
                    'inventory.users.',
                    'inventory.roleMenus.',
                    'inventory.userMenus.'
                ];
                
                foreach ($userAccessPrefixes as $prefix) {
                    if (str_starts_with($routeName, $prefix)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    private function routeMatchesAny($routeName, array $allowedRoutes): bool
    {
        foreach ($allowedRoutes as $allowed) {
            if ($this->routeMatches($routeName, $allowed)) {
                return true;
            }
        }
        return false;
    }
}
