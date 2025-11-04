<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Models\Pet;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ConsultationController extends Controller
{
    /**
     * Display a listing of consultations.
     * Admin: can see all consultations
     * Client: can see only consultations for their pets
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            if ($user->isAdmin()) {
                // Admin can see all consultations with pet and client info
                $consultations = Consultation::with([
                    'pet.client:id,name,username,email',
                    'labtests',
                    'treatments',
                    'prescriptions'
                ])->orderBy('consultation_date', 'desc')->get();
            } else {
                // Client can see only consultations for their pets
                $petIds = $user->pets()->pluck('id');
                $consultations = Consultation::whereIn('pet_id', $petIds)
                    ->with(['pet', 'labtests', 'treatments', 'prescriptions'])
                    ->orderBy('consultation_date', 'desc')
                    ->get();
            }

            return response()->json([
                'success' => true,
                'message' => 'Consultations retrieved successfully',
                'consultations' => $consultations
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve consultations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created consultation.
     * Admin: can create consultations for any pet
     * Client: can create consultations only for their pets
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            $validationRules = [
                'pet_id' => 'required|exists:pets,id',
                'consultation_date' => 'required|date',
                'weight' => 'nullable|numeric|min:0|max:999.99',
                'temperature' => 'nullable|numeric|min:0|max:99.99',
                'complaint' => 'nullable|string',
                'diagnosis' => 'nullable|string',
            ];

            $validator = Validator::make($request->all(), $validationRules);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check if user can create consultation for this pet
            $pet = Pet::findOrFail($request->pet_id);
            
            if (!$user->isAdmin() && $pet->client_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. You can only create consultations for your own pets.'
                ], 403);
            }

            $consultation = Consultation::create([
                'pet_id' => $request->pet_id,
                'consultation_date' => $request->consultation_date,
                'weight' => $request->weight,
                'temperature' => $request->temperature,
                'complaint' => $request->complaint,
                'diagnosis' => $request->diagnosis,
            ]);

            // Load relationships for response
            $consultation->load(['pet.client:id,name,username,email', 'labtests']);

            return response()->json([
                'success' => true,
                'message' => 'Consultation created successfully',
                'consultation' => $consultation
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
                'message' => 'Consultation creation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified consultation.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        try {
            $user = $request->user();
            
            $consultation = Consultation::with([
                'pet.client:id,name,username,email',
                'labtests',
                'treatments',
                'prescriptions'
            ])->findOrFail($id);

            // Check access permissions
            if (!$user->isAdmin() && $consultation->pet->client_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'message' => 'Consultation retrieved successfully',
                'consultation' => $consultation
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Consultation not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve consultation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified consultation.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $user = $request->user();
            
            $consultation = Consultation::findOrFail($id);

            // Check access permissions
            if (!$user->isAdmin() && $consultation->pet->client_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied'
                ], 403);
            }

            $validationRules = [
                'consultation_date' => 'sometimes|date',
                'weight' => 'sometimes|nullable|numeric|min:0|max:999.99',
                'temperature' => 'sometimes|nullable|numeric|min:0|max:99.99',
                'complaint' => 'sometimes|nullable|string',
                'diagnosis' => 'sometimes|nullable|string',
            ];

            $validator = Validator::make($request->all(), $validationRules);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            $consultation->update($request->only([
                'consultation_date',
                'weight',
                'temperature',
                'complaint',
                'diagnosis'
            ]));

            // Load relationships for response
            $consultation->load(['pet.client:id,name,username,email', 'labtests', 'treatments', 'prescriptions']);

            return response()->json([
                'success' => true,
                'message' => 'Consultation updated successfully',
                'consultation' => $consultation
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Consultation not found'
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
                'message' => 'Consultation update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified consultation.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            $user = $request->user();
            
            $consultation = Consultation::findOrFail($id);

            // Check access permissions
            if (!$user->isAdmin() && $consultation->pet->client_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied'
                ], 403);
            }

            $petName = $consultation->pet->name;
            $consultationDate = $consultation->consultation_date->format('Y-m-d H:i');
            
            $consultation->delete();

            return response()->json([
                'success' => true,
                'message' => "Consultation for {$petName} on {$consultationDate} deleted successfully"
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Consultation not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Consultation deletion failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get consultations for a specific pet (Admin hierarchical route)
     * Route: GET /api/admin/clients/{client}/pets/{pet}/consultations
     */
    public function petConsultations(Request $request, string $clientId, string $petId): JsonResponse
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

            $consultations = Consultation::where('pet_id', $petId)
                                        ->whereHas('pet', function ($query) use ($clientId) {
                                            $query->where('client_id', $clientId);
                                        })
                                        ->with([
                                            'pet.client:id,name,username,email',
                                            'labtests',
                                            'treatments', 
                                            'prescriptions'
                                        ])
                                        ->orderBy('consultation_date', 'desc')
                                        ->get();

            return response()->json([
                'success' => true,
                'message' => 'Pet consultations retrieved successfully',
                'consultations' => $consultations
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve pet consultations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get latest records for a consultation (Admin hierarchical route)
     * Route: GET /api/admin/clients/{client}/pets/{pet}/consultations/{consultation}/latest
     */
    public function latestRecords(Request $request, string $clientId, string $petId, string $consultationId): JsonResponse
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

            $consultation = Consultation::where('id', $consultationId)
                                       ->where('pet_id', $petId)
                                       ->whereHas('pet', function ($query) use ($clientId) {
                                           $query->where('client_id', $clientId);
                                       })
                                       ->with([
                                           'pet.client:id,name,username,email',
                                           'labtests' => function ($query) {
                                               $query->orderBy('created_at', 'desc')->limit(5);
                                           },
                                           'treatments' => function ($query) {
                                               $query->orderBy('created_at', 'desc')->limit(5);
                                           },
                                           'prescriptions' => function ($query) {
                                               $query->orderBy('created_at', 'desc')->limit(5);
                                           }
                                       ])
                                       ->firstOrFail();

            return response()->json([
                'success' => true,
                'message' => 'Latest consultation records retrieved successfully',
                'consultation' => $consultation
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Consultation not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve latest records',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
