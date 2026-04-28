<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$t = DB::table('inv_t_inventory_transaction as t')
    ->join('inv_m_transaction_category as tc', 'tc.id', '=', 't.transaction_category_id')
    ->where('t.id', 6)
    ->select('tc.name', 'tc.effect')
    ->first();

print_r($t);
