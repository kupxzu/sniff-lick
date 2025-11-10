<?php
require 'bootstrap/app.php';

$app = require_once 'bootstrap/app.php';

use App\Models\Consultation;

try {
    $consultations = Consultation::with('pet')->get();
    echo "Total Consultations: " . count($consultations) . "\n";
    echo "=".str_repeat("=", 80)."\n";
    
    foreach ($consultations as $c) {
        echo "ID: {$c->id}\n";
        echo "Pet ID: {$c->pet_id}\n";
        echo "Date: {$c->consultation_date}\n";
        echo "Weight: {$c->weight}\n";
        echo "Temp: {$c->temperature}\n";
        echo "Complaint: {$c->complaint}\n";
        echo "Diagnosis: {$c->diagnosis}\n";
        echo "-".str_repeat("-", 80)."\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
