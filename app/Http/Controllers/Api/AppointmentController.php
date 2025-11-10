<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Pet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    /**
     * Get all appointments with optional date filter
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $filter = $request->query('filter', 'all'); // all, today, week, month

        $query = Appointment::with(['client', 'pet']);

        switch ($filter) {
            case 'today':
                $query->whereDate('appointment_date', Carbon::today());
                break;
            case 'week':
                $query->whereBetween('appointment_date', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ]);
                break;
            case 'month':
                $query->whereMonth('appointment_date', Carbon::now()->month)
                      ->whereYear('appointment_date', Carbon::now()->year);
                break;
            case 'all':
            default:
                // No filter - show all appointments
                break;
        }

        $appointments = $query->orderBy('appointment_date', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $appointments
        ]);
    }

    /**
     * Store a new appointment
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:users,id',
            'pet_id' => 'required|exists:pets,id',
            'appointment_date' => 'required|date',
            'types' => 'required|in:consultation,vaccine,deworming',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verify pet belongs to client
        $pet = Pet::where('id', $request->pet_id)
                   ->where('client_id', $request->client_id)
                   ->first();

        if (!$pet) {
            return response()->json([
                'success' => false,
                'message' => 'Pet does not belong to this client'
            ], 404);
        }

        $appointment = Appointment::create($request->all());
        $appointment->load(['client', 'pet']);

        return response()->json([
            'success' => true,
            'message' => 'Appointment created successfully',
            'data' => $appointment
        ], 201);
    }

    /**
     * Update an appointment
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $appointment = Appointment::find($id);

        if (!$appointment) {
            return response()->json([
                'success' => false,
                'message' => 'Appointment not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'client_id' => 'sometimes|exists:users,id',
            'pet_id' => 'sometimes|exists:pets,id',
            'appointment_date' => 'sometimes|date',
            'types' => 'sometimes|in:consultation,vaccine,deworming',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // If updating pet_id or client_id, verify pet belongs to client
        if ($request->has('pet_id') || $request->has('client_id')) {
            $petId = $request->pet_id ?? $appointment->pet_id;
            $clientId = $request->client_id ?? $appointment->client_id;

            $pet = Pet::where('id', $petId)
                       ->where('client_id', $clientId)
                       ->first();

            if (!$pet) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pet does not belong to this client'
                ], 404);
            }
        }

        $appointment->update($request->all());
        $appointment->load(['client', 'pet']);

        return response()->json([
            'success' => true,
            'message' => 'Appointment updated successfully',
            'data' => $appointment
        ]);
    }

    /**
     * Delete an appointment
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $appointment = Appointment::find($id);

        if (!$appointment) {
            return response()->json([
                'success' => false,
                'message' => 'Appointment not found'
            ], 404);
        }

        $appointment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Appointment deleted successfully'
        ]);
    }
}
