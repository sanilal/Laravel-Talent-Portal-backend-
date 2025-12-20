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
     * Normalize phone number - SIMPLIFIED VERSION
     * 
     * Strategy: User MUST provide phone in international format with country code
     * We just clean the formatting (spaces, dashes, etc.)
     * 
     * Why this approach?
     * - User's residence country ≠ Phone number country
     * - Someone in UAE might have Indian/UK/US phone number
     * - Clearest UX: User enters full international number
     * 
     * @param string|null $phone - Phone with country code (e.g., "+971 52 723 2144")
     * @return string|null - Cleaned E.164 format (e.g., "+971527232144")
     */
    private function normalizePhoneNumber(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        // Remove all spaces, dashes, parentheses, dots
        $phone = preg_replace('/[\s\-\(\)\.]/', '', $phone);

        // If doesn't start with +, user forgot country code - validation will catch this
        if (!str_starts_with($phone, '+')) {
            // Just add + prefix and let validation fail if format is wrong
            return '+' . $phone;
        }

        return $phone;
    }

    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        // ✅ Normalize phone (just clean formatting)
        if ($request->has('phone') && $request->phone) {
            $request->merge([
                'phone' => $this->normalizePhoneNumber($request->phone)
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
            
            // ✅ Phone MUST be in E.164 format: +[country code][number]
            'phone' => [
                'nullable',
                'string',
                'regex:/^\+[1-9]\d{1,14}$/', // Must start with + and have 1-15 digits
                'unique:users'
            ],
            
            'country_id' => ['nullable', 'exists:countries,id'],
            'gender' => ['nullable', 'in:male,female,other,prefer_not_to_say'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'category_id' => ['nullable', 'exists:categories,id'],
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
                'phone' => $request->phone, // Already normalized
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
                // ✅ Provide default values for required fields
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

    // ... (rest of the methods remain the same)
    
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

        // ✅ Normalize phone if provided
        if ($request->has('phone') && $request->phone) {
            $request->merge([
                'phone' => $this->normalizePhoneNumber($request->phone)
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