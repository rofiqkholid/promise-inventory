<?php

namespace App\Models\InventoryModel\Tool;

use Illuminate\Database\Eloquent\Model;

class TolStoSlow extends Model
{
    protected $table = 'tol_t_sto_slow';
    protected $fillable = [
        'event_id', 'batch_id',
        'physical_check', 'qty_checked', 'qty_ok', 'qty_nok',
        'age_years', 'remaining_value',
        'note'
    ];

    protected $casts = [
        'qty_checked'     => 'integer',
        'qty_ok'          => 'integer',
        'qty_nok'         => 'integer',
        'age_years'       => 'float',
        'remaining_value' => 'float',
    ];

    public function event()
    {
        return $this->belongsTo(TolStoEvent::class, 'event_id');
    }

    public function batch()
    {
        return $this->belongsTo(TolSlowBatch::class, 'batch_id');
    }
}
