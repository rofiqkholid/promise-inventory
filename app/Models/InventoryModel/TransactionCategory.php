<?php

namespace App\Models\InventoryModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransactionCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'inv_m_transaction_category';

    public $timestamps = false;

    protected $fillable = [
        'code',
        'name',
        'effect', // 1 for Add, -1 for Deduct
    ];

    protected $casts = [
        'effect' => 'integer',
    ];
}
