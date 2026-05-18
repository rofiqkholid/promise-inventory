<?php

namespace App\Models\InventoryModel\Tool;

use Illuminate\Database\Eloquent\Model;

class TolToolSetting extends Model
{
    protected $table = 'tol_m_tool_settings';

    protected $fillable = [
        'tool_id',
        'material_category',
        'spindle_speed',
        'table_feed',
        'depth_of_cut',
        'step_over',
        'cnc_small_plant_b',
        'cnc_big_hartford_plant_f',
        'status',
    ];

    protected $casts = [
        'tool_id'                  => 'integer',
        'spindle_speed'            => 'integer',
        'table_feed'               => 'integer',
        'depth_of_cut'             => 'float',
        'cnc_small_plant_b'        => 'boolean',
        'cnc_big_hartford_plant_f' => 'boolean',
    ];

    public function tool()
    {
        return $this->belongsTo(TolTool::class, 'tool_id');
    }
}
