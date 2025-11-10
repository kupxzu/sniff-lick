<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    // Get a client user
    $user = \App\Models\User::where('role', 'client')->first();
    echo "Testing with user: {$user->email} (ID: {$user->id})\n\n";
    
    // Try to get pets the way the controller does
    $pets = \App\Models\Pet::where('client_id', $user->id)
        ->with([
            'consultations' => function ($query) {
                $query->latest('consultation_date')->take(5);
            },
            'consultations.treatments',
            'consultations.prescriptions',
            'consultations.labtests',
            'vaccinations' => function ($query) {
                $query->latest('date')->take(5);
            },
            'vaccinations.vacTreatments'
        ])
        ->get();
    
    echo "Success! Found " . $pets->count() . " pets\n";
    
    foreach ($pets as $pet) {
        echo "- {$pet->name}: " . $pet->consultations->count() . " consultations, " . $pet->vaccinations->count() . " vaccinations\n";
    }
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n";
}
