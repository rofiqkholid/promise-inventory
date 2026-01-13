<?php

namespace App\Models\InventoryModel;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasHashId;

class TransactionCategory extends Model
{
    use HasFactory, SoftDeletes, HasHashId;

    protected $table = 'inv_m_transaction_category';
    protected $appends = ['hash_id'];
    protected $hidden = ['id'];

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
