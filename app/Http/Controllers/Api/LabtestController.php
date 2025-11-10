<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Labtest;
use App\Models\Consultation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class LabtestController extends Controller
{
    /**
     * Display a listing of labtests for a specific consultation.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $consultationId = $request->get('consultation_id');

            if ($consultationId) {
                // Get labtests for specific consultation
                $consultation = Consultation::findOrFail($consultationId);
                
                // Check access permissions
                if (!$user->isAdmin() && $consultation->pet->client_id !== $user->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Access denied'
                    ], 403);
                }

                $labtests = $consultation->labtests()->get();
            } else {
                // Get all labtests based on user role
                if ($user->isAdmin()) {
                    $labtests = Labtest::with('consultation.pet.client:id,name,username,email')->get();
                } else {
                    $petIds = $user->pets()->pluck('id');
                    $consultationIds = Consultation::whereIn('pet_id', $petIds)->pluck('id');
                    $labtests = Labtest::whereIn('consultation_id', $consultationIds)
                        ->with('consultation.pet')
                        ->get();
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Lab tests retrieved successfully',
                'labtests' => $labtests
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve lab tests',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created labtest.
     * Can be called from both regular route and hierarchical admin route
     */
    public function store(Request $request, $clientId = null, $petId = null, $consultationId = null): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Determine consultation_id from route parameter or request body
            $finalConsultationId = $consultationId ?? $request->input('consultation_id');
            
            $validationRules = [
                'lab_types' => 'required|in:cbc,microscopy,bloodchem,ultrasound,xray',
                'notes' => 'nullable|string',
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

            // Check if user can create labtest for this consultation
            $consultation = Consultation::findOrFail($finalConsultationId);
            
            if (!$user->isAdmin() && $consultation->pet->client_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. You can only create lab tests for your own pet consultations.'
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

            $labtest = Labtest::create([
                'consultation_id' => $finalConsultationId,
                'lab_types' => $request->input('lab_types'),
                'photo_result' => null,
                'notes' => $request->input('notes'),
            ]);

            // Load consultation relationship
            $labtest->load('consultation.pet.client:id,name,username,email');

            return response()->json([
                'success' => true,
                'message' => 'Lab test created successfully',
                'labtest' => $labtest
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
                'message' => 'Lab test creation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified labtest.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        try {
            $user = $request->user();
            
            $labtest = Labtest::with('consultation.pet.client:id,name,username,email')->findOrFail($id);

            // Check access permissions
            if (!$user->isAdmin() && $labtest->consultation->pet->client_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'message' => 'Lab test retrieved successfully',
                'labtest' => $labtest
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lab test not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve lab test',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified labtest.
     */
    public function update(Request $request, $clientId = null, $petId = null, $consultationId = null, $labtest = null): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Determine labtest ID from route parameter
            $labtestId = $labtest ?? $request->route('id');
            
            $labtestModel = Labtest::findOrFail($labtestId);

            // Check access permissions
            if (!$user->isAdmin() && $labtestModel->consultation->pet->client_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied'
                ], 403);
            }

            // For hierarchical routes, validate the client-pet-consultation chain
            if ($clientId && $petId && $consultationId) {
                if ($labtestModel->consultation_id != $consultationId ||
                    $labtestModel->consultation->pet_id != $petId ||
                    $labtestModel->consultation->pet->client_id != $clientId) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid lab test for the specified client, pet, and consultation'
                    ], 400);
                }
            }

            $validator = Validator::make($request->all(), [
                'lab_types' => 'sometimes|in:cbc,microscopy,bloodchem,ultrasound,xray',
                'notes' => 'sometimes|nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            $updateData = [];
            if ($request->has('lab_types')) {
                $updateData['lab_types'] = $request->input('lab_types');
            }
            if ($request->has('notes')) {
                $updateData['notes'] = $request->input('notes');
            }

            $labtestModel->update($updateData);

            $labtestModel->load('consultation.pet.client:id,name,username,email');

            return response()->json([
                'success' => true,
                'message' => 'Lab test updated successfully',
                'labtest' => $labtestModel
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lab test not found'
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
                'message' => 'Lab test update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified labtest.
     * Can be called from both hierarchical route and regular route
     */
    public function destroy(Request $request, $clientId = null, $petId = null, $consultationId = null, $labtest = null): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Determine labtest ID from route parameter or path parameter
            $labtestId = $labtest ?? $request->route('id');
            
            $labtestModel = Labtest::findOrFail($labtestId);

            // Check access permissions
            if (!$user->isAdmin() && $labtestModel->consultation->pet->client_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied'
                ], 403);
            }

            // For hierarchical routes, validate the client-pet-consultation-labtest chain
            if ($clientId && $petId && $consultationId) {
                if ($labtestModel->consultation_id != $consultationId ||
                    $labtestModel->consultation->pet_id != $petId ||
                    $labtestModel->consultation->pet->client_id != $clientId) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid lab test for the specified client, pet, and consultation'
                    ], 400);
                }
            }

            $labType = $labtestModel->lab_types;
            $labtestModel->delete();

            return response()->json([
                'success' => true,
                'message' => "Lab test ({$labType}) deleted successfully"
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lab test not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lab test deletion failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get labtests for a specific consultation (Admin hierarchical route)
     * Route: GET /api/admin/clients/{client}/pets/{pet}/consultations/{consultation}/labtests
     */
    public function consultationLabtests(Request $request, string $clientId, string $petId, string $consultationId): JsonResponse
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

            $labtests = Labtest::where('consultation_id', $consultationId)
                              ->whereHas('consultation.pet', function ($query) use ($clientId, $petId) {
                                  $query->where('client_id', $clientId)->where('id', $petId);
                              })
                              ->with('consultation.pet.client:id,name,username,email')
                              ->get();

            return response()->json([
                'success' => true,
                'message' => 'Consultation lab tests retrieved successfully',
                'labtests' => $labtests
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve consultation lab tests',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
