<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$array = [
    ['id' => 1, 'name' => 'Test " Product', 'price' => 5000, 'maxQty' => 1, 'returnQty' => 0, 'condition' => 'good']
];

echo Illuminate\Support\Js::from($array)->toHtml();
