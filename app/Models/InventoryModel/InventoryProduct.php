<?php

namespace App\Models\InventoryModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasHashId;

class InventoryProduct extends Model
{
    use HasFactory, HasHashId;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'inv_t_product_detail';
    protected $appends = ['hash_id'];
    protected $hidden = ['id'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'subcont_id',
        'coil_center_id',
        'material_spec_id',
        'unit_id',
        'rank_id',
        'revision',
        'thickness',
        'width',
        'length',
        'length_2',
        'pitch',
        'pcs_per_unit',
        'unit_per_car',
        'min_stock',
        'current_stock_qty',
        'trial_usage_qty',
        'is_active',
        'remark',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'thickness' => 'float',
        'width' => 'float',
        'length' => 'float',
        'length_2' => 'float',
        'pitch' => 'float',
        'current_stock_qty' => 'float',
        'trial_usage_qty' => 'float',
        'pcs_per_unit' => 'integer',
        'unit_per_car' => 'integer',
        'min_stock' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the product that owns the inventory detail.
     */
    public function product()
    {
        return $this->belongsTo(\App\Models\Products::class, 'product_id');
    }

    /**
     * Get the coil center that owns the product.
     */
    public function coilCenter()
    {
        return $this->belongsTo(CoilCenter::class, 'coil_center_id');
    }

    /**
     * Get the material spec that owns the product.
     */
    public function materialSpec()
    {
        return $this->belongsTo(MaterialSpec::class, 'material_spec_id');
    }

    /**
     * Get the unit that owns the product.
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    /**
     * Get the rank that owns the product.
     */
    public function rank()
    {
        return $this->belongsTo(Rank::class, 'rank_id');
    }

    /**
     * Get the sub contractor that owns the product.
     */
    public function subContractor()
    {
        return $this->belongsTo(SubContractor::class, 'subcont_id');
    }
}
