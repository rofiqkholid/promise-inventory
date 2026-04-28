<?php

namespace App\Models\InventoryModel\Material;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasHashId;

class VaveBase extends Model
{
    use HasFactory, HasHashId;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'inv_m_vave_base';
    protected $appends = ['hash_id'];
    protected $hidden = ['id'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'base_name',
        'is_active',
        'material_spec_id',
        'unit_id',
        'thickness',
        'width',
        'length',
        'length_2',
        'pitch',
        'density',
        'pcs_per_unit',
        'pcs_per_pitch',
        'weight_kg',
        'net_weight',
        'material_price',
        'vave_base_suffix_id',
        'effective_from',
        'effective_to',
        'remark',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'thickness' => 'float',
        'width' => 'float',
        'length' => 'float',
        'length_2' => 'float',
        'pitch' => 'float',
        'density' => 'float',
        'pcs_per_unit' => 'integer',
        'pcs_per_pitch' => 'integer',
        'weight_kg'      => 'float',
        'net_weight'     => 'float',
        'material_price' => 'float',
        'effective_from' => 'integer',
        'effective_to'   => 'integer',
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
    ];

    /**
     * Get the product that owns the VAVE base data.
     */
    public function product()
    {
        return $this->belongsTo(\App\Models\Products::class, 'product_id');
    }

    /**
     * Get the material spec that owns the VAVE base data.
     */
    public function materialSpec()
    {
        return $this->belongsTo(MaterialSpec::class, 'material_spec_id');
    }

    /**
     * Get the unit that owns the VAVE base data.
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    /**
     * Get the suffix associated with the VAVE base data.
     */
    public function suffix()
    {
        return $this->belongsTo(VaveBaseSuffix::class, 'vave_base_suffix_id');
    }
}
