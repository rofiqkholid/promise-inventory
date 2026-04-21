<?php

namespace App\Models\InventoryModel\Tool;

use Illuminate\Database\Eloquent\Model;

class TolStoSlow extends Model
{
    protected $table = 'tol_t_sto_slow';
    protected $fillable = [
        'sto_date', 'batch_id',
        'physical_check', 'qty_checked', 'qty_ok', 'qty_nok',
        'age_years', 'remaining_value',
        'note', 'conducted_by', 'approved_by', 'status',
    ];

    protected $casts = [
        'sto_date'        => 'date',
        'qty_checked'     => 'integer',
        'qty_ok'          => 'integer',
        'qty_nok'         => 'integer',
        'age_years'       => 'float',
        'remaining_value' => 'float',
    ];

    public function batch()
    {
        return $this->belongsTo(TolSlowBatch::class, 'batch_id');
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
