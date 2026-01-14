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
        'pic_id',
        'description',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function details()
    {
        return $this->hasMany(StoDetail::class, 'event_id');
    }

    public function pic()
    {
        return $this->belongsTo(PIC::class, 'pic_id');
    }
}
