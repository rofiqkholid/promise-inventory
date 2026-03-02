<?php

namespace App\Models\InventoryModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelStatus extends Model
{
    use HasFactory;

    protected $table = 'inv_m_model_status';
    protected $primaryKey = 'model_id';
    public $incrementing = false;

    protected $fillable = [
        'model_id',
        'project_status',
    ];

    public function model()
    {
        return $this->belongsTo(\App\Models\Models::class, 'model_id');
    }
}
