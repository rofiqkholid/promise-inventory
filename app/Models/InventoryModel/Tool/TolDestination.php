<?php

namespace App\Models\InventoryModel\Tool;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TolDestination extends Model
{
    use HasFactory;

    protected $table = 'tol_m_destinations';

    protected $fillable = [
        'code',
        'name',
        'is_active',
    ];

    public function transactions()
    {
        return $this->hasMany(TolTransaction::class, 'destination_id');
    }
}
