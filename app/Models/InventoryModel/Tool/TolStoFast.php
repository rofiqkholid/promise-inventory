<?php

namespace App\Models\InventoryModel\Tool;

use Illuminate\Database\Eloquent\Model;

class TolStoFast extends Model
{
    protected $table = 'tol_t_sto_fast';
    protected $fillable = [
        'sto_date', 'tool_id', 'location_id',
        'system_qty', 'physical_qty', 'adjustment_qty',
        'note', 'conducted_by', 'approved_by', 'status',
    ];

    protected $casts = [
        'sto_date'       => 'date',
        'system_qty'     => 'integer',
        'physical_qty'   => 'integer',
        'adjustment_qty' => 'integer',
    ];

    public function tool()
    {
        return $this->belongsTo(TolTool::class, 'tool_id');
    }

    public function location()
    {
        return $this->belongsTo(TolLocation::class, 'location_id');
    }

    public function conductor()
    {
        return $this->belongsTo(\App\Models\User::class, 'conducted_by');
    }

    public function approver()
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }
}
