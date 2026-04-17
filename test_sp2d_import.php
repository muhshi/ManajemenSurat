<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$rows = Maatwebsite\Excel\Facades\Excel::toArray(new stdClass, 'storage/app/public/sp2d-uploads/01KPD4094QK68DT03RBM75RKKK.xlsx');
print_r(array_slice($rows[0], 0, 5));
