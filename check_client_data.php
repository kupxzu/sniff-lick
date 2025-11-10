<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Get a client user
$user = App\Models\User::where('role', 'client')->first();

if ($user) {
    echo "Client Data:\n";
    echo "ID: " . $user->id . "\n";
    echo "Name: " . $user->name . "\n";
    echo "Email: " . $user->email . "\n";
    echo "Phone: " . ($user->phone ?? 'NULL') . "\n";
    echo "Address: " . ($user->address ?? 'NULL') . "\n";
    
    echo "\n\nJSON Output:\n";
    echo json_encode([
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'phone' => $user->phone,
        'address' => $user->address
    ], JSON_PRETTY_PRINT);
} else {
    echo "No client found\n";
}
