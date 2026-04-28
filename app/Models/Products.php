<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Customer;

class Products extends Model
{
    use HasFactory, \App\Traits\HasHashId;

    protected $appends = ['hash_id'];
    protected $hidden = ['id'];

    protected $fillable = [
        'customer_id',
        'model_id',
        'part_no',
        'part_name',
        'group_id',
        'is_delete',
    ];

    protected $table = 'products';

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    protected static function booted()
    {
        static::addGlobalScope('not_deleted', function (Builder $builder) {
            $builder->where('is_delete', 0);
        });
    }
}
