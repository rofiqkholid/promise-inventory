<?php

namespace App\Models\InventoryModel\Tool;

use Illuminate\Database\Eloquent\Model;

class TolStoFast extends Model
{
    protected $table = 'tol_t_sto_fast';
    protected $fillable = [
        'event_id', 'tool_id', 'location_id',
        'system_qty', 'physical_qty', 'adjustment_qty',
        'note'
    ];

    protected $casts = [
        'system_qty'     => 'integer',
        'physical_qty'   => 'integer',
        'adjustment_qty' => 'integer',
    ];

    public function event()
    {
        return $this->belongsTo(TolStoEvent::class, 'event_id');
    }

    public function tool()
    {
        return $this->belongsTo(TolTool::class, 'tool_id');
    }

    public function location()
    {
        return $this->belongsTo(TolLocation::class, 'location_id');
    }
}
