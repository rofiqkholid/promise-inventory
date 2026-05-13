<?php

namespace App\Models\InventoryModel\Tool;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TolSlowBatch extends Model
{
    protected $table = 'tol_t_slow_batches';
    protected $fillable = [
        'id_number', 'tool_id', 'location_id',
        'purchase_date', 'purchase_price',
        'qty_purchased', 'qty_current',
        'std_lifetime_yrs', 'physical_rate', 'current_value',
        'status', 'nok_date', 'nok_reason', 'nok_by',
    ];

    protected $casts = [
        'purchase_date'    => 'date',
        'purchase_price'   => 'float',
        'qty_purchased'    => 'integer',
        'qty_current'      => 'integer',
        'std_lifetime_yrs' => 'integer',
        'physical_rate'    => 'float',
        'current_value'    => 'float',
        'nok_date'         => 'date',
    ];

    public function tool()
    {
        return $this->belongsTo(TolTool::class, 'tool_id');
    }

    public function location()
    {
        return $this->belongsTo(TolLocation::class, 'location_id');
    }

    public function stoRecords()
    {
        return $this->hasMany(TolStoSlow::class, 'batch_id');
    }

    /**
     * Calculate asset value based on depreciation and physical rate.
     */
    public function calculateAssetValue(string $stoDate = null): float
    {
        $sto = $stoDate ? Carbon::parse($stoDate) : Carbon::today();
        $purchase = Carbon::parse($this->purchase_date);
        $ageYears = $purchase->diffInDays($sto) / 365.25;
        $remainingYears = $this->std_lifetime_yrs - $ageYears;

        if ($remainingYears <= 0) return 0.0;

        // Combine depreciation with physical condition rate
        $depreciationFactor = $remainingYears / $this->std_lifetime_yrs;
        $physicalFactor = $this->physical_rate / 100;
        
        return round($this->qty_current * $this->purchase_price * $depreciationFactor * $physicalFactor, 2);
    }

    /** Used age in years */
    public function getAgeYearsAttribute(): float
    {
        return round(Carbon::parse($this->purchase_date)->diffInDays(Carbon::today()) / 365.25, 2);
    }
}
