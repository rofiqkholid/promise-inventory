<?php

namespace App\Models\InventoryModel\Tool;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class TolStoEvent extends Model
{
    protected $table = 'tol_t_sto_events';
    protected $fillable = [
        'code', 'name', 'period_start', 'period_end',
        'status', 'user_id', 'approved_by', 'approved_at',
        'description', 'rejection_note'
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end'   => 'date',
        'approved_at'  => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function fastDetails()
    {
        return $this->hasMany(TolStoFast::class, 'event_id');
    }

    public function slowDetails()
    {
        return $this->hasMany(TolStoSlow::class, 'event_id');
    }
}
