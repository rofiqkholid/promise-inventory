<?php

namespace App\Models\InventoryModel\Tool;

use Illuminate\Database\Eloquent\Model;

class TolFastStock extends Model
{
    protected $table = 'tol_t_fast_stock';
    protected $fillable = ['tool_id', 'location_id', 'current_qty', 'last_updated_at'];

    protected $casts = [
        'current_qty'     => 'integer',
        'last_updated_at' => 'datetime',
    ];

    public function tool()
    {
        return $this->belongsTo(TolTool::class, 'tool_id');
    }

    public function location()
    {
        return $this->belongsTo(TolLocation::class, 'location_id');
    }

    /** True jika stok di bawah atau sama dengan batas minimum */
    public function getBelowLimitAttribute(): bool
    {
        return $this->tool && $this->current_qty <= $this->tool->limit_stock;
    }
}
