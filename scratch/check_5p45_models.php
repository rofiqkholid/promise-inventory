<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$models = DB::table('models')->where('name', 'like', '5P45%')->get();
foreach ($models as $m) {
    echo "ID: {$m->id}, Name: {$m->name}" . PHP_EOL;
}
