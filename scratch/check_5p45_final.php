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

$lossCount = DB::table('inv_m_vave_base as vb')
    ->join('inv_t_product_detail as pd', 'pd.product_id', '=', 'vb.product_id')
    ->where('vb.is_active', 1)
    ->where('pd.is_active', 1)
    ->where('pd.model_id', $modelId)
    ->whereRaw('vb.weight_kg < pd.weight_kg')
    ->count();

$equalCount = DB::table('inv_m_vave_base as vb')
    ->join('inv_t_product_detail as pd', 'pd.product_id', '=', 'vb.product_id')
    ->where('vb.is_active', 1)
    ->where('pd.is_active', 1)
    ->where('pd.model_id', $modelId)
    ->whereRaw('vb.weight_kg = pd.weight_kg')
    ->count();

echo "5P45 Merit: {$meritCount}" . PHP_EOL;
echo "5P45 Loss: {$lossCount}" . PHP_EOL;
echo "5P45 Equal: {$equalCount}" . PHP_EOL;
