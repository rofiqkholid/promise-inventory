<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$year = 2026;
$month = 4;

$baseQuery = DB::table('inv_t_inventory_transaction as t')
    ->join('inv_m_transaction_category as tc', 'tc.id', '=', 't.transaction_category_id')
    ->join('inv_t_product_detail as pd', 'pd.id', '=', 't.product_detail_id')
    ->join('products as p', 'p.id', '=', 'pd.product_id')
    ->join('models as m', 'm.id', '=', 'pd.model_id')
    ->join('customers as c', 'c.id', '=', 'p.customer_id')
    ->leftJoin('inv_m_vave_base as vb', function ($join) use ($year) {
        $join->on('vb.product_id', '=', 'p.id')
             ->whereRaw('vb.effective_from <= ?', [$year])
             ->where(function ($q) use ($year) {
                 $q->whereNull('vb.effective_to')
                   ->orWhereRaw('vb.effective_to >= ?', [$year]);
             });
    })
    ->where('tc.effect', 1)
    ->whereYear('t.transaction_date', $year)
    ->where('p.is_delete', 0)
    ->whereNotNull('vb.id');

if ($month) {
    $baseQuery->whereMonth('t.transaction_date', $month);
}

$rawData = $baseQuery->select([
        'p.id as product_id',
        'p.part_no',
        'p.part_name',
        'm.name as model_name',
        'c.code as customer_code',
        'vb.base_name as ebd_version',
        DB::raw('ISNULL(vb.weight_kg, 0) as plan_kg'),
        DB::raw('ISNULL(pd.weight_kg, 0) as actual_kg'),
        DB::raw('ISNULL(vb.material_price, 0) as idr_per_kg'),
        DB::raw('SUM(t.qty) as qty_usage'),
        DB::raw('SUM(((ISNULL(vb.weight_kg, 0) - ISNULL(pd.weight_kg, 0)) * ISNULL(vb.material_price, 0)) * t.qty) as gap_benefit_idr'),
        DB::raw('SUM((ISNULL(vb.weight_kg, 0) - ISNULL(pd.weight_kg, 0)) * t.qty) as gap_kg_total'),
        DB::raw('SUM(ISNULL(vb.weight_kg, 0) * ISNULL(vb.material_price, 0) * t.qty) as plan_total_cost'),
        DB::raw('COUNT(CASE WHEN (ISNULL(vb.weight_kg, 0) - ISNULL(pd.weight_kg, 0)) > 0 THEN 1 END) as merit_count'),
        DB::raw('COUNT(CASE WHEN (ISNULL(vb.weight_kg, 0) - ISNULL(pd.weight_kg, 0)) < 0 THEN 1 END) as loss_count')
    ])
    ->groupBy(
        'p.id', 'p.part_no', 'p.part_name', 'm.name', 'c.code', 'vb.base_name',
        'vb.weight_kg', 'pd.weight_kg', 'vb.material_price'
    )
    ->get();

foreach ($rawData as $row) {
    $gap = $row->plan_kg - $row->actual_kg;
    echo "Part: {$row->part_no}, Plan: {$row->plan_kg}, Act: {$row->actual_kg}, Gap: {$gap}" . PHP_EOL;
}
