<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$modelId = 1; // 5P45

$trans = DB::table('inv_t_inventory_transaction as t')
    ->join('inv_m_transaction_category as tc', 'tc.id', '=', 't.transaction_category_id')
    ->join('inv_t_product_detail as pd', 'pd.id', '=', 't.product_detail_id')
    ->where('pd.model_id', $modelId)
    ->whereYear('t.transaction_date', 2026)
    ->select('t.id', 't.qty', 'tc.name as category', 'tc.effect', 't.transaction_date')
    ->get();

echo "Transactions for 5P45 in 2026:" . PHP_EOL;
print_r($trans);
