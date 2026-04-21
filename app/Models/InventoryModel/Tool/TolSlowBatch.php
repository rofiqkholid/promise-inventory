<?php

namespace App\Models\InventoryModel\Tool;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TolSlowBatch extends Model
{
    protected $table = 'tol_t_slow_batches';
    protected $fillable = [
        'batch_no', 'tool_id', 'location_id',
        'purchase_date', 'purchase_price',
        'qty_purchased', 'qty_current',
        'std_lifetime_yrs', 'current_value',
        'status', 'nok_date', 'nok_reason', 'nok_by',
    ];

    protected $casts = [
        'purchase_date'    => 'date',
        'purchase_price'   => 'float',
        'qty_purchased'    => 'integer',
        'qty_current'      => 'integer',
        'std_lifetime_yrs' => 'integer',
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
     * Hitung nilai aset berdasarkan Straight-Line Depreciation.
     * @param string|null $stoDate  Format Y-m-d, default = hari ini
     * @return float  Nilai aset dalam IDR
     */
    public function calculateAssetValue(string $stoDate = null): float
    {
        $sto = $stoDate ? Carbon::parse($stoDate) : Carbon::today();
        $purchase = Carbon::parse($this->purchase_date);
        $ageYears = $purchase->diffInDays($sto) / 365.25;
        $remainingYears = $this->std_lifetime_yrs - $ageYears;

        if ($remainingYears <= 0) return 0.0;

        return round($this->qty_current * $this->purchase_price * ($remainingYears / $this->std_lifetime_yrs), 2);
    }

    /** Umur terpakai dalam tahun (presisi 2 desimal) */
    public function getAgeYearsAttribute(): float
    {
        return round(Carbon::parse($this->purchase_date)->diffInDays(Carbon::today()) / 365.25, 2);
    }
}
