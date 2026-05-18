<?php

namespace App\Models\InventoryModel\Tool;

use Illuminate\Database\Eloquent\Model;

class TolCategory extends Model
{
    protected $table = 'tol_m_categories';
    protected $fillable = ['name', 'moving_type', 'code_prefix', 'description', 'is_active'];

    public function tools()
    {
        return $this->hasMany(TolTool::class, 'category_id');
    }
}
