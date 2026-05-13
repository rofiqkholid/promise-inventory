<?php

namespace App\Models\InventoryModel\Tool;

use Illuminate\Database\Eloquent\Model;

class TolTool extends Model
{
    protected $table = 'tol_m_tools';
    protected $fillable = [
        'category_id', 'sketch_id', 'location_id', 'name', 'brand', 'spec_code',
        'diameter', 'length', 'material_type', 'hrc',
        'uom', 'pcs_per_unit', 'price_per_unit',
        'limit_stock', 'qty_min', 'qty_max', 'std_lifetime_yrs', 'is_active',
    ];

    protected $casts = [
        'diameter'        => 'float',
        'length'          => 'float',
        'price_per_unit'  => 'float',
        'pcs_per_unit'    => 'integer',
        'limit_stock'     => 'integer',
        'qty_min'         => 'integer',
        'qty_max'         => 'integer',
        'std_lifetime_yrs'=> 'integer',
        'is_active'       => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(TolCategory::class, 'category_id');
    }

    public function sketch()
    {
        return $this->belongsTo(TolSketch::class, 'sketch_id');
    }

    public function location()
    {
        return $this->belongsTo(TolLocation::class, 'location_id');
    }

    public function fastStock()
    {
        return $this->hasMany(TolFastStock::class, 'tool_id');
    }

    public function slowBatches()
    {
        return $this->hasMany(TolSlowBatch::class, 'tool_id');
    }

    public function transactions()
    {
        return $this->hasMany(TolTransaction::class, 'tool_id');
    }
}
