<?php
use Illuminate\Support\Facades\DB;
use App\Models\Consultation;
use App\Models\Pet;
use App\Models\User;

echo "Database Check:\n";
echo "===============\n";

try {
    $consultationCount = Consultation::count();
    $petCount = Pet::count();
    $userCount = User::count();
    
    echo "Consultations: $consultationCount\n";
    echo "Pets: $petCount\n";
    echo "Users: $userCount\n";
    
    if ($petCount > 0) {
        $pet = Pet::first();
        echo "\nFirst Pet:\n";
        echo "- ID: {$pet->id}\n";
        echo "- Name: {$pet->name}\n";
        echo "- Client ID: {$pet->client_id}\n";
    }
    
    if ($consultationCount > 0) {
        $consultation = Consultation::first();
        echo "\nFirst Consultation:\n";
        echo "- ID: {$consultation->id}\n";
        echo "- Pet ID: {$consultation->pet_id}\n";
        echo "- Date: {$consultation->consultation_date}\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
