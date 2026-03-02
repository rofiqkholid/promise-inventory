<?php

namespace App\Models\InventoryModel;

use Illuminate\Database\Eloquent\Model;

class StoReason extends Model
{
    protected $table = 'inv_m_sto_reasons';
    protected $fillable = ['name', 'category', 'is_active'];
}
