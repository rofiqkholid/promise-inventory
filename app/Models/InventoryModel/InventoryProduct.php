<?php

namespace App\Models\InventoryModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasHashId;

class InventoryProduct extends Model
{
    use HasFactory, HasHashId;

    protected $table = 'inv_t_product_detail';
    protected $appends = ['hash_id'];
    protected $hidden = ['id'];

    protected $fillable = [
        'product_id',
        'model_id',
        'material_spec_id',
        'unit_id',
        'rank_id',
        'revision_id',
        'thickness',
        'width',
        'length',
        'length_2',
        'pitch',
        'pcs_per_pitch',
        'pcs_per_unit',
        'unit_per_car',
        'min_stock',
        'current_stock_qty',
        'trial_usage_qty',
        'is_active',
        'remark',
        'density',
        'weight_kg',
        'net_weight',
        'material_price',
        'product_status',
        'product_status_remark',
    ];

    protected $casts = [
        'thickness' => 'float',
        'width' => 'float',
        'length' => 'float',
        'length_2' => 'float',
        'pitch' => 'float',
        'current_stock_qty' => 'float',
        'trial_usage_qty' => 'float',
        'pcs_per_unit' => 'integer',
        'pcs_per_pitch' => 'integer',
        'unit_per_car' => 'integer',
        'min_stock' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'density' => 'float',
        'weight_kg' => 'float',
        'net_weight' => 'float',
        'material_price' => 'float',
    ];

    public function product()
    {
        return $this->belongsTo(\App\Models\Products::class, 'product_id');
    }

    public function model()
    {
        return $this->belongsTo(\App\Models\Models::class, 'model_id');
    }

    public function materialSpec()
    {
        return $this->belongsTo(MaterialSpec::class, 'material_spec_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function rank()
    {
        return $this->belongsTo(Rank::class, 'rank_id');
    }

    public function revision()
    {
        return $this->belongsTo(Revision::class, 'revision_id');
    }
}
