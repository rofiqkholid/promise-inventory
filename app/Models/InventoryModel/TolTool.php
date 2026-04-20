<?php

namespace App\Models\InventoryModel;

use Illuminate\Database\Eloquent\Model;

class TolTool extends Model
{
    protected $table = 'tol_m_tool';
    protected $fillable = [
        'category_id', 'name', 'brand', 'spec_code', 'diameter', 
        'length', 'material_type', 'hrc', 'uom', 'pcs_per_unit'
    ];

    public function category()
    {
        return $this->belongsTo(TolCategory::class, 'category_id');
    }

    public function inventories()
    {
        return $this->hasMany(TolInventory::class, 'tool_id');
    }
}
