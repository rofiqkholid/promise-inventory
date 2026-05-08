<?php

namespace App\Models\InventoryModel\Material;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasHashId;

class VaveBaseSuffix extends Model
{
    use HasFactory, HasHashId;

    protected $table = 'inv_m_vave_base_suffix';
    protected $appends = ['hash_id'];
    protected $hidden = ['id'];

    protected $fillable = [
        'base_type',
        'name',
        'remark',
        'is_active',
    ];
}
