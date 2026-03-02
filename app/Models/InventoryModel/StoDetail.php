<?php

namespace App\Models\InventoryModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasHashId;

class StoDetail extends Model
{
    use HasFactory, HasHashId;

    protected $table = 'inv_t_sto_detail';
    protected $appends = ['hash_id'];
    protected $hidden = ['id'];

    protected $fillable = [
        'event_id',
        'product_detail_id',
        'system_qty_snapshot',
        'real_qty_input',
        'diff_qty',
        'auditor_id',
        'remark',
        'reason_id',
        'is_adjusted'
    ];

    protected $casts = [
        'system_qty_snapshot' => 'float',
        'real_qty_input' => 'float',
        'diff_qty' => 'float',
        'is_adjusted' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function event()
    {
        return $this->belongsTo(StoEvent::class, 'event_id');
    }

    public function product()
    {
        return $this->belongsTo(InventoryProduct::class, 'product_detail_id');
    }

    public function auditor()
    {
        return $this->belongsTo(\App\Models\User::class, 'auditor_id');
    }

    public function reason()
    {
        return $this->belongsTo(StoReason::class, 'reason_id');
    }

    // Accessor for Computed Column 'diff_qty' if needed in PHP logic, 
    // although Model will just retrieve it if it's select * or explicit select.
    // Ensure we don't try to fill it.
}
