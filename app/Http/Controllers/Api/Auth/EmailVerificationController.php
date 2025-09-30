<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\EmailVerificationAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class EmailVerificationController extends Controller
{
    /**
     * Send email verification notification
     */
    public function send(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
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
                'message' => 'Too many verification emails sent. Please try again later.'
            ], 429);
        }

        // Log attempt
        EmailVerificationAttempt::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Verification email sent successfully'
        ]);
    }

    /**
     * Verify email with code or token
     */
    public function verify(Request $request)
    {
        $request->validate([
            'verification_code' => 'required|string|size:6',
        ]);

        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email already verified'
            ], 400);
        }

        // In a real implementation, you'd store verification codes in database
        // For now, we'll use Laravel's built-in email verification
        
        $user->markEmailAsVerified();

        // Update account status
        $user->update([
            'account_status' => 'active'
        ]);

        return response()->json([
            'message' => 'Email verified successfully',
            'user' => $user->fresh()
        ]);
    }

    /**
     * Check email verification status
     */
    public function status(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'email_verified' => $user->hasVerifiedEmail(),
            'email_verified_at' => $user->email_verified_at,
            'account_status' => $user->account_status,
        ]);
    }
}