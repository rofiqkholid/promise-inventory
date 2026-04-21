<?php

namespace App\Models\InventoryModel\Tool;

use Illuminate\Database\Eloquent\Model;

class TolLocation extends Model
{
    protected $table = 'tol_m_locations';
    protected $fillable = ['code', 'name', 'description', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function fastStock()
    {
        return $this->hasMany(TolFastStock::class, 'location_id');
    }

    public function slowBatches()
    {
        return $this->hasMany(TolSlowBatch::class, 'location_id');
    }
}
