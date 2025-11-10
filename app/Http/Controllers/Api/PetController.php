<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pet;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PetController extends Controller
{
    /**
     * Display a listing of pets.
     * Admin: can see all pets
     * Client: can see only their own pets
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            if ($user->isAdmin()) {
                // Admin can see all pets with client info
                $pets = Pet::with(['client' => function ($query) {
                    $query->select('id', 'name', 'username', 'email');
                }])->get();
            } else {
                // Client can see only their own pets
                $pets = $user->pets()->get();
            }

            return response()->json([
                'success' => true,
                'message' => 'Pets retrieved successfully',
                'pets' => $pets
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve pets',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created pet in storage.
     * Admin: can create pets for any client by specifying client in route
     * Client: can create pets only for themselves
     */
    public function store(Request $request, $client = null): JsonResponse
    {
        try {
            $user = $request->user();
            
            $validationRules = [
                'name' => 'required|string|max:255',
                'age' => 'required|integer|min:0|max:50',
                'species' => 'required|in:canine,feline',
                'breed' => 'required|string|max:255',
                'colormark' => 'required|string|max:255',
            ];

            $validator = Validator::make($request->all(), $validationRules);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Determine client_id based on route or user
            if ($client) {
                // Admin using hierarchical route
                $clientId = $client;
            } else {
                // Client creating for themselves or legacy route
                $clientId = $user->isAdmin() && $request->has('client_id') 
                    ? $request->client_id 
                    : $user->id;
            }

            $pet = Pet::create([
                'client_id' => $clientId,
                'name' => $request->name,
                'age' => $request->age,
                'species' => $request->species,
                'breed' => $request->breed,
                'colormark' => $request->colormark,
            ]);

            // Load client relationship for response
            $pet->load('client:id,name,username,email');

            return response()->json([
                'success' => true,
                'message' => 'Pet created successfully',
                'pet' => $pet
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
                'message' => 'Pet creation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified pet.
     * Admin: can view any pet
     * Client: can view only their own pets
     */
    public function show(Request $request, string $client, string $pet): JsonResponse
    {
        try {
            $user = $request->user();
            
            if ($user->isAdmin()) {
                // Admin can see any pet with client info
                $petModel = Pet::with('client:id,name,username,email')->findOrFail($pet);
            } else {
                // Client can see only their own pets
                $petModel = $user->pets()->findOrFail($pet);
            }

            return response()->json([
                'success' => true,
                'message' => 'Pet retrieved successfully',
                'pet' => $petModel
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pet not found or access denied'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve pet',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified pet in storage.
     * Admin: can update any pet and change client_id
     * Client: can update only their own pets
     */
    public function update(Request $request, string $client, string $pet): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Find pet based on user role
            if ($user->isAdmin()) {
                $petModel = Pet::findOrFail($pet);
            } else {
                $petModel = $user->pets()->findOrFail($pet);
            }

            $validationRules = [
                'name' => 'sometimes|string|max:255',
                'age' => 'sometimes|integer|min:0|max:50',
                'species' => 'sometimes|in:canine,feline',
                'breed' => 'sometimes|string|max:255',
                'colormark' => 'sometimes|string|max:255',
            ];

            // Admin can change client_id, client cannot
            if ($user->isAdmin()) {
                $validationRules['client_id'] = 'sometimes|exists:users,id';
            }

            $validator = Validator::make($request->all(), $validationRules);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            $updateData = [];
            
            if ($request->has('name')) {
                $updateData['name'] = $request->name;
            }
            
            if ($request->has('age')) {
                $updateData['age'] = $request->age;
            }
            
            if ($request->has('species')) {
                $updateData['species'] = $request->species;
            }
            
            if ($request->has('breed')) {
                $updateData['breed'] = $request->breed;
            }
            
            if ($request->has('colormark')) {
                $updateData['colormark'] = $request->colormark;
            }

            // Admin can change client_id
            if ($user->isAdmin() && $request->has('client_id')) {
                $updateData['client_id'] = $request->client_id;
            }

            $petModel->update($updateData);

            // Load client relationship for response
            $petModel->load('client:id,name,username,email');

            return response()->json([
                'success' => true,
                'message' => 'Pet updated successfully',
                'pet' => $petModel
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pet not found or access denied'
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
                'message' => 'Pet update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified pet from storage.
     * Admin: can delete any pet
     * Client: can delete only their own pets
     */
    public function destroy(Request $request, string $client, string $pet): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Find pet based on user role
            if ($user->isAdmin()) {
                $petModel = Pet::findOrFail($pet);
            } else {
                $petModel = $user->pets()->findOrFail($pet);
            }
            
            $petName = $petModel->name;
            $clientName = $petModel->client->name;
            
            $petModel->delete();

            $message = $user->isAdmin() 
                ? "Pet '{$petName}' (owned by {$clientName}) deleted successfully"
                : "Pet '{$petName}' deleted successfully";

            return response()->json([
                'success' => true,
                'message' => $message
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pet not found or access denied'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pet deletion failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get pets for a specific client (Admin hierarchical route)
     * Route: GET /api/admin/clients/{client}/pets
     */
    public function clientPets(Request $request, string $clientId): JsonResponse
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

            $pets = Pet::where('client_id', $clientId)
                       ->with(['client:id,name,username,email'])
                       ->get();

            return response()->json([
                'success' => true,
                'message' => 'Client pets retrieved successfully',
                'data' => $pets
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve client pets',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
