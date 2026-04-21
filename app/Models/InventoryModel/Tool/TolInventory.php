<?php

namespace App\Models\InventoryModel\Tool;

use Illuminate\Database\Eloquent\Model;

class TolInventory extends Model
{
    protected $table = 'tol_m_inventories';
    protected $fillable = [
        'tool_id', 'moving_status', 'location', 'stock_limit', 
        'current_stock', 'price_per_unit', 'asset_value', 'purchase_date', 
        'std_lifetime_yrs', 'last_sto_date', 'physical_check'
    ];

    public function tool()
    {
        return $this->belongsTo(TolTool::class, 'tool_id');
    }
}
