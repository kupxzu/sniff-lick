<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$consultations = DB::table('consultations')
    ->where('pet_id', 1)
    ->select('id', 'weight', 'temperature', 'consultation_date')
    ->get();

foreach ($consultations as $c) {
    echo "ID: {$c->id}, Weight: " . ($c->weight ?? 'NULL') . ", Temp: " . ($c->temperature ?? 'NULL') . "\n";
}
