<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$pdo = DB::connection()->getPdo();
$stmt = $pdo->query('DESCRIBE users');
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Users table schema:\n\n";
foreach ($columns as $col) {
    echo "- " . $col['Field'] . " (" . $col['Type'] . ")" . " NULL=" . $col['Null'] . "\n";
}

echo "\n\nChecking if phone and address columns exist:\n";
$hasPhone = false;
$hasAddress = false;
foreach ($columns as $col) {
    if ($col['Field'] === 'phone') $hasPhone = true;
    if ($col['Field'] === 'address') $hasAddress = true;
}

echo "Phone column exists: " . ($hasPhone ? "YES" : "NO") . "\n";
echo "Address column exists: " . ($hasAddress ? "YES" : "NO") . "\n";
