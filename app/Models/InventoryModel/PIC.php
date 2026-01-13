<?php

namespace App\Models\InventoryModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasHashId;

class PIC extends Model
{
    use HasFactory, HasHashId;

    protected $table = 'inv_m_pic';
    protected $appends = ['hash_id'];
    protected $hidden = ['id'];

    protected $fillable = [
        'name',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];
}
