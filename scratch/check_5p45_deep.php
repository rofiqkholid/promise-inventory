<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$p1 = DB::table('products')->where('id', 1248)->first();
$p2 = DB::table('products')->where('id', 1221)->first();

echo "P1: {$p1->part_no} ({$p1->part_name})" . PHP_EOL;
echo "P2: {$p2->part_no} ({$p2->part_name})" . PHP_EOL;

$pd1 = DB::table('inv_t_product_detail')->where('product_id', 1248)->where('is_active', 1)->first();
$vb1 = DB::table('inv_m_vave_base')->where('product_id', 1248)->where('is_active', 1)->first();

echo "P1 Detail (Actual): Weight={$pd1->weight_kg}" . PHP_EOL;
echo "P1 Vave (Baseline): Weight={$vb1->weight_kg}, Price={$vb1->material_price}" . PHP_EOL;

$pd2 = DB::table('inv_t_product_detail')->where('product_id', 1221)->where('is_active', 1)->first();
$vb2 = DB::table('inv_m_vave_base')->where('product_id', 1221)->where('is_active', 1)->first();

echo "P2 Detail (Actual): Weight={$pd2->weight_kg}" . PHP_EOL;
echo "P2 Vave (Baseline): Weight={$vb2->weight_kg}, Price={$vb2->material_price}" . PHP_EOL;
