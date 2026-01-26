<?php

namespace App\Models\InventoryModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasHashId;

class ProductRfq extends Model
{
    use HasFactory, HasHashId;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'inv_m_product_rfq';
    protected $appends = ['hash_id'];
    protected $hidden = ['id'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'material_spec_id',
        'unit_id',
        'thickness',
        'width',
        'length',
        'length_2',
        'pitch',
        'density',
        'weight_kg',
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
        'density' => 'float',
        'weight_kg' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the product that owns the RFQ data.
     */
    public function product()
    {
        return $this->belongsTo(\App\Models\Products::class, 'product_id');
    }

    /**
     * Get the material spec that owns the RFQ data.
     */
    public function materialSpec()
    {
        return $this->belongsTo(MaterialSpec::class, 'material_spec_id');
    }

    /**
     * Get the unit that owns the RFQ data.
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }
}
