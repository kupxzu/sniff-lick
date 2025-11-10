<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\ConsultationController;
use App\Http\Controllers\Api\DewormingController;
use App\Http\Controllers\Api\LabtestController;
use App\Http\Controllers\Api\PetController;
use App\Http\Controllers\Api\PrescriptionController;
use App\Http\Controllers\Api\TreatmentController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\VaccinationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public storage route (no authentication required)
Route::get('/storage/{path}', function ($path) {
    $filePath = storage_path('app/public/' . $path);
    
    if (!file_exists($filePath)) {
        abort(404);
    }
    
    $mimeType = mime_content_type($filePath);
    
    return response()->file($filePath, [
        'Content-Type' => $mimeType,
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Methods' => 'GET, OPTIONS',
        'Access-Control-Allow-Headers' => '*',
    ]);
})->where('path', '.*');

// Health check route
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'API is running',
        'timestamp' => now()
    ]);
});

// Authentication Routes (Public)
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register'])->name('auth.register');
    Route::post('login', [AuthController::class, 'login'])->name('auth.login');
    
    // Protected auth routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('user', [AuthController::class, 'user'])->name('auth.user');
    });
});

// User Profile Routes (Protected)
Route::middleware('auth:sanctum')->prefix('user')->group(function () {
    Route::get('profile', [UserController::class, 'profile'])->name('user.profile');
    Route::put('profile', [UserController::class, 'updateProfile'])->name('user.update-profile');
    Route::put('username', [UserController::class, 'updateUsername'])->name('user.update-username');
    Route::put('email', [UserController::class, 'updateEmail'])->name('user.update-email');
    Route::put('password', [UserController::class, 'updatePassword'])->name('user.update-password');
    Route::get('pets', [UserController::class, 'pets'])->name('user.pets');
});

// Client-Centric Hierarchical Routes (Client Access to Own Data)
Route::middleware('auth:sanctum')->prefix('client')->group(function () {
    // My Pets
    Route::get('pets', [UserController::class, 'pets'])->name('client.pets');
    Route::get('pets/{pet}', [UserController::class, 'pet'])->name('client.pet.show');
    
    // My Pet -> Consultations
    Route::get('pets/{pet}/consultations', [UserController::class, 'petConsultations'])->name('client.pet.consultations');
    
    // My Pet -> Vaccinations
    Route::get('pets/{pet}/vaccinations', [UserController::class, 'petVaccinations'])->name('client.pet.vaccinations');
});

// Admin-Centric Hierarchical Routes (Admin Only)
Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    // Dashboard
    Route::get('dashboard', [ClientController::class, 'dashboard'])->name('admin.dashboard');
    
    // Client Management
    Route::get('clients', [ClientController::class, 'index'])->name('admin.clients');
    Route::post('clients', [ClientController::class, 'store'])->name('admin.clients.create');
    Route::get('clients/{client}', [ClientController::class, 'show'])->name('admin.client.show');
    Route::put('clients/{client}', [ClientController::class, 'update'])->name('admin.client.update');
    Route::delete('clients/{client}', [ClientController::class, 'destroy'])->name('admin.client.delete');
    
    // Client -> Pet Management
    Route::get('clients/{client}/pets', [PetController::class, 'clientPets'])->name('admin.client.pets');
    Route::post('clients/{client}/pets', [PetController::class, 'store'])->name('admin.client.pets.create');
    Route::get('clients/{client}/pets/{pet}', [PetController::class, 'show'])->name('admin.client.pet.show');
    Route::put('clients/{client}/pets/{pet}', [PetController::class, 'update'])->name('admin.client.pet.update');
    Route::delete('clients/{client}/pets/{pet}', [PetController::class, 'destroy'])->name('admin.client.pet.delete');
    
    // Client -> Pet -> Consultation Management
    Route::get('clients/{client}/pets/{pet}/consultations', [ConsultationController::class, 'petConsultations'])->name('admin.client.pet.consultations');
    Route::post('clients/{client}/pets/{pet}/consultations', [ConsultationController::class, 'store'])->name('admin.client.pet.consultations.create');
    Route::get('clients/{client}/pets/{pet}/consultations/{consultation}', [ConsultationController::class, 'show'])->name('admin.client.pet.consultation.show');
    Route::put('clients/{client}/pets/{pet}/consultations/{consultation}', [ConsultationController::class, 'update'])->name('admin.client.pet.consultation.update');
    Route::delete('clients/{client}/pets/{pet}/consultations/{consultation}', [ConsultationController::class, 'destroy'])->name('admin.client.pet.consultation.delete');
    
    // Client -> Pet -> Consultation -> Latest Records
    Route::get('clients/{client}/pets/{pet}/consultations/{consultation}/latest', [ConsultationController::class, 'latestRecords'])->name('admin.client.pet.consultation.latest');
    
    // Client -> Pet -> Consultation -> Lab Tests
    Route::get('clients/{client}/pets/{pet}/consultations/{consultation}/labtests', [LabtestController::class, 'consultationLabtests'])->name('admin.client.pet.consultation.labtests');
    Route::post('clients/{client}/pets/{pet}/consultations/{consultation}/labtests', [LabtestController::class, 'store'])->name('admin.client.pet.consultation.labtests.create');
    Route::get('clients/{client}/pets/{pet}/consultations/{consultation}/labtests/{labtest}', [LabtestController::class, 'show'])->name('admin.client.pet.consultation.labtest.show');
    Route::put('clients/{client}/pets/{pet}/consultations/{consultation}/labtests/{labtest}', [LabtestController::class, 'update'])->name('admin.client.pet.consultation.labtest.update');
    Route::delete('clients/{client}/pets/{pet}/consultations/{consultation}/labtests/{labtest}', [LabtestController::class, 'destroy'])->name('admin.client.pet.consultation.labtest.delete');
    
    // Client -> Pet -> Consultation -> Treatments
    Route::get('clients/{client}/pets/{pet}/consultations/{consultation}/treatments', [TreatmentController::class, 'consultationTreatments'])->name('admin.client.pet.consultation.treatments');
    Route::post('clients/{client}/pets/{pet}/consultations/{consultation}/treatments', [TreatmentController::class, 'store'])->name('admin.client.pet.consultation.treatments.create');
    Route::get('clients/{client}/pets/{pet}/consultations/{consultation}/treatments/{treatment}', [TreatmentController::class, 'show'])->name('admin.client.pet.consultation.treatment.show');
    Route::put('clients/{client}/pets/{pet}/consultations/{consultation}/treatments/{treatment}', [TreatmentController::class, 'update'])->name('admin.client.pet.consultation.treatment.update');
    Route::delete('clients/{client}/pets/{pet}/consultations/{consultation}/treatments/{treatment}', [TreatmentController::class, 'destroy'])->name('admin.client.pet.consultation.treatment.delete');
    
    // Client -> Pet -> Consultation -> Prescriptions
    Route::get('clients/{client}/pets/{pet}/consultations/{consultation}/prescriptions', [PrescriptionController::class, 'consultationPrescriptions'])->name('admin.client.pet.consultation.prescriptions');
    Route::post('clients/{client}/pets/{pet}/consultations/{consultation}/prescriptions', [PrescriptionController::class, 'store'])->name('admin.client.pet.consultation.prescriptions.create');
    Route::get('clients/{client}/pets/{pet}/consultations/{consultation}/prescriptions/{prescription}', [PrescriptionController::class, 'show'])->name('admin.client.pet.consultation.prescription.show');
    Route::put('clients/{client}/pets/{pet}/consultations/{consultation}/prescriptions/{prescription}', [PrescriptionController::class, 'update'])->name('admin.client.pet.consultation.prescription.update');
    Route::delete('clients/{client}/pets/{pet}/consultations/{consultation}/prescriptions/{prescription}', [PrescriptionController::class, 'destroy'])->name('admin.client.pet.consultation.prescription.delete');
    
    // Client -> Pet -> Vaccinations
    Route::get('clients/{client}/pets/{pet}/vaccinations', [VaccinationController::class, 'index'])->name('admin.client.pet.vaccinations');
    Route::post('clients/{client}/pets/{pet}/vaccinations', [VaccinationController::class, 'store'])->name('admin.client.pet.vaccinations.create');
    Route::get('clients/{client}/pets/{pet}/vaccinations/{vaccination}', [VaccinationController::class, 'show'])->name('admin.client.pet.vaccination.show');
    Route::put('clients/{client}/pets/{pet}/vaccinations/{vaccination}', [VaccinationController::class, 'update'])->name('admin.client.pet.vaccination.update');
    Route::delete('clients/{client}/pets/{pet}/vaccinations/{vaccination}', [VaccinationController::class, 'destroy'])->name('admin.client.pet.vaccination.delete');
    
    // Client -> Pet -> Dewormings
    Route::get('clients/{client}/pets/{pet}/dewormings', [DewormingController::class, 'index'])->name('admin.client.pet.dewormings');
    Route::post('clients/{client}/pets/{pet}/dewormings', [DewormingController::class, 'store'])->name('admin.client.pet.dewormings.create');
    Route::get('clients/{client}/pets/{pet}/dewormings/{deworming}', [DewormingController::class, 'show'])->name('admin.client.pet.deworming.show');
    Route::put('clients/{client}/pets/{pet}/dewormings/{deworming}', [DewormingController::class, 'update'])->name('admin.client.pet.deworming.update');
    Route::delete('clients/{client}/pets/{pet}/dewormings/{deworming}', [DewormingController::class, 'destroy'])->name('admin.client.pet.deworming.delete');
    
    // Appointments (Global)
    Route::get('appointments', [AppointmentController::class, 'index'])->name('admin.appointments'); // Supports ?filter=all|today|week|month
    Route::post('appointments', [AppointmentController::class, 'store'])->name('admin.appointments.create');
    Route::put('appointments/{appointment}', [AppointmentController::class, 'update'])->name('admin.appointments.update');
    Route::delete('appointments/{appointment}', [AppointmentController::class, 'destroy'])->name('admin.appointments.delete');
});

// Legacy route for compatibility
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return response()->json([
        'success' => true,
        'user' => $request->user()
    ]);
})->name('user.legacy');
