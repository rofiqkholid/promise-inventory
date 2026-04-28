<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$p = DB::table('products')->where('part_no', '76366E000P')->first();
$m = DB::table('models')->where('id', $p->model_id)->first();
echo "Part 76366E000P Model: " . ($m ? $m->name : 'NULL') . PHP_EOL;
