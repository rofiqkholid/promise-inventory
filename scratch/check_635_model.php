<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$p = DB::table('products')->where('id', 635)->first();
$m = DB::table('models')->where('id', $p->model_id)->first();
echo "Product 635 Model: " . ($m ? $m->name : 'NULL') . PHP_EOL;

$pd = DB::table('inv_t_product_detail')->where('product_id', 635)->where('is_active', 1)->first();
$md = DB::table('models')->where('id', $pd->model_id)->first();
echo "Product 635 Detail Model: " . ($md ? $md->name : 'NULL') . PHP_EOL;
