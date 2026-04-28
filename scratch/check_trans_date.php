<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$t = DB::table('inv_t_inventory_transaction')->where('id', 6)->first();
echo "Transaction 6 date: {$t->transaction_date}" . PHP_EOL;
