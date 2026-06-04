<?php

namespace App\Models\InventoryModel;

use Illuminate\Database\Eloquent\Model;

class InvRole extends Model
{
    protected $table = 'roles';

    protected $fillable = [
        'role_name',
    ];

    public function getNameAttribute()
    {
        return $this->role_name;
    }

    public function getCodeAttribute()
    {
        $name = $this->role_name;
        if (str_starts_with($name, 'Inv ')) {
            $name = substr($name, 4);
        }
        return str_replace(' ', '_', strtolower($name));
    }

    public function menus()
    {
        return $this->belongsToMany(Menu::class, 'role_scope_permissions', 'role_id', 'menu_id')
            ->wherePivot('scope_id', 'app_inventory')
            ->orderBy('menus.sort_order');
    }
}
