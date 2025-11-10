<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ClientController extends Controller
{
    /**
     * Get all clients (Admin only)
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Check if user is admin
            if (!$request->user()->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. Admin role required.'
                ], 403);
            }

            $clients = User::where('role', 'client')
                ->select('id', 'name', 'username', 'email', 'phone', 'address', 'created_at')
                ->withCount('pets')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Clients retrieved successfully',
                'data' => $clients
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve clients',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific client with their pets (Admin only)
     */
    public function show(Request $request, string $id): JsonResponse
    {
        try {
            // Check if user is admin
            if (!$request->user()->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. Admin role required.'
                ], 403);
            }

            $client = User::where('role', 'client')
                ->where('id', $id)
                ->with('pets')
                ->first();

            if (!$client) {
                return response()->json([
                    'success' => false,
                    'message' => 'Client not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Client retrieved successfully',
                'data' => $client
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve client',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all clients with all their pets (Admin only)
     */
    public function clientsWithPets(Request $request): JsonResponse
    {
        try {
            // Check if user is admin
            if (!$request->user()->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. Admin role required.'
                ], 403);
            }

            $clients = User::where('role', 'client')
                ->select('id', 'name', 'username', 'email', 'phone', 'address', 'created_at')
                ->with(['pets' => function ($query) {
                    $query->select('id', 'client_id', 'name', 'age', 'species', 'breed', 'colormark', 'created_at');
                }])
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Clients with pets retrieved successfully',
                'clients' => $clients
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve clients with pets',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get pets for a specific client (Admin only)
     */
    public function clientPets(Request $request, string $clientId): JsonResponse
    {
        try {
            // Check if user is admin
            if (!$request->user()->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. Admin role required.'
                ], 403);
            }

            $client = User::where('role', 'client')
                ->where('id', $clientId)
                ->first();

            if (!$client) {
                return response()->json([
                    'success' => false,
                    'message' => 'Client not found'
                ], 404);
            }

            $pets = $client->pets()->get();

            return response()->json([
                'success' => true,
                'message' => 'Client pets retrieved successfully',
                'client' => [
                    'id' => $client->id,
                    'name' => $client->name,
                    'username' => $client->username,
                    'email' => $client->email,
                    'phone' => $client->phone,
                    'address' => $client->address,
                ],
                'pets' => $pets
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve client pets',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get dashboard summary for admin
     */
    public function dashboard(Request $request): JsonResponse
    {
        try {
            // Check if user is admin
            if (!$request->user()->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. Admin role required.'
                ], 403);
            }

            $totalClients = User::where('role', 'client')->count();
            $totalAdmins = User::where('role', 'admin')->count();
            $totalPets = \App\Models\Pet::count();
            $canines = \App\Models\Pet::where('species', 'canine')->count();
            $felines = \App\Models\Pet::where('species', 'feline')->count();
            $totalConsultations = \App\Models\Consultation::count();
            $totalPrescriptions = \App\Models\Prescription::count();
            $totalVaccinations = \App\Models\Vaccination::count();
            $totalAppointments = \App\Models\Appointment::count();

            // Recent clients (last 30 days)
            $recentClients = User::where('role', 'client')
                ->where('created_at', '>=', now()->subDays(30))
                ->count();

            // Recent pets (last 30 days)
            $recentPets = \App\Models\Pet::where('created_at', '>=', now()->subDays(30))
                ->count();

            return response()->json([
                'success' => true,
                'message' => 'Dashboard data retrieved successfully',
                'data' => [
                    'totalClients' => $totalClients,
                    'totalPets' => $totalPets,
                    'totalConsultations' => $totalConsultations,
                    'totalPrescriptions' => $totalPrescriptions,
                    'totalVaccinations' => $totalVaccinations,
                    'totalAppointments' => $totalAppointments,
                    'canines' => $canines,
                    'felines' => $felines,
                    'recentClients' => $recentClients,
                    'recentPets' => $recentPets,
                    'totalAdmins' => $totalAdmins,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve dashboard data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new client (Admin only)
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Check if user is admin
            if (!$request->user()->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. Admin role required.'
                ], 403);
            }

            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'nullable|string|email|max:255|unique:users',
                'username' => 'nullable|string|max:255|unique:users',
                'phone' => 'nullable|string|max:255',
                'address' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            $name = $request->input('name');
            $email = $request->input('email') ?: null;
            $username = $request->input('username') ?: null;
            $phone = $request->input('phone') ?: null;
            $address = $request->input('address') ?: null;

            // Generate a username if not provided
            if (empty($username) && !empty($email)) {
                $username = explode('@', $email)[0] . '_' . substr(uniqid(), -4);
            } elseif (empty($username)) {
                $username = 'client_' . substr(uniqid(), -6);
            }

            // Generate a random password and hash it using Hash facade
            $randomPassword = bin2hex(random_bytes(8));

            $client = User::create([
                'name' => $name,
                'email' => $email,
                'username' => $username,
                'password' => \Illuminate\Support\Facades\Hash::make($randomPassword),
                'role' => 'client',
                'phone' => $phone,
                'address' => $address,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Client created successfully',
                'data' => [
                    'id' => $client->id,
                    'name' => $client->name,
                    'email' => $client->email,
                    'username' => $client->username,
                    'role' => $client->role,
                    'created_at' => $client->created_at
                ]
            ], 201);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Client creation error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create client',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a client (Admin only)
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            // Check if user is admin
            if (!$request->user()->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. Admin role required.'
                ], 403);
            }

            $client = User::where('role', 'client')->where('id', $id)->first();

            if (!$client) {
                return response()->json([
                    'success' => false,
                    'message' => 'Client not found'
                ], 404);
            }

            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|max:255|unique:users,email,' . $id,
                'username' => 'sometimes|string|max:255|unique:users,username,' . $id,
                'phone' => 'sometimes|nullable|string|max:255',
                'address' => 'sometimes|nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            $updateData = [];
            if ($request->has('name')) {
                $updateData['name'] = $request->input('name');
            }
            if ($request->has('email')) {
                $updateData['email'] = $request->input('email') ?: null;
            }
            if ($request->has('username')) {
                $updateData['username'] = $request->input('username') ?: null;
            }
            if ($request->has('phone')) {
                $updateData['phone'] = $request->input('phone') ?: null;
            }
            if ($request->has('address')) {
                $updateData['address'] = $request->input('address') ?: null;
            }

            $client->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Client updated successfully',
                'data' => $client
            ], 200);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Client update error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update client',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a client (Admin only)
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            // Check if user is admin
            if (!$request->user()->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. Admin role required.'
                ], 403);
            }

            $client = User::where('role', 'client')->where('id', $id)->first();

            if (!$client) {
                return response()->json([
                    'success' => false,
                    'message' => 'Client not found'
                ], 404);
            }

            $clientName = $client->name;
            $client->delete();

            return response()->json([
                'success' => true,
                'message' => "Client '{$clientName}' deleted successfully"
            ], 200);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Client deletion error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete client',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
  