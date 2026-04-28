<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$modelName = '5P45';
$model = DB::table('models')->where('name', $modelName)->first();

if ($model) {
    echo "Model {$modelName} ID: " . $model->id . PHP_EOL;
    
    $vaveCount = DB::table('inv_m_vave_base as vb')
        ->join('products as p', 'p.id', '=', 'vb.product_id')
        ->where('p.model_id', $model->id)
        ->count();
    echo "VAVE records for this model (via products table): " . $vaveCount . PHP_EOL;
    
    $vaveCountDetail = DB::table('inv_m_vave_base as vb')
        ->join('inv_t_product_detail as pd', 'pd.product_id', '=', 'vb.product_id')
        ->where('pd.model_id', $model->id)
        ->count();
    echo "VAVE records for this model (via product_detail table): " . $vaveCountDetail . PHP_EOL;
    
    $activeDetailCount = DB::table('inv_t_product_detail')
        ->where('model_id', $model->id)
        ->where('is_active', 1)
        ->count();
    echo "Active Master Product Details for this model: " . $activeDetailCount . PHP_EOL;

    $transCount = DB::table('inv_t_inventory_transaction as t')
        ->join('inv_t_product_detail as pd', 'pd.id', '=', 't.product_detail_id')
        ->where('pd.model_id', $model->id)
        ->whereYear('t.transaction_date', 2026)
        ->count();
    echo "Transactions for this model in 2026: " . $transCount . PHP_EOL;

} else {
    echo "Model {$modelName} not found" . PHP_EOL;
}
