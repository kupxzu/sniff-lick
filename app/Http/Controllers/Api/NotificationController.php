<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class NotificationController extends Controller
{
    /**
     * Send appointment reminder emails
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendAppointmentReminders(Request $request)
    {
        $request->validate([
            'client_ids' => 'required|array',
            'client_ids.*' => 'exists:users,id'
        ]);

        $clientIds = $request->client_ids;
        $today = Carbon::today()->toDateString();
        
        // Get today's appointments for selected clients
        $appointments = Appointment::with(['client', 'pet'])
            ->whereIn('client_id', $clientIds)
            ->whereDate('appointment_date', $today)
            ->get();

        if ($appointments->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No appointments found for today for selected clients'
            ], 404);
        }

        $sentCount = 0;
        $failedCount = 0;
        $errors = [];

        foreach ($appointments as $appointment) {
            try {
                if (!$appointment->client || !$appointment->client->email) {
                    $failedCount++;
                    $errors[] = "Client {$appointment->client_id} has no email address";
                    continue;
                }

                // Send email
                Mail::send('emails.appointment-reminder', [
                    'clientName' => $appointment->client->name,
                    'petName' => $appointment->pet->name ?? 'Your pet',
                    'appointmentDate' => Carbon::parse($appointment->appointment_date)->format('F j, Y'),
                    'appointmentTime' => Carbon::parse($appointment->appointment_date)->format('g:i A'),
                    'appointmentType' => ucfirst($appointment->types),
                    'clinicName' => 'Sniff & Lick Veterinary Clinic',
                    'clinicPhone' => '(02) 8123-4567',
                    'clinicAddress' => '123 Main Street, Manila, Philippines'
                ], function ($message) use ($appointment) {
                    $message->to($appointment->client->email, $appointment->client->name)
                            ->subject('Appointment Reminder - Sniff & Lick Veterinary Clinic');
                });

                $sentCount++;
                
            } catch (\Exception $e) {
                $failedCount++;
                $errors[] = "Failed to send to {$appointment->client->email}: {$e->getMessage()}";
                Log::error('Failed to send appointment reminder', [
                    'appointment_id' => $appointment->id,
                    'client_email' => $appointment->client->email,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Sent {$sentCount} reminder(s), {$failedCount} failed",
            'data' => [
                'sent' => $sentCount,
                'failed' => $failedCount,
                'errors' => $errors
            ]
        ]);
    }

    /**
     * Get clients with appointments today
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getClientsWithAppointmentsToday()
    {
        $today = Carbon::today()->toDateString();
        
        $clients = User::where('role', 'client')
            ->whereHas('appointments', function ($query) use ($today) {
                $query->whereDate('appointment_date', $today);
            })
            ->with(['appointments' => function ($query) use ($today) {
                $query->whereDate('appointment_date', $today)
                      ->with('pet');
            }])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $clients
        ]);
    }
}
