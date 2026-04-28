<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$query = DB::table('products as p')
    ->where('p.is_delete', 0)
    ->whereExists(function ($q) {
        $q->select(DB::raw(1))
          ->from('inv_t_product_detail')
          ->whereColumn('inv_t_product_detail.product_id', 'p.id')
          ->where('inv_t_product_detail.is_active', 1);
    });

echo 'Total matching products in DB: ' . $query->count() . PHP_EOL;

$withBase = (clone $query)->leftJoin('inv_m_vave_base as base', function($join) {
    $join->on('base.product_id', '=', 'p.id')
         ->where('base.is_active', '=', 1);
})->whereNotNull('base.id')->count();

echo 'Products with active VAVE base: ' . $withBase . PHP_EOL;
