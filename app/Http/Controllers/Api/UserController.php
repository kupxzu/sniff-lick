<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Update user profile
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateProfile(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string|max:255',
                'username' => [
                    'sometimes',
                    'string',
                    'max:255',
                    Rule::unique('users')->ignore($user->id)
                ],
                'email' => [
                    'sometimes',
                    'string',
                    'email',
                    'max:255',
                    Rule::unique('users')->ignore($user->id)
                ],
                'current_password' => 'required_with:password|string',
                'password' => 'sometimes|string|min:8|confirmed',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            // If password is being updated, verify current password
            if ($request->has('password')) {
                if (!$request->has('current_password') || !Hash::check($request->current_password, $user->password)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Current password is incorrect'
                    ], 400);
                }
            }

            $updateData = [];
            
            if ($request->has('name')) {
                $updateData['name'] = $request->name;
            }
            
            if ($request->has('username')) {
                $updateData['username'] = $request->username;
            }
            
            if ($request->has('email')) {
                $updateData['email'] = $request->email;
            }
            
            if ($request->has('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'user' => $user->fresh()
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Profile update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update username only
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateUsername(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            $validator = Validator::make($request->all(), [
                'username' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('users')->ignore($user->id)
                ],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user->update(['username' => $request->username]);

            return response()->json([
                'success' => true,
                'message' => 'Username updated successfully',
                'user' => $user->fresh()
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Username update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update email only
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateEmail(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            $validator = Validator::make($request->all(), [
                'email' => [
                    'required',
                    'string',
                    'email',
                    'max:255',
                    Rule::unique('users')->ignore($user->id)
                ],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user->update(['email' => $request->email]);

            return response()->json([
                'success' => true,
                'message' => 'Email updated successfully',
                'user' => $user->fresh()
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Email update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update password only
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updatePassword(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            $validator = Validator::make($request->all(), [
                'current_password' => 'required|string',
                'password' => 'required|string|min:8|confirmed',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect'
                ], 400);
            }

            $user->update(['password' => Hash::make($request->password)]);

            return response()->json([
                'success' => true,
                'message' => 'Password updated successfully'
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Password update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user profile
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function profile(Request $request): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'user' => $request->user()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's pets with their latest consultation data
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function pets(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Load pets with their latest consultations, vaccinations, and related data
            $pets = \App\Models\Pet::where('client_id', $user->id)
                ->with([
                    'consultations' => function ($query) {
                        $query->latest('consultation_date')->take(5);
                    },
                    'consultations.treatments',
                    'consultations.prescriptions',
                    'consultations.labtests',
                    'vaccinations' => function ($query) {
                        $query->latest('date')->take(5);
                    },
                    'vaccinations.vacTreatments'
                ])
                ->get();

            return response()->json([
                'success' => true,
                'data' => $pets
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get pets',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific pet details
     *
     * @param Request $request
     * @param int $petId
     * @return JsonResponse
     */
    public function pet(Request $request, $petId): JsonResponse
    {
        try {
            $user = $request->user();
            
            $pet = \App\Models\Pet::where('client_id', $user->id)
                ->where('id', $petId)
                ->with([
                    'consultations' => function ($query) {
                        $query->latest('consultation_date');
                    },
                    'consultations.treatments',
                    'consultations.prescriptions',
                    'consultations.labtests',
                    'vaccinations' => function ($query) {
                        $query->latest('date');
                    },
                    'vaccinations.vacTreatments'
                ])
                ->first();

            if (!$pet) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pet not found or does not belong to you'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $pet
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get pet',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get consultations for a specific pet
     *
     * @param Request $request
     * @param int $petId
     * @return JsonResponse
     */
    public function petConsultations(Request $request, $petId): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Verify pet belongs to user
            $pet = \App\Models\Pet::where('client_id', $user->id)
                ->where('id', $petId)
                ->first();

            if (!$pet) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pet not found or does not belong to you'
                ], 404);
            }

            $consultations = \App\Models\Consultation::where('pet_id', $petId)
                ->with(['treatments', 'prescriptions', 'labtests'])
                ->latest('consultation_date')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $consultations
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get consultations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get vaccinations for a specific pet
     *
     * @param Request $request
     * @param int $petId
     * @return JsonResponse
     */
    public function petVaccinations(Request $request, $petId): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Verify pet belongs to user
            $pet = \App\Models\Pet::where('client_id', $user->id)
                ->where('id', $petId)
                ->first();

            if (!$pet) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pet not found or does not belong to you'
                ], 404);
            }

            $vaccinations = \App\Models\Vaccination::where('pet_id', $petId)
                ->with('vacTreatments')
                ->latest('date')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $vaccinations
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get vaccinations',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}