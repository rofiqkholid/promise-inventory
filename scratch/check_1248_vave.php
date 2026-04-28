<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$vave = DB::table('inv_m_vave_base')->where('product_id', 1248)->get();
echo "VAVE records for Product 1248:" . PHP_EOL;
foreach ($vave as $v) {
    echo "ID: {$v->id}, Base: {$v->base_name}, Active: {$v->is_active}, From: {$v->effective_from}" . PHP_EOL;
}
