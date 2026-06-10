<?php

namespace App\Models\InventoryModel;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 'menus';
    protected $fillable = ['title', 'route', 'icon', 'sort_order', 'is_active', 'parent_id', 'is_visible', 'scope_id'];

    protected static function booted()
    {
        static::addGlobalScope('inventory_scope', function ($builder) {
            $builder->where('menus.scope_id', 'app_inventory');
        });
    }

    public function roles()
    {
        return $this->belongsToMany(InvRole::class, 'role_scope_permissions', 'menu_id', 'role_id')
            ->wherePivot('scope_id', 'app_inventory');
    }

    public function parent()
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Menu::class, 'parent_id')->with('children')->orderBy('sort_order');
    }

    public function userSpecific()
    {
        return $this->belongsToMany(\App\Models\User::class, 'user_scope_permissions', 'menu_id', 'user_id')
            ->wherePivot('scope_id', 'app_inventory');
    }
}
