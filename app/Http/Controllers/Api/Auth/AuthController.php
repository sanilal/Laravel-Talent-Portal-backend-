<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\TalentProfile;
use App\Models\RecruiterProfile;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Normalize phone number using phone country
     * 
     * @param string|null $phone - Local phone number (e.g., "52 723 2144")
     * @param string|null $phoneCountryId - UUID of phone's country
     * @return string|null - E.164 format (e.g., "+971527232144")
     */
    private function normalizePhoneNumber(?string $phone, ?string $phoneCountryId = null): ?string
    {
        if (!$phone) {
            return null;
        }

        // Remove all spaces, dashes, parentheses, dots
        $phone = preg_replace('/[\s\-\(\)\.]/', '', $phone);

        // If already has +, just return cleaned version
        if (str_starts_with($phone, '+')) {
            return $phone;
        }

        // Get dialing code from phone country
        if ($phoneCountryId) {
            $country = Country::find($phoneCountryId);
            
            if ($country && $country->dialing_code) {
                $dialingCode = ltrim($country->dialing_code, '+');
                
                // If phone starts with 0, replace it with country code
                if (str_starts_with($phone, '0')) {
                    return '+' . $dialingCode . substr($phone, 1);
                }
                
                // If phone already starts with country code (without +)
                if (str_starts_with($phone, $dialingCode)) {
                    return '+' . $phone;
                }
                
                // Otherwise, just prepend country code
                return '+' . $dialingCode . $phone;
            }
        }

        // Fallback: if no country provided and phone doesn't have +, add it
        return '+' . $phone;
    }

    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        // ✅ Normalize phone using phone_country_id
        if ($request->has('phone') && $request->phone) {
            $request->merge([
                'phone' => $this->normalizePhoneNumber(
                    $request->phone,
                    $request->phone_country_id  // ← Use separate phone country
                )
            ]);
        }

        // Validation rules
        $validator = Validator::make($request->all(), [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::min(8)
                ->mixedCase()
                ->numbers()
                ->symbols()
            ],
            'user_type' => ['required', 'in:talent,recruiter'],
            
            // ✅ Phone is optional, but if provided must be valid E.164
            'phone' => [
                'nullable',
                'string',
                'regex:/^\+[1-9]\d{1,14}$/',
                'unique:users'
            ],
            
            // ✅ Phone country is optional, but recommended if phone is provided
            'phone_country_id' => ['nullable', 'exists:countries,id'],
            
            'country_id' => ['nullable', 'exists:countries,id'],
            'gender' => ['nullable', 'in:male,female,other,prefer_not_to_say'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'category_id' => ['nullable', 'exists:categories,id'],
        ], [
            'phone.regex' => 'The phone number format is invalid. Please select a country and enter your phone number.',
            'phone.unique' => 'This phone number is already registered.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Create user
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'user_type' => $request->user_type,
                'phone' => $request->phone, // Already normalized with correct country code
                'country_id' => $request->country_id,
                'gender' => $request->gender,
                'date_of_birth' => $request->date_of_birth,
                'account_status' => 'pending_verification',
                'is_email_verified' => false,
                'is_verified' => false,
            ]);

            // Create profile based on user type
            if ($user->user_type === 'talent') {
                $talentProfileData = [
                    'user_id' => $user->id,
                    'profile_completion_percentage' => 10,
                ];

                if ($request->category_id) {
                    $talentProfileData['primary_category_id'] = $request->category_id;
                    $talentProfileData['profile_completion_percentage'] = 15;
                }

                TalentProfile::create($talentProfileData);
                
            } elseif ($user->user_type === 'recruiter') {
                $companyName = 'Company';
                $companySlug = Str::slug($companyName) . '-' . Str::random(6);
                
                RecruiterProfile::create([
                    'user_id' => $user->id,
                    'company_name' => $companyName,
                    'company_slug' => $companySlug,
                    'profile_completion_percentage' => 0,
                ]);
            }

            // Load relationships
            $user->load(['talentProfile', 'recruiterProfile', 'country']);

            return response()->json([
                'message' => 'Registration successful. Please verify your email to continue.',
                'user' => $user,
                'requires_verification' => true,
            ], 201);

        } catch (\Exception $e) {
            Log::error('Registration failed: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        if (!$user->is_email_verified) {
            return response()->json([
                'message' => 'Email not verified. Please verify your email to continue.',
                'requires_verification' => true,
                'email' => $user->email,
                'user_id' => $user->id,
            ], 403);
        }

        if ($user->account_status === 'suspended') {
            return response()->json([
                'message' => 'Your account has been suspended. Please contact support.'
            ], 403);
        }

        if ($user->account_status === 'banned') {
            return response()->json([
                'message' => 'Your account has been banned. Please contact support.'
            ], 403);
        }

        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;
        $user->load(['talentProfile', 'recruiterProfile', 'country']);

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return response()->json(['message' => 'Logged out successfully']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Logout failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function user(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            $user->load(['talentProfile', 'recruiterProfile', 'country']);
            return response()->json(['user' => $user]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        // ✅ Normalize phone with phone_country_id if provided
        if ($request->has('phone') && $request->phone) {
            $request->merge([
                'phone' => $this->normalizePhoneNumber(
                    $request->phone,
                    $request->phone_country_id
                )
            ]);
        }

        $validator = Validator::make($request->all(), [
            'first_name' => ['sometimes', 'string', 'max:255'],
            'last_name' => ['sometimes', 'string', 'max:255'],
            'phone' => [
                'sometimes',
                'nullable',
                'string',
                'regex:/^\+[1-9]\d{1,14}$/',
                'unique:users,phone,' . $user->id
            ],
            'phone_country_id' => ['sometimes', 'nullable', 'exists:countries,id'],
            'bio' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'location' => ['sometimes', 'nullable', 'string', 'max:255'],
            'website' => ['sometimes', 'nullable', 'url', 'max:255'],
            'linkedin_url' => ['sometimes', 'nullable', 'url', 'max:255'],
            'twitter_url' => ['sometimes', 'nullable', 'url', 'max:255'],
            'instagram_url' => ['sometimes', 'nullable', 'url', 'max:255'],
            'gender' => ['sometimes', 'nullable', 'in:male,female,other,prefer_not_to_say'],
            'date_of_birth' => ['sometimes', 'nullable', 'date', 'before:today'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user->update($request->only([
                'first_name',
                'last_name',
                'phone',
                'bio',
                'location',
                'website',
                'linkedin_url',
                'twitter_url',
                'instagram_url',
                'gender',
                'date_of_birth',
            ]));

            return response()->json([
                'message' => 'Profile updated successfully',
                'user' => $user->fresh(['talentProfile', 'recruiterProfile'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Profile update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => ['required'],
            'password' => ['required', 'confirmed', Password::min(8)
                ->mixedCase()
                ->numbers()
                ->symbols()
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect'
            ], 422);
        }

        try {
            $user->update(['password' => Hash::make($request->password)]);
            return response()->json(['message' => 'Password changed successfully']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Password change failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}