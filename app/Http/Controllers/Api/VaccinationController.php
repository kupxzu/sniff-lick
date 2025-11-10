<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vaccination;
use App\Models\VacTreatment;
use App\Models\Pet;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class VaccinationController extends Controller
{
    /**
     * Display a listing of vaccinations for a specific pet.
     */
    public function index($clientId = null, $petId = null): JsonResponse
    {
        try {
            // Validate pet exists and belongs to client
            $pet = Pet::where('id', $petId)
                ->where('client_id', $clientId)
                ->first();

            if (!$pet) {
                return response()->json([
                    'message' => 'Pet not found or does not belong to this client'
                ], 404);
            }

            $vaccinations = Vaccination::where('pet_id', $petId)
                ->with('vacTreatments')
                ->orderBy('date', 'desc')
                ->get();

            return response()->json($vaccinations);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching vaccinations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created vaccination.
     */
    public function store(Request $request, $clientId = null, $petId = null): JsonResponse
    {
        try {
            // Validate pet exists and belongs to client
            $pet = Pet::where('id', $petId)
                ->where('client_id', $clientId)
                ->first();

            if (!$pet) {
                return response()->json([
                    'message' => 'Pet not found or does not belong to this client'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'date' => 'required|date',
                'weight' => 'nullable|numeric',
                'temperature' => 'nullable|numeric',
                'treatments' => 'required|array|min:1',
                'treatments.*.treatment' => 'required|string|max:255',
                'treatments.*.dose' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Create vaccination
            $vaccination = Vaccination::create([
                'pet_id' => $petId,
                'date' => $request->date,
                'weight' => $request->weight,
                'temperature' => $request->temperature,
            ]);

            // Create vac treatments
            foreach ($request->treatments as $treatment) {
                VacTreatment::create([
                    'vac_id' => $vaccination->id,
                    'treatment' => $treatment['treatment'],
                    'dose' => $treatment['dose'],
                ]);
            }

            // Load treatments relation
            $vaccination->load('vacTreatments');

            return response()->json([
                'message' => 'Vaccination created successfully',
                'vaccination' => $vaccination
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating vaccination',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified vaccination.
     */
    public function show($clientId = null, $petId = null, $vaccinationId = null): JsonResponse
    {
        try {
            $vaccination = Vaccination::where('id', $vaccinationId)
                ->where('pet_id', $petId)
                ->with('vacTreatments')
                ->first();

            if (!$vaccination) {
                return response()->json([
                    'message' => 'Vaccination not found'
                ], 404);
            }

            return response()->json($vaccination);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching vaccination',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified vaccination.
     */
    public function update(Request $request, $clientId = null, $petId = null, $vaccinationId = null): JsonResponse
    {
        try {
            $vaccination = Vaccination::where('id', $vaccinationId)
                ->where('pet_id', $petId)
                ->first();

            if (!$vaccination) {
                return response()->json([
                    'message' => 'Vaccination not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'date' => 'required|date',
                'weight' => 'nullable|numeric',
                'temperature' => 'nullable|numeric',
                'treatments' => 'required|array|min:1',
                'treatments.*.treatment' => 'required|string|max:255',
                'treatments.*.dose' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Update vaccination
            $vaccination->update([
                'date' => $request->date,
                'weight' => $request->weight,
                'temperature' => $request->temperature,
            ]);

            // Delete existing treatments and create new ones
            $vaccination->vacTreatments()->delete();
            foreach ($request->treatments as $treatment) {
                VacTreatment::create([
                    'vac_id' => $vaccination->id,
                    'treatment' => $treatment['treatment'],
                    'dose' => $treatment['dose'],
                ]);
            }

            // Load treatments relation
            $vaccination->load('vacTreatments');

            return response()->json([
                'message' => 'Vaccination updated successfully',
                'vaccination' => $vaccination
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating vaccination',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified vaccination.
     */
    public function destroy($clientId = null, $petId = null, $vaccinationId = null): JsonResponse
    {
        try {
            $vaccination = Vaccination::where('id', $vaccinationId)
                ->where('pet_id', $petId)
                ->first();

            if (!$vaccination) {
                return response()->json([
                    'message' => 'Vaccination not found'
                ], 404);
            }

            // Delete vaccination (treatments will be cascade deleted)
            $vaccination->delete();

            return response()->json([
                'message' => 'Vaccination deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting vaccination',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
