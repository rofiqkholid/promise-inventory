<?php

namespace App\Models\InventoryModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasHashId;

class MaterialSpec extends Model
{
    use HasFactory, HasHashId;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'inv_m_material_spec';
    protected $appends = ['hash_id'];
    protected $hidden = ['id'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'spec_name',
        'coating_type',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


}
