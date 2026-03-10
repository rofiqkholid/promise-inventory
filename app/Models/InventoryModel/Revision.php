<?php

namespace App\Models\InventoryModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasHashId;

class Revision extends Model
{
    use HasFactory, HasHashId;

    protected $table = 'inv_m_revision';
    protected $appends = ['hash_id'];
    protected $hidden = ['id'];

    protected $fillable = [
        'code',
        'group_name',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];
}
