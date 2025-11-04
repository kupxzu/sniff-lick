<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Prescription;
use App\Models\Consultation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PrescriptionController extends Controller
{
    /**
     * Display a listing of prescriptions for a specific consultation.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $consultationId = $request->get('consultation_id');

            if ($consultationId) {
                // Get prescriptions for specific consultation
                $consultation = Consultation::findOrFail($consultationId);
                
                // Check access permissions
                if (!$user->isAdmin() && $consultation->pet->client_id !== $user->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Access denied'
                    ], 403);
                }

                $prescriptions = $consultation->prescriptions()->get();
            } else {
                // Get all prescriptions based on user role
                if ($user->isAdmin()) {
                    $prescriptions = Prescription::with('consultation.pet.client:id,name,username,email')->get();
                } else {
                    $petIds = $user->pets()->pluck('id');
                    $consultationIds = Consultation::whereIn('pet_id', $petIds)->pluck('id');
                    $prescriptions = Prescription::whereIn('consultation_id', $consultationIds)
                        ->with('consultation.pet')
                        ->get();
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Prescriptions retrieved successfully',
                'prescriptions' => $prescriptions
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve prescriptions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created prescription.
     * Can be called from both regular route and hierarchical admin route
     */
    public function store(Request $request, $clientId = null, $petId = null, $consultationId = null): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Determine consultation_id from route parameter or request body
            $finalConsultationId = $consultationId ?? $request->consultation_id;
            
            $validationRules = [
                'upload_photo' => 'nullable|array',
                'upload_photo.*' => 'string', // Photo file paths
                'description' => 'nullable|string',
            ];

            // Only require consultation_id in body if not provided via route
            if (!$consultationId) {
                $validationRules['consultation_id'] = 'required|exists:consultations,id';
            }

            $validator = Validator::make($request->all(), $validationRules);

            // At least one of photo or description must be provided
            $validator->after(function ($validator) use ($request) {
                if (!$request->upload_photo && !$request->description) {
                    $validator->errors()->add('prescription', 'Either upload_photo or description must be provided.');
                }
            });

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check if user can create prescription for this consultation
            $consultation = Consultation::findOrFail($finalConsultationId);
            
            if (!$user->isAdmin() && $consultation->pet->client_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. You can only create prescriptions for your own pet consultations.'
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

            $prescription = Prescription::create([
                'consultation_id' => $finalConsultationId,
                'upload_photo' => $request->upload_photo,
                'description' => $request->description,
            ]);

            // Load consultation relationship
            $prescription->load('consultation.pet.client:id,name,username,email');

            return response()->json([
                'success' => true,
                'message' => 'Prescription created successfully',
                'prescription' => $prescription
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
                'message' => 'Prescription creation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified prescription.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        try {
            $user = $request->user();
            
            $prescription = Prescription::with('consultation.pet.client:id,name,username,email')->findOrFail($id);

            // Check access permissions
            if (!$user->isAdmin() && $prescription->consultation->pet->client_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'message' => 'Prescription retrieved successfully',
                'prescription' => $prescription
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Prescription not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve prescription',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified prescription.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $user = $request->user();
            
            $prescription = Prescription::findOrFail($id);

            // Check access permissions
            if (!$user->isAdmin() && $prescription->consultation->pet->client_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'upload_photo' => 'sometimes|nullable|string|max:255',
                'description' => 'sometimes|nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            $prescription->update($request->only([
                'upload_photo',
                'description'
            ]));

            $prescription->load('consultation.pet.client:id,name,username,email');

            return response()->json([
                'success' => true,
                'message' => 'Prescription updated successfully',
                'prescription' => $prescription
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Prescription not found'
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
                'message' => 'Prescription update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified prescription.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            $user = $request->user();
            
            $prescription = Prescription::findOrFail($id);

            // Check access permissions
            if (!$user->isAdmin() && $prescription->consultation->pet->client_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied'
                ], 403);
            }

            $prescription->delete();

            return response()->json([
                'success' => true,
                'message' => 'Prescription deleted successfully'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Prescription not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Prescription deletion failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get prescriptions for a specific consultation (Admin hierarchical route)
     * Route: GET /api/admin/clients/{client}/pets/{pet}/consultations/{consultation}/prescriptions
     */
    public function consultationPrescriptions(Request $request, string $clientId, string $petId, string $consultationId): JsonResponse
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

            $prescriptions = Prescription::where('consultation_id', $consultationId)
                                        ->whereHas('consultation.pet', function ($query) use ($clientId, $petId) {
                                            $query->where('client_id', $clientId)->where('id', $petId);
                                        })
                                        ->with('consultation.pet.client:id,name,username,email')
                                        ->get();

            return response()->json([
                'success' => true,
                'message' => 'Consultation prescriptions retrieved successfully',
                'prescriptions' => $prescriptions
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve consultation prescriptions',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
