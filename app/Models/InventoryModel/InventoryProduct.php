<?php

namespace App\Models\InventoryModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasHashId;

class InventoryProduct extends Model
{
    use HasFactory, HasHashId;

    protected $table = 'inv_t_product_detail';
    protected $appends = ['hash_id'];
    protected $hidden = ['id'];

    protected $fillable = [
        'product_id',
        'model_id',
        'material_spec_id',
        'unit_id',
        'rank_id',
        'revision_id',
        'thickness',
        'width',
        'length',
        'length_2',
        'pitch',
        'pcs_per_pitch',
        'pcs_per_unit',
        'unit_per_car',
        'min_stock',
        'current_stock_qty',
        'trial_usage_qty',
        'is_active',
        'remark',
        'density',
        'weight_kg',
        'net_weight',
        'material_price',
        'product_status',
        'product_status_remark',
    ];

    protected $casts = [
        'thickness' => 'float',
        'width' => 'float',
        'length' => 'float',
        'length_2' => 'float',
        'pitch' => 'float',
        'current_stock_qty' => 'float',
        'trial_usage_qty' => 'float',
        'pcs_per_unit' => 'integer',
        'pcs_per_pitch' => 'integer',
        'unit_per_car' => 'integer',
        'min_stock' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'density' => 'float',
        'weight_kg' => 'float',
        'net_weight' => 'float',
        'material_price' => 'float',
    ];

    public function product()
    {
        return $this->belongsTo(\App\Models\Products::class, 'product_id');
    }

    public function model()
    {
        return $this->belongsTo(\App\Models\Models::class, 'model_id');
    }

    public function materialSpec()
    {
        return $this->belongsTo(MaterialSpec::class, 'material_spec_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function rank()
    {
        return $this->belongsTo(Rank::class, 'rank_id');
    }

    public function revision()
    {
        return $this->belongsTo(Revision::class, 'revision_id');
    }

    /**
     * Get the universal Raw SQL to convert stock / qty column to accurate total Pcs.
     * Takes account of 'Coil' unit type which records stock in Kg.
     */
    public static function getPcsCalculationSql($qtyColumn = 'inv_t_product_detail.current_stock_qty', $tableAlias = 'inv_t_product_detail', $unitNameColumn = null)
    {
        $alias = $tableAlias ? $tableAlias . '.' : '';
        $unitCheck = $unitNameColumn ?: "(SELECT name FROM inv_m_unit WHERE id = {$alias}unit_id)";
        
        return "
            CASE 
                WHEN LOWER({$unitCheck}) LIKE '%coil%' 
                     AND ISNULL({$alias}weight_kg, 0) > 0 
                THEN FLOOR({$qtyColumn} / {$alias}weight_kg) * COALESCE({$alias}pcs_per_unit, 1) 
                ELSE ({$qtyColumn}) * COALESCE({$alias}pcs_per_unit, 1) 
            END
        ";
    }

    /**
     * PHP implementation of the PCS calculation logic.
     */
    public static function calculatePcs($qty, $weightKg, $pcsPerUnit, $unitName)
    {
        $qty = (float)$qty;
        $weightKg = (float)$weightKg;
        $pcsPerUnit = (float)($pcsPerUnit ?: 1);
        $unitName = strtolower($unitName ?? '');

        if (str_contains($unitName, 'coil') && $weightKg > 0) {
            return floor($qty / $weightKg) * $pcsPerUnit;
        }

        return $qty * $pcsPerUnit;
    }

    /**
     * Consolidated logic to determine stock status color/label.
     */
    public static function calculateStockStatus($currentPcs, $minStock, $projectStatus = null)
    {
        $minStock = (float)$minStock;
        $currentPcs = (float)$currentPcs;

        if ($minStock <= 0) return 'safe';

        $maxStock = $minStock * 3;

        if ($currentPcs > $maxStock) return 'over'; // Blue
        
        // Skip Warning/Critical for Regular projects or specific Oldstock overrides
        $safeStatuses = ['Regular', 'Oldstock OK', 'Oldstock NG'];
        if ($projectStatus && in_array($projectStatus, $safeStatuses)) {
            return 'safe';
        }

        if ($currentPcs < ($minStock - 30)) return 'critical'; // Red
        if ($currentPcs < $minStock) return 'warning'; // Yellow

        return 'safe'; // Normal
    }

    /**
     * Consolidated logic for trial items.
     */
    public static function calculateTrialStatus($usage, $limit, $pcsPerUnit)
    {
        $limit = (float)$limit;
        $usage = (float)$usage;

        if ($limit <= 0) return 'safe';

        $usagePCS = $usage * (float)$pcsPerUnit;

        if ($usagePCS > $limit) return 'critical';
        if ($usagePCS > ($limit - 50)) return 'warning';

        return 'safe';
    }
}
