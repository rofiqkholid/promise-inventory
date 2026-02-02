<?php

namespace App\Models\InventoryModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasHashId;

class StoEvent extends Model
{
    use HasFactory, HasHashId;

    protected $table = 'inv_t_sto_event';
    protected $appends = ['hash_id'];
    protected $hidden = ['id'];

    protected $fillable = [
        'code',
        'name',
        'period_start',
        'period_end',
        'status',
        'user_id',
        'checked_by',
        'checked_at',
        'approved_by',
        'approved_at',
        'description',
        'rejection_note',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'checked_at' => 'datetime',
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function details()
    {
        return $this->hasMany(StoDetail::class, 'event_id');
    }

    public function pic()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function checker()
    {
        return $this->belongsTo(\App\Models\User::class, 'checked_by');
    }

    public function approver()
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }
}
