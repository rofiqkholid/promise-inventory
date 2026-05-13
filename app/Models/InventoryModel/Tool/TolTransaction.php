<?php

namespace App\Models\InventoryModel\Tool;

use Illuminate\Database\Eloquent\Model;

class TolTransaction extends Model
{
    protected $table = 'tol_t_transactions';
    protected $fillable = [
        'tool_id', 'location_id', 'destination_id', 'transaction_type',
        'qty', 'ref_doc', 'note', 'transacted_by', 'transacted_at',
    ];

    protected $casts = [
        'qty'            => 'integer',
        'transacted_at'  => 'datetime',
    ];

    public function tool()
    {
        return $this->belongsTo(TolTool::class, 'tool_id');
    }

    public function location()
    {
        return $this->belongsTo(TolLocation::class, 'location_id');
    }

    public function destination()
    {
        return $this->belongsTo(TolDestination::class, 'destination_id');
    }

    public function operator()
    {
        return $this->belongsTo(\App\Models\User::class, 'transacted_by');
    }
}
