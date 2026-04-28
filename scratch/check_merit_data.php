<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$meritCount = DB::table('inv_m_vave_base as vb')
    ->join('inv_t_product_detail as pd', 'pd.product_id', '=', 'vb.product_id')
    ->where('vb.is_active', 1)
    ->where('pd.is_active', 1)
    ->whereRaw('vb.weight_kg > pd.weight_kg')
    ->count();

echo 'Merit items found in DB: ' . $meritCount . PHP_EOL;

$totalVave = DB::table('inv_m_vave_base')->where('is_active', 1)->count();
echo 'Total active Vave records: ' . $totalVave . PHP_EOL;

$withTransactions = DB::table('inv_t_inventory_transaction as t')
    ->join('inv_t_product_detail as pd', 'pd.id', '=', 't.product_detail_id')
    ->join('inv_m_vave_base as vb', 'vb.product_id', '=', 'pd.product_id')
    ->whereYear('t.transaction_date', 2026)
    ->count();
echo 'Vave records with 2026 transactions: ' . $withTransactions . PHP_EOL;
