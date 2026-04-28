<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$count = DB::table('inv_m_vave_base as vb')
    ->join('inv_t_product_detail as pd', 'pd.product_id', '=', 'vb.product_id')
    ->join('inv_t_inventory_transaction as t', 't.product_detail_id', '=', 'pd.id')
    ->where('pd.model_id', 1)
    ->whereYear('t.transaction_date', 2026)
    ->whereRaw('vb.weight_kg > pd.weight_kg')
    ->count();

echo "Merit items for 5P45 with transactions: " . $count . PHP_EOL;
