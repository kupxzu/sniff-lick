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
                ->select('id', 'name', 'username', 'email', 'created_at')
                ->withCount('pets')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Clients retrieved successfully',
                'clients' => $clients
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
                'client' => $client
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
                ->select('id', 'name', 'username', 'email', 'created_at')
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
                'dashboard' => [
                    'users' => [
                        'total_clients' => $totalClients,
                        'total_admins' => $totalAdmins,
                        'recent_clients' => $recentClients,
                    ],
                    'pets' => [
                        'total_pets' => $totalPets,
                        'canines' => $canines,
                        'felines' => $felines,
                        'recent_pets' => $recentPets,
                    ]
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
}
