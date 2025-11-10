<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Treatment;
use App\Models\Consultation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class TreatmentController extends Controller
{
    /**
     * Display a listing of treatments for a specific consultation.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $consultationId = $request->get('consultation_id');

            if ($consultationId) {
                // Get treatments for specific consultation
                $consultation = Consultation::findOrFail($consultationId);
                
                // Check access permissions
                if (!$user->isAdmin() && $consultation->pet->client_id !== $user->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Access denied'
                    ], 403);
                }

                $treatments = $consultation->treatments()->get();
            } else {
                // Get all treatments based on user role
                if ($user->isAdmin()) {
                    $treatments = Treatment::with('consultation.pet.client:id,name,username,email')->get();
                } else {
                    $petIds = $user->pets()->pluck('id');
                    $consultationIds = Consultation::whereIn('pet_id', $petIds)->pluck('id');
                    $treatments = Treatment::whereIn('consultation_id', $consultationIds)
                        ->with('consultation.pet')
                        ->get();
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Treatments retrieved successfully',
                'treatments' => $treatments
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve treatments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created treatment.
     * Can be called from both regular route and hierarchical admin route
     */
    public function store(Request $request, $clientId = null, $petId = null, $consultationId = null): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Determine consultation_id from route parameter or request body
            $finalConsultationId = $consultationId ?? $request->consultation_id;
            
            $validationRules = [
                'treatment_type' => 'required|in:medicine,surgery,confinement',
                'meds_name' => 'nullable|string|max:255',
                'treatment_details' => 'nullable|string',
            ];

            // Only require consultation_id in body if not provided via route
            if (!$consultationId) {
                $validationRules['consultation_id'] = 'required|exists:consultations,id';
            }

            $validator = Validator::make($request->all(), $validationRules);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check if user can create treatment for this consultation
            $consultation = Consultation::findOrFail($finalConsultationId);
            
            if (!$user->isAdmin() && $consultation->pet->client_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. You can only create treatments for your own pet consultations.'
                ], 403);
            }

            // For hierarchical routes, validate the client-pet-consultation chain
            if ($clientId && $petId) {
                if ($consultation->pet_id != $petId || $consultation->pet->client_id != $clientId) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid consultation for the specified client and pet'
                    ], 400);
                }
            }

            $treatment = Treatment::create([
                'consultation_id' => $finalConsultationId,
                'treatment_type' => $request->treatment_type,
                'meds_name' => $request->meds_name,
                'treatment_details' => $request->treatment_details,
            ]);

            // Load consultation relationship
            $treatment->load('consultation.pet.client:id,name,username,email');

            return response()->json([
                'success' => true,
                'message' => 'Treatment created successfully',
                'treatment' => $treatment
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Treatment creation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified treatment.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        try {
            $user = $request->user();
            
            $treatment = Treatment::with('consultation.pet.client:id,name,username,email')->findOrFail($id);

            // Check access permissions
            if (!$user->isAdmin() && $treatment->consultation->pet->client_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'message' => 'Treatment retrieved successfully',
                'treatment' => $treatment
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Treatment not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve treatment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified treatment.
     * Supports both hierarchical route (client/pet/consultation/treatment) and direct route
     */
    public function update(Request $request, $clientId = null, $petId = null, $consultationId = null, $treatment = null): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Determine treatment ID from route parameter
            $treatmentId = $treatment ?? $request->route('id');
            
            $treatmentModel = Treatment::findOrFail($treatmentId);

            // Check access permissions
            if (!$user->isAdmin() && $treatmentModel->consultation->pet->client_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied'
                ], 403);
            }

            // For hierarchical routes, validate the client-pet-consultation chain
            if ($clientId && $petId && $consultationId) {
                if ($treatmentModel->consultation_id != $consultationId || 
                    $treatmentModel->consultation->pet_id != $petId || 
                    $treatmentModel->consultation->pet->client_id != $clientId) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid treatment for the specified client, pet, and consultation'
                    ], 400);
                }
            }

            $validator = Validator::make($request->all(), [
                'treatment_type' => 'sometimes|in:medicine,surgery,confinement',
                'meds_name' => 'sometimes|nullable|string|max:255',
                'treatment_details' => 'sometimes|nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            $treatmentModel->update($request->only([
                'treatment_type',
                'meds_name',
                'treatment_details'
            ]));

            $treatmentModel->load('consultation.pet.client:id,name,username,email');

            return response()->json([
                'success' => true,
                'message' => 'Treatment updated successfully',
                'treatment' => $treatmentModel
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Treatment not found'
            ], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Treatment update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified treatment.
     * Supports both hierarchical route (client/pet/consultation/treatment) and direct route
     */
    public function destroy(Request $request, $clientId = null, $petId = null, $consultationId = null, $treatment = null): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Determine treatment ID from route parameter
            $treatmentId = $treatment ?? $request->route('id');
            
            $treatmentModel = Treatment::findOrFail($treatmentId);

            // Check access permissions
            if (!$user->isAdmin() && $treatmentModel->consultation->pet->client_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied'
                ], 403);
            }

            // For hierarchical routes, validate the client-pet-consultation chain
            if ($clientId && $petId && $consultationId) {
                if ($treatmentModel->consultation_id != $consultationId || 
                    $treatmentModel->consultation->pet_id != $petId || 
                    $treatmentModel->consultation->pet->client_id != $clientId) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid treatment for the specified client, pet, and consultation'
                    ], 400);
                }
            }

            $treatmentType = $treatmentModel->treatment_type;
            $treatmentModel->delete();

            return response()->json([
                'success' => true,
                'message' => "Treatment ({$treatmentType}) deleted successfully"
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Treatment not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Treatment deletion failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get treatments for a specific consultation (Admin hierarchical route)
     * Route: GET /api/admin/clients/{client}/pets/{pet}/consultations/{consultation}/treatments
     */
    public function consultationTreatments(Request $request, string $clientId, string $petId, string $consultationId): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Only admin can access this route
            if (!$user->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Admin access required'
                ], 403);
            }

            $treatments = Treatment::where('consultation_id', $consultationId)
                                 ->whereHas('consultation.pet', function ($query) use ($clientId, $petId) {
                                     $query->where('client_id', $clientId)->where('id', $petId);
                                 })
                                 ->with('consultation.pet.client:id,name,username,email')
                                 ->get();

            return response()->json([
                'success' => true,
                'message' => 'Consultation treatments retrieved successfully',
                'treatments' => $treatments
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve consultation treatments',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
