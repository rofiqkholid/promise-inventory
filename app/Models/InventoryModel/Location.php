<?php

namespace App\Models\InventoryModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasHashId;

class Location extends Model
{
    use HasFactory, HasHashId;

    protected $table = 'inv_m_locations';
    protected $appends = ['hash_id'];
    protected $hidden = [];

    protected $fillable = [
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
