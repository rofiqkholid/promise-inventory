<?php

namespace App\Models\InventoryModel;

use Illuminate\Database\Eloquent\Model;

class UserAppRole extends Model
{
    protected $table = 'inv_user_roles';

    protected $fillable = [
        'user_id',
        'role_id',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function role()
    {
        return $this->belongsTo(InvRole::class, 'role_id');
    }
}
