<?php

namespace App\Models\InventoryModel\Tool;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TolSketch extends Model
{
    use HasFactory;

    protected $table = 'tol_m_sketches';

    protected $fillable = [
        'category_id',
        'name',
        'image_path',
    ];

    public function category()
    {
        return $this->belongsTo(TolCategory::class, 'category_id');
    }

    public function tools()
    {
        return $this->hasMany(TolTool::class, 'sketch_id');
    }
}
