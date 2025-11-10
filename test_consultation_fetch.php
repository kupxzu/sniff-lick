<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Consultation;

try {
    echo "Fetching consultations for pet_id=1...\n";
    
    $consultations = Consultation::where('pet_id', 1)
                                ->whereHas('pet', function ($query) {
                                    $query->where('client_id', 3);
                                })
                                ->with([
                                    'pet.client:id,name,username,email',
                                    'labtests',
                                    'treatments', 
                                    'prescriptions'
                                ])
                                ->orderBy('consultation_date', 'desc')
                                ->get();

    echo "Found " . $consultations->count() . " consultations\n";
    
    foreach ($consultations as $consultation) {
        echo "\nConsultation ID: {$consultation->id}\n";
        echo "Date: {$consultation->consultation_date}\n";
        echo "Weight: {$consultation->weight}\n";
        echo "Temperature: {$consultation->temperature}\n";
        echo "Complaint: {$consultation->complaint}\n";
        echo "Diagnosis: {$consultation->diagnosis}\n";
        echo "Pet: {$consultation->pet->name}\n";
        echo "Labtests: " . $consultation->labtests->count() . "\n";
        echo "Treatments: " . $consultation->treatments->count() . "\n";
        echo "Prescriptions: " . $consultation->prescriptions->count() . "\n";
    }
    
    echo "\nJSON Output:\n";
    echo json_encode([
        'success' => true,
        'consultations' => $consultations
    ], JSON_PRETTY_PRINT);
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
