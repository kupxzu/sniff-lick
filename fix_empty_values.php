<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Fixing empty string values in consultations table...\n";

$updated = DB::table('consultations')
    ->where('weight', '')
    ->orWhere('temperature', '')
    ->update([
        'weight' => DB::raw("NULLIF(weight, '')"),
        'temperature' => DB::raw("NULLIF(temperature, '')")
    ]);

echo "Updated {$updated} rows\n";

// Verify
$consultations = DB::table('consultations')
    ->select('id', 'weight', 'temperature')
    ->get();

echo "\nAll consultations after fix:\n";
foreach ($consultations as $c) {
    echo "ID: {$c->id}, Weight: " . ($c->weight ?? 'NULL') . ", Temp: " . ($c->temperature ?? 'NULL') . "\n";
}
