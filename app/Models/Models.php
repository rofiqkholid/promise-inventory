<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Models extends Model
{
    use HasFactory;

    protected $table = 'models';

    protected $fillable = [
        'name',
        'customer_id', // Assuming it belongs to a customer
    ];
}
