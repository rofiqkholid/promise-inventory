<?php

namespace App\Models\InventoryModel;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 'inv_m_menus';
    protected $fillable = ['title', 'route', 'icon', 'order', 'is_active', 'parent_id'];

    public function roles()
    {
        return $this->belongsToMany(InvRole::class, 'inv_role_menus', 'menu_id', 'role_id');
    }

    public function parent()
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Menu::class, 'parent_id')->orderBy('order');
    }
}
