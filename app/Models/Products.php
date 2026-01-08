<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Products extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'model_id',
        'part_no',
        'part_name',
        'group_id',
        'is_delete',
    ];

    protected $table = 'products';

    protected static function booted()
    {
        static::addGlobalScope('not_deleted', function (Builder $builder) {
            $builder->where('is_delete', 0);
        });
    }
}
