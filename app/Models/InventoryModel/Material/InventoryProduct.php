<?php

namespace App\Models\InventoryModel\Material;

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
        'current_stock_pcs',
        'trial_usage_qty',
        'trial_usage_pcs',
        'is_active',
        'remark',
        'density',
        'gross_coil',
        'top_coil',
        'end_coil',
        'net_coil',
        'weight_kg',
        'net_weight',
        'material_price',
        'product_status',
        'product_status_remark',
        'action_status',
    ];

    protected $casts = [
        'thickness' => 'float',
        'width' => 'float',
        'length' => 'float',
        'length_2' => 'float',
        'pitch' => 'float',
        'current_stock_qty' => 'float',
        'current_stock_pcs' => 'integer',
        'trial_usage_qty' => 'float',
        'trial_usage_pcs' => 'integer',
        'pcs_per_unit' => 'integer',
        'pcs_per_pitch' => 'integer',
        'unit_per_car' => 'integer',
        'min_stock' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'density' => 'float',
        'gross_coil' => 'float',
        'top_coil' => 'float',
        'end_coil' => 'float',
        'net_coil' => 'float',
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
     * Takes account of 'Coil' unit type which records stock in Kg and subtracts scrap mm.
     */
    public static function getPcsCalculationSql($qtyColumn = 'inv_t_product_detail.current_stock_qty', $tableAlias = 'inv_t_product_detail', $unitNameColumn = null)
    {
        $alias = $tableAlias ? $tableAlias . '.' : '';
        $unitCheck = $unitNameColumn ?: "(SELECT name FROM inv_m_unit WHERE id = {$alias}unit_id)";
        
        return "
            CASE 
                WHEN LOWER({$unitCheck}) LIKE '%coil%' 
                     AND ISNULL({$alias}gross_coil, 0) > 0 
                THEN ({$qtyColumn} / {$alias}gross_coil) * COALESCE(NULLIF({$alias}pcs_per_unit, 0), 1) 
                ELSE ({$qtyColumn}) * COALESCE(NULLIF({$alias}pcs_per_unit, 0), 1) 
            END
        ";
    }

    /**
     * Get the universal Raw SQL to convert stock / qty column to monetary Amount.
     * Principle: Total Price = Total Weight (KG) * Price per KG.
     */
    public static function getAmountCalculationSql($qtyColumn = 'inv_t_product_detail.current_stock_qty', $tableAlias = 'inv_t_product_detail', $unitNameColumn = null)
    {
        $alias = $tableAlias ? $tableAlias . '.' : '';
        $unitCheck = $unitNameColumn ?: "(SELECT name FROM inv_m_unit WHERE id = {$alias}unit_id)";
        
        return "
            CASE 
                WHEN LOWER({$unitCheck}) LIKE '%coil%' 
                THEN ({$qtyColumn} * ISNULL({$alias}material_price, 0)) 
                ELSE ({$qtyColumn} * COALESCE(NULLIF({$alias}pcs_per_unit, 0), 1) * ISNULL({$alias}weight_kg, 0) * ISNULL({$alias}material_price, 0)) 
            END
        ";
    }

    public static function calculatePcs($qty, $weightKg, $pcsPerUnit, $unitName, $topMm = 0, $endMm = 0, $pitch = 0, $pcsPerPitch = 1, $grossCoil = 0)
    {
        return \App\Services\Inventory\StockCalculator::calculatePcs($qty, $weightKg, $pcsPerUnit, $unitName, $topMm, $endMm, $pitch, $pcsPerPitch, $grossCoil);
    }

    public static function calculateAmount($qty, $materialPrice, $weightKg = 0, $pcsPerUnit = 1, $unitName = '')
    {
        return \App\Services\Inventory\StockCalculator::calculateAmount($qty, $materialPrice, $weightKg, $pcsPerUnit, $unitName);
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

    /**
     * Logic to calculate weight in Kg.
     */
    public static function calculateWeight($unitName, $thickness, $width, $length, $length2, $pitch, $density, $pcsPerUnit = 1, $pcsPerPitch = 1)
    {
        $unitNameLower = strtolower($unitName ?? '');
        $thickness = (float)$thickness;
        $width = (float)$width;
        $length = (float)$length;
        $length2 = (float)$length2;
        $pitch = (float)$pitch;
        $density = (float)($density ?: 7.85);
        $pcsPerUnit = max(1, (int)$pcsPerUnit);
        $pcsPerPitch = max(1, (int)$pcsPerPitch);

        if (str_contains($unitNameLower, 'sheet')) {
            return (($thickness * $width * $length * $density) / 1000000) / $pcsPerUnit;
        } elseif (str_contains($unitNameLower, 'coil')) {
            return (($thickness * $width * $pitch * $density) / 1000000) / $pcsPerPitch;
        } elseif (str_contains($unitNameLower, 'trapezoid')) {
            return (($thickness * $width * (($length + $length2) / 2) * $density) / 1000000) / $pcsPerUnit;
        }

        return (($thickness * $width * $length * $density) / 1000000) / $pcsPerUnit;
    }

    /**
     * Logic to calculate default minimum stock.
     */
    public static function calculateMinStock($unitPerCar, $days = 90)
    {
        return (int)($unitPerCar * $days);
    }
    /**
     * Helper to check if this product is a coil based on its unit.
     */
    public function isCoil()
    {
        $unitName = strtolower($this->unit->name ?? '');
        return str_contains($unitName, 'coil');
    }
}
