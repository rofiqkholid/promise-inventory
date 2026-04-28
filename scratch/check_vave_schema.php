<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$sample = DB::table('inv_m_vave_base')->orderBy('id', 'desc')->first();
print_r($sample);
