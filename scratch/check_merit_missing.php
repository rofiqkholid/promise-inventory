<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$year = 2026;

$meritWithTransactions = DB::table('inv_t_inventory_transaction as t')
    ->join('inv_t_product_detail as pd', 'pd.id', '=', 't.product_detail_id')
    ->join('inv_m_vave_base as vb', 'vb.product_id', '=', 'pd.product_id')
    ->whereYear('t.transaction_date', $year)
    ->whereRaw('vb.weight_kg > pd.weight_kg')
    ->count();

echo "Merit items with 2026 transactions: " . $meritWithTransactions . PHP_EOL;

$meritWithoutTransactions = DB::table('inv_m_vave_base as vb')
    ->join('inv_t_product_detail as pd', 'pd.product_id', '=', 'vb.product_id')
    ->where('vb.is_active', 1)
    ->where('pd.is_active', 1)
    ->whereRaw('vb.weight_kg > pd.weight_kg')
    ->whereNotExists(function ($q) use ($year) {
        $q->select(DB::raw(1))
          ->from('inv_t_inventory_transaction')
          ->whereColumn('inv_t_inventory_transaction.product_detail_id', 'pd.id')
          ->whereYear('transaction_date', $year);
    })
    ->count();

echo "Merit items WITHOUT 2026 transactions: " . $meritWithoutTransactions . PHP_EOL;
