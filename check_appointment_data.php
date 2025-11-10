<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Get an appointment with client data
$appointment = App\Models\Appointment::with(['client', 'pet'])->first();

if ($appointment) {
    echo "Appointment Data:\n";
    echo "ID: " . $appointment->id . "\n";
    echo "Date: " . $appointment->appointment_date . "\n";
    echo "Type: " . $appointment->types . "\n\n";
    
    echo "Client Data:\n";
    echo "ID: " . $appointment->client->id . "\n";
    echo "Name: " . $appointment->client->name . "\n";
    echo "Email: " . $appointment->client->email . "\n";
    echo "Phone: " . ($appointment->client->phone ?? 'NULL') . "\n";
    echo "Address: " . ($appointment->client->address ?? 'NULL') . "\n";
    
    echo "\n\nJSON Output (like API response):\n";
    echo json_encode([
        'id' => $appointment->id,
        'appointment_date' => $appointment->appointment_date,
        'types' => $appointment->types,
        'client' => [
            'id' => $appointment->client->id,
            'name' => $appointment->client->name,
            'email' => $appointment->client->email,
            'phone' => $appointment->client->phone,
            'address' => $appointment->client->address,
        ]
    ], JSON_PRETTY_PRINT);
} else {
    echo "No appointment found\n";
}
