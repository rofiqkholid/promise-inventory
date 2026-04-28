<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$transIds = [1, 7];

$data = DB::table('inv_t_inventory_transaction as t')
    ->join('inv_t_product_detail as pd', 'pd.id', '=', 't.product_detail_id')
    ->leftJoin('inv_m_vave_base as vb', 'vb.product_id', '=', 'pd.product_id')
    ->whereIn('t.id', $transIds)
    ->select('t.id', 'pd.product_id', 'vb.id as vave_id', 'vb.weight_kg as vave_weight', 'pd.weight_kg as actual_weight', 'vb.effective_from')
    ->get();

echo "Transaction Product & Vave Link:" . PHP_EOL;
print_r($data);
