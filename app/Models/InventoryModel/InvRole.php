<?php

namespace App\Models\InventoryModel;

use Illuminate\Database\Eloquent\Model;

class InvRole extends Model
{
    protected $table = 'inv_m_roles';

    protected $fillable = [
        'name',
        'code',
        'description',
    ];

    public function menus()
    {
        return $this->belongsToMany(Menu::class, 'inv_role_menus', 'role_id', 'menu_id')
            ->orderBy('inv_m_menus.order');
    }
}
