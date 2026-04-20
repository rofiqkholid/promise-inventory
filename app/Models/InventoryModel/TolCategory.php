<?php

namespace App\Models\InventoryModel;

use Illuminate\Database\Eloquent\Model;

class TolCategory extends Model
{
    protected $table = 'tol_m_categories';
    protected $fillable = ['name', 'description'];

    public function tools()
    {
        return $this->hasMany(TolTool::class, 'category_id');
    }
}
