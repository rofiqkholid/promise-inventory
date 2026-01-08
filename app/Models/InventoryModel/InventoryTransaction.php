<?php

namespace App\Models\InventoryModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryTransaction extends Model
{
    use HasFactory;

    protected $table = 'inv_t_inventory_transaction';

    // Disable updated_at since the user schema only showed created_at
    const UPDATED_AT = null;

    protected $fillable = [
        'product_detail_id',
        'transaction_date',
        'qty',
        'category', // Stores the CODE from Master
        'pic_id',
        'remark'
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'qty' => 'float',
    ];

    public function product()
    {
        return $this->belongsTo(InventoryProduct::class, 'product_detail_id');
    }

    public function pic()
    {
        return $this->belongsTo(PIC::class, 'pic_id');
    }
}
