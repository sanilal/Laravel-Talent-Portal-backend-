<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user (Talent or Recruiter)
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::min(8)
                ->mixedCase()
                ->numbers()
                ->symbols()],
            'role' => 'required|in:talent,recruiter',
            'phone' => 'nullable|string|max:20',

            // Conditional validation based on role
            'company_name' => 'required_if:role,recruiter|string|max:255',
            'category_id' => 'required_if:role,talent|uuid|exists:categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            \DB::beginTransaction();

            // Create user
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'user_type' => $request->role, // Changed from 'role' to 'user_type'
                'phone' => $request->phone,
                'account_status' => 'pending_verification',
            ]);

            // Create role-specific profile
            if ($request->role === 'talent') {
                $user->talentProfile()->create([
                    'primary_category_id' => $request->category_id,
                    'is_available' => true,
                    'is_public' => false, // Private until profile is completed
                    'profile_completion_percentage' => 10, // Basic info completed
                ]);
            } elseif ($request->role === 'recruiter') {
                $user->recruiterProfile()->create([
                    'company_name' => $request->company_name,
                    'is_verified' => false,
                ]);
            }

            \DB::commit();

            // Generate email verification code
            $verificationCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            
            // Store verification code (you can use cache or database)
            \Cache::put(
                'email_verification_' . $user->id,
                $verificationCode,
                now()->addMinutes(15)
            );

            // TODO: Send verification email with $verificationCode
            // Mail::to($user->email)->send(new VerificationEmail($verificationCode));

            // Create auth token
            $token = $user->createToken('auth-token', [$request->role])->plainTextToken;

            // Load profile relationship
            $profileRelation = $request->role . 'Profile';
            $user->load($profileRelation);

            return response()->json([
                'message' => 'Registration successful. Please verify your email.',
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer',
                'verification_required' => true,
            ], 201);

        } catch (\Exception $e) {
            \DB::rollBack();
            
            return response()->json([
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Login user and return token
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'nullable|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Check account status
        if ($user->account_status === 'suspended') {
            return response()->json([
                'message' => 'Your account has been suspended. Please contact support.'
            ], 403);
        }

        if ($user->account_status === 'deleted') {
            return response()->json([
                'message' => 'This account no longer exists.'
            ], 403);
        }

        // Check if 2FA is enabled
        if ($user->two_factor_enabled) {
            // Create temporary token for 2FA verification
            $tempToken = $user->createToken('2fa-temp', ['2fa:pending'])->plainTextToken;
            
            return response()->json([
                'message' => '2FA verification required',
                'requires_2fa' => true,
                'temp_token' => $tempToken,
            ]);
        }

        // Update last login
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        // Create token with role-based abilities
        $deviceName = $request->device_name ?? $request->userAgent();
        $token = $user->createToken($deviceName, [$user->role])->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => $user->load($user->role . 'Profile'),
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Verify two-factor authentication code
     */
    public function verifyTwoFactor(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = $request->user();

        // Verify the 2FA code using Google2FA
        $google2fa = app('pragmarx.google2fa');
        $valid = $google2fa->verifyKey($user->two_factor_secret, $request->code);

        if (!$valid) {
            return response()->json([
                'message' => 'Invalid verification code'
            ], 401);
        }

        // Delete temporary token and create full access token
        $request->user()->currentAccessToken()->delete();

        // Update last login
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        // Create new token with full abilities
        $token = $user->createToken('auth-token', [$user->role])->plainTextToken;

        return response()->json([
            'message' => '2FA verification successful',
            'user' => $user->load($user->role . 'Profile'),
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Get authenticated user details
     */
    public function me(Request $request)
    {
        $user = $request->user()->load([
            $request->user()->role . 'Profile',
            'notifications' => function ($query) {
                $query->whereNull('read_at')->latest()->take(5);
            }
        ]);

        return response()->json([
            'user' => $user,
            'unread_notifications' => $user->unreadNotifications->count(),
        ]);
    }

    /**
     * Logout user (revoke current token)
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Logout from all devices (revoke all tokens)
     */
    public function logoutAllDevices(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logged out from all devices'
        ]);
    }

    /**
     * Refresh token (create new token, revoke old)
     */
    public function refreshToken(Request $request)
    {
        $user = $request->user();
        $oldToken = $request->user()->currentAccessToken();
        
        // Create new token
        $newToken = $user->createToken(
            $oldToken->name,
            $oldToken->abilities
        )->plainTextToken;

        // Revoke old token
        $oldToken->delete();

        return response()->json([
            'token' => $newToken,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Get all active sessions
     */
    public function sessions(Request $request)
    {
        $tokens = $request->user()->tokens()->get()->map(function ($token) use ($request) {
            return [
                'id' => $token->id,
                'name' => $token->name,
                'abilities' => $token->abilities,
                'last_used_at' => $token->last_used_at,
                'created_at' => $token->created_at,
                'is_current' => $token->id === $request->user()->currentAccessToken()->id,
            ];
        });

        return response()->json([
            'sessions' => $tokens
        ]);
    }

    /**
     * Revoke a specific session/token
     */
    public function revokeSession(Request $request, $tokenId)
    {
        $token = $request->user()->tokens()->find($tokenId);

        if (!$token) {
            return response()->json([
                'message' => 'Session not found'
            ], 404);
        }

        // Prevent revoking current session
        if ($token->id === $request->user()->currentAccessToken()->id) {
            return response()->json([
                'message' => 'Cannot revoke current session. Use logout instead.'
            ], 400);
        }

        $token->delete();

        return response()->json([
            'message' => 'Session revoked successfully'
        ]);
    }

    /**
     * Setup two-factor authentication
     */
    public function setupTwoFactor(Request $request)
    {
        $user = $request->user();

        // Generate secret key
        $google2fa = app('pragmarx.google2fa');
        $secret = $google2fa->generateSecretKey();

        // Store secret temporarily (not enabled yet)
        $user->update([
            'two_factor_secret' => encrypt($secret),
        ]);

        // Generate QR code
        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        return response()->json([
            'message' => '2FA setup initiated',
            'secret' => $secret,
            'qr_code_url' => $qrCodeUrl,
        ]);
    }

    /**
     * Confirm and enable two-factor authentication
     */
    public function confirmTwoFactor(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = $request->user();

        if (!$user->two_factor_secret) {
            return response()->json([
                'message' => '2FA setup not initiated. Call setup endpoint first.'
            ], 400);
        }

        // Verify the code
        $google2fa = app('pragmarx.google2fa');
        $secret = decrypt($user->two_factor_secret);
        $valid = $google2fa->verifyKey($secret, $request->code);

        if (!$valid) {
            return response()->json([
                'message' => 'Invalid verification code'
            ], 401);
        }

        // Enable 2FA
        $user->update([
            'two_factor_enabled' => true,
        ]);

        // Generate recovery codes
        $recoveryCodes = collect(range(1, 8))->map(function () {
            return strtoupper(substr(md5(random_bytes(10)), 0, 8));
        });

        $user->update([
            'two_factor_recovery_codes' => encrypt($recoveryCodes->toJson()),
        ]);

        return response()->json([
            'message' => '2FA enabled successfully',
            'recovery_codes' => $recoveryCodes,
        ]);
    }

    /**
     * Disable two-factor authentication
     */
    public function disableTwoFactor(Request $request)
    {
        $request->validate([
            'password' => 'required',
        ]);

        $user = $request->user();

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Incorrect password'
            ], 401);
        }

        $user->update([
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
        ]);

        return response()->json([
            'message' => '2FA disabled successfully'
        ]);
    }

    /**
     * Get two-factor recovery codes
     */
    public function getRecoveryCodes(Request $request)
    {
        $user = $request->user();

        if (!$user->two_factor_enabled) {
            return response()->json([
                'message' => '2FA is not enabled'
            ], 400);
        }

        $recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes));

        return response()->json([
            'recovery_codes' => $recoveryCodes
        ]);
    }

    /**
     * Regenerate two-factor recovery codes
     */
    public function regenerateRecoveryCodes(Request $request)
    {
        $user = $request->user();

        if (!$user->two_factor_enabled) {
            return response()->json([
                'message' => '2FA is not enabled'
            ], 400);
        }

        // Generate new recovery codes
        $recoveryCodes = collect(range(1, 8))->map(function () {
            return strtoupper(substr(md5(random_bytes(10)), 0, 8));
        });

        $user->update([
            'two_factor_recovery_codes' => encrypt($recoveryCodes->toJson()),
        ]);

        return response()->json([
            'message' => 'Recovery codes regenerated successfully',
            'recovery_codes' => $recoveryCodes,
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|nullable|string|max:20',
            'bio' => 'sometimes|nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user->update($request->only(['name', 'phone', 'bio']));

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user->fresh()->load($user->role . 'Profile')
        ]);
    }

    /**
     * Change password
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Password::min(8)
                ->mixedCase()
                ->numbers()
                ->symbols()],
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
            ], 400);
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        // Optionally revoke all other tokens for security
        if ($request->logout_other_devices) {
            $user->tokens()->where('id', '!=', $user->currentAccessToken()->id)->delete();
        }

        return response()->json([
            'message' => 'Password changed successfully'
        ]);
    }
}