<?php

namespace App\Models\InventoryModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryTransaction extends Model
{
    use HasFactory, \App\Traits\HasHashId;

    protected $appends = ['hash_id'];
    protected $hidden = ['id'];

    protected $table = 'inv_t_inventory_transaction';

    protected $fillable = [
        'product_detail_id',
        'transaction_date',
        'qty',
        'transaction_category_id',
        'user_id',
        'remark',
        'coil_center_id',
        'supplier_id',
        'destination_id'
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'qty' => 'float',
    ];

    public function transactionCategory()
    {
        return $this->belongsTo(TransactionCategory::class, 'transaction_category_id');
    }

    public function product()
    {
        return $this->belongsTo(InventoryProduct::class, 'product_detail_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function pic()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function coilCenter()
    {
        return $this->belongsTo(CoilCenter::class, 'coil_center_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function destination()
    {
        return $this->belongsTo(Supplier::class, 'destination_id');
    }
}
