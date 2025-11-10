<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Deworming;
use App\Models\DewormTreatment;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DewormingController extends Controller
{
    /**
     * Get all dewormings for a specific pet
     */
    public function index($clientId, $petId)
    {
        $client = User::findOrFail($clientId);
        $pet = Pet::where('client_id', $clientId)->findOrFail($petId);
        
        $dewormings = Deworming::where('pet_id', $petId)
            ->with('dewormTreatments')
            ->orderBy('date', 'desc')
            ->get();
        
        return response()->json($dewormings);
    }

    /**
     * Create a new deworming record
     */
    public function store(Request $request, $clientId, $petId)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'weight' => 'nullable|numeric|min:0',
            'temperature' => 'nullable|numeric|min:0',
            'treatments' => 'required|array|min:1',
            'treatments.*.treatment' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verify pet belongs to client
        $pet = Pet::where('client_id', $clientId)->findOrFail($petId);

        // Create deworming record
        $deworming = Deworming::create([
            'pet_id' => $petId,
            'date' => $request->date,
            'weight' => $request->weight,
            'temperature' => $request->temperature,
        ]);

        // Create treatments
        foreach ($request->treatments as $treatment) {
            DewormTreatment::create([
                'deworming_id' => $deworming->id,
                'treatment' => $treatment['treatment'],
            ]);
        }

        // Load the treatments relationship
        $deworming->load('dewormTreatments');

        return response()->json([
            'success' => true,
            'message' => 'Deworming record created successfully',
            'data' => $deworming
        ], 201);
    }

    /**
     * Update a deworming record
     */
    public function update(Request $request, $clientId, $petId, $dewormingId)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'weight' => 'nullable|numeric|min:0',
            'temperature' => 'nullable|numeric|min:0',
            'treatments' => 'required|array|min:1',
            'treatments.*.treatment' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verify pet belongs to client
        $pet = Pet::where('client_id', $clientId)->findOrFail($petId);
        
        // Find deworming
        $deworming = Deworming::where('pet_id', $petId)->findOrFail($dewormingId);

        // Update deworming
        $deworming->update([
            'date' => $request->date,
            'weight' => $request->weight,
            'temperature' => $request->temperature,
        ]);

        // Delete old treatments and create new ones
        $deworming->dewormTreatments()->delete();
        
        foreach ($request->treatments as $treatment) {
            DewormTreatment::create([
                'deworming_id' => $deworming->id,
                'treatment' => $treatment['treatment'],
            ]);
        }

        // Reload treatments
        $deworming->load('dewormTreatments');

        return response()->json([
            'success' => true,
            'message' => 'Deworming record updated successfully',
            'data' => $deworming
        ]);
    }

    /**
     * Delete a deworming record
     */
    public function destroy($clientId, $petId, $dewormingId)
    {
        // Verify pet belongs to client
        $pet = Pet::where('client_id', $clientId)->findOrFail($petId);
        
        // Find and delete deworming (treatments will cascade delete)
        $deworming = Deworming::where('pet_id', $petId)->findOrFail($dewormingId);
        $deworming->delete();

        return response()->json([
            'success' => true,
            'message' => 'Deworming record deleted successfully'
        ]);
    }
}
