<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Get all client users
$clients = App\Models\User::where('role', 'client')->get();

echo "Found " . $clients->count() . " clients\n\n";

$samplePhones = [
    '0917 123 4567',
    '0918 234 5678',
    '0919 345 6789',
    '0920 456 7890',
];

$sampleAddresses = [
    'Brgy. Centro 1, Tuguegarao City, Cagayan',
    'Brgy. Ugac Sur, Tuguegarao City, Cagayan',
    'Brgy. Atulayan Norte, Tuguegarao City, Cagayan',
    'Brgy. Caritan Centro, Tuguegarao City, Cagayan',
];

foreach ($clients as $index => $client) {
    $phoneIndex = $index % count($samplePhones);
    $addressIndex = $index % count($sampleAddresses);
    
    $client->phone = $samplePhones[$phoneIndex];
    $client->address = $sampleAddresses[$addressIndex];
    $client->save();
    
    echo "Updated Client ID {$client->id}: {$client->name}\n";
    echo "  Phone: {$client->phone}\n";
    echo "  Address: {$client->address}\n\n";
}

echo "All clients updated successfully!\n";
