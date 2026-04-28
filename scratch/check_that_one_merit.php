<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$year = 2026;

$data = DB::table('inv_t_inventory_transaction as t')
    ->join('inv_t_product_detail as pd', 'pd.id', '=', 't.product_detail_id')
    ->join('inv_m_vave_base as vb', 'vb.product_id', '=', 'pd.product_id')
    ->whereYear('t.transaction_date', $year)
    ->whereRaw('vb.weight_kg > pd.weight_kg')
    ->select('t.id', 'pd.product_id', 'vb.effective_from', 'vb.is_active')
    ->get();

print_r($data);
