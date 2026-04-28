<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$modelId = 1; // 5P45

$meritCount = DB::table('inv_m_vave_base as vb')
    ->join('inv_t_product_detail as pd', 'pd.product_id', '=', 'vb.product_id')
    ->where('vb.is_active', 1)
    ->where('pd.is_active', 1)
    ->where('pd.model_id', $modelId)
    ->whereRaw('vb.weight_kg > pd.weight_kg')
    ->count();

echo "Merit VAVE items for 5P45: " . $meritCount . PHP_EOL;

$totalVave = DB::table('inv_m_vave_base as vb')
    ->join('inv_t_product_detail as pd', 'pd.product_id', '=', 'vb.product_id')
    ->where('vb.is_active', 1)
    ->where('pd.is_active', 1)
    ->where('pd.model_id', $modelId)
    ->count();

echo "Total active VAVE items for 5P45: " . $totalVave . PHP_EOL;

$sample = DB::table('inv_m_vave_base as vb')
    ->join('inv_t_product_detail as pd', 'pd.product_id', '=', 'vb.product_id')
    ->where('vb.is_active', 1)
    ->where('pd.is_active', 1)
    ->where('pd.model_id', $modelId)
    ->select('vb.weight_kg as base', 'pd.weight_kg as actual')
    ->limit(5)
    ->get();

echo "Samples (Base vs Actual):" . PHP_EOL;
print_r($sample);
