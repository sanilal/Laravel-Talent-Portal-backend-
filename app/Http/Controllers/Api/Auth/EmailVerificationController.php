<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\EmailVerificationAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class EmailVerificationController extends Controller
{
    /**
     * Send OTP to user's email
     */
    public function resend(Request $request)
    {
        // Can work with authenticated user OR email in request
        $email = $request->email ?? $request->user()?->email;

        if (!$email) {
            return response()->json([
                'message' => 'Email is required'
            ], 422);
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        if ($user->is_email_verified) {
            return response()->json([
                'message' => 'Email already verified'
            ], 400);
        }

        // Check rate limiting - max 6 attempts per hour
        $recentAttempts = EmailVerificationAttempt::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subHour())
            ->count();

        if ($recentAttempts >= 6) {
            return response()->json([
                'message' => 'Too many verification attempts. Please try again in 1 hour.',
                'retry_after' => 3600
            ], 429);
        }

        // Generate 6-digit OTP
        $otp = sprintf('%06d', random_int(0, 999999));

        // Store OTP in database
        $attempt = EmailVerificationAttempt::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'token' => $otp, // Using token field for OTP
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'expires_at' => now()->addMinutes(15), // OTP valid for 15 minutes
            'is_used' => false,
        ]);

        // Send OTP via email
        try {
            Mail::send('emails.verification-otp', ['otp' => $otp, 'user' => $user], function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Verify Your Email - Talents You Need');
            });

            return response()->json([
                'message' => 'Verification code sent successfully to ' . $this->maskEmail($user->email),
                'expires_in' => 900, // 15 minutes in seconds
            ]);
        } catch (\Exception $e) {
            // Log error but don't expose it to user
            \Log::error('Failed to send OTP email: ' . $e->getMessage());

            return response()->json([
                'message' => 'Failed to send verification code. Please try again later.'
            ], 500);
        }
    }

    /**
     * Verify OTP
     */
    public function verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'otp' => ['required', 'string', 'size:6', 'regex:/^[0-9]{6}$/'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        if ($user->is_email_verified) {
            return response()->json([
                'message' => 'Email already verified'
            ], 400);
        }

        // Find valid OTP attempt
        $attempt = EmailVerificationAttempt::where('user_id', $user->id)
            ->where('token', $request->otp)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->first();

        if (!$attempt) {
            return response()->json([
                'message' => 'Invalid or expired verification code'
            ], 422);
        }

        // Mark OTP as used
        $attempt->update([
            'is_used' => true,
            'verified_at' => now(),
        ]);

        // Verify user email
        $user->update([
            'email_verified_at' => now(),
            'is_email_verified' => true,
            'account_status' => 'active',
        ]);

        // Create authentication token
        $token = $user->createToken('auth-token')->plainTextToken;

        // Load relationships
        $user->load(['talentProfile', 'recruiterProfile', 'country']);

        return response()->json([
            'message' => 'Email verified successfully',
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }

    /**
     * Check email verification status
     */
    public function status(Request $request)
    {
        $email = $request->email ?? $request->user()?->email;

        if (!$email) {
            return response()->json([
                'message' => 'Email is required'
            ], 422);
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        return response()->json([
            'email_verified' => $user->is_email_verified,
            'email_verified_at' => $user->email_verified_at,
            'account_status' => $user->account_status,
        ]);
    }

    /**
     * Mask email for security
     * example@domain.com -> e*****@domain.com
     */
    private function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return $email;
        }

        $name = $parts[0];
        $domain = $parts[1];

        $maskedName = substr($name, 0, 1) . str_repeat('*', max(strlen($name) - 1, 5));

        return $maskedName . '@' . $domain;
    }
}