<?php

namespace App\Models\InventoryModel;

use Illuminate\Database\Eloquent\Model;

class InvUserMenu extends Model
{
    protected $table = 'inv_user_menus';
    protected $fillable = ['user_id', 'menu_id'];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }
}
