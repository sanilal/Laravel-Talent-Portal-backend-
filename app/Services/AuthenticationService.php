<?php

namespace App\Services;

use App\Models\User;
use App\Models\LoginAttempt;
use App\Models\EmailVerificationAttempt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;
use PragmaRX\Google2FA\Google2FA;

class AuthenticationService
{
    public function __construct(
        private Google2FA $google2fa
    ) {}

    /**
     * Create API token for user.
     */
    public function createApiToken(User $user, string $deviceName = 'Unknown Device'): array
    {
        // Revoke old tokens for the same device (optional)
        $user->tokens()->where('name', $deviceName)->delete();

        // Create abilities based on user type
        $abilities = $this->getUserAbilities($user);

        // Create token
        $token = $user->createToken($deviceName, $abilities, now()->addDays(30));

        return [
            'access_token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'expires_in' => 30 * 24 * 60 * 60, // 30 days in seconds
            'abilities' => $abilities,
        ];
    }

    /**
     * Get user abilities based on type.
     */
    protected function getUserAbilities(User $user): array
    {
        $baseAbilities = ['user:read'];

        return match($user->user_type) {
            User::TYPE_TALENT => array_merge($baseAbilities, [
                'talent:read',
                'talent:write',
                'applications:create',
                'applications:read',
                'messages:read',
                'messages:write',
                'media:upload',
                'portfolio:manage',
                'profile:update',
            ]),
            User::TYPE_RECRUITER => array_merge($baseAbilities, [
                'recruiter:read',
                'recruiter:write',
                'projects:create',
                'projects:read',
                'projects:update',
                'projects:delete',
                'applications:read',
                'applications:manage',
                'messages:read',
                'messages:write',
                'media:upload',
                'company:manage',
            ]),
            User::TYPE_ADMIN => [
                '*', // All abilities
            ],
            default => $baseAbilities
        };
    }

    /**
     * Revoke user tokens.
     */
    public function revokeTokens(User $user, ?string $exceptToken = null): void
    {
        $tokens = $user->tokens();
        
        if ($exceptToken) {
            $tokens = $tokens->where('token', '!=', hash('sha256', explode('|', $exceptToken)[1]));
        }
        
        $tokens->delete();
    }

    /**
     * Generate two-factor authentication secret.
     */
    public function generateTwoFactorSecret(User $user): array
    {
        $secret = $this->google2fa->generateSecretKey();
        
        $user->update([
            'two_factor_secret' => encrypt($secret),
            'two_factor_recovery_codes' => encrypt(json_encode($this->generateRecoveryCodes())),
        ]);

        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        return [
            'secret' => $secret,
            'qr_code' => $qrCodeUrl,
        ];
    }

    /**
     * Verify two-factor authentication code.
     */
    public function verifyTwoFactorCode(User $user, string $code): bool
    {
        if (!$user->two_factor_secret) {
            return false;
        }

        $secret = decrypt($user->two_factor_secret);
        
        return $this->google2fa->verifyKey($secret, $code);
    }

    /**
     * Confirm two-factor authentication.
     */
    public function confirmTwoFactor(User $user): void
    {
        $user->update([
            'two_factor_confirmed_at' => now(),
        ]);
    }

    /**
     * Disable two-factor authentication.
     */
    public function disableTwoFactor(User $user): void
    {
        $user->update([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);
    }

    /**
     * Verify recovery code.
     */
    public function verifyRecoveryCode(User $user, string $code): bool
    {
        if (!$user->two_factor_recovery_codes) {
            return false;
        }

        $recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);
        
        if (!in_array($code, $recoveryCodes)) {
            return false;
        }

        // Remove used recovery code
        $recoveryCodes = array_diff($recoveryCodes, [$code]);
        
        $user->update([
            'two_factor_recovery_codes' => encrypt(json_encode(array_values($recoveryCodes))),
        ]);

        return true;
    }

    /**
     * Generate recovery codes.
     */
    protected function generateRecoveryCodes(): array
    {
        return collect(range(1, 8))->map(function () {
            return Str::random(10);
        })->toArray();
    }

    /**
     * Check if user should be locked out.
     */
    public function shouldLockUser(User $user): bool
    {
        $recentFailedAttempts = LoginAttempt::where('email', $user->email)
            ->where('successful', false)
            ->where('attempted_at', '>=', now()->subMinutes(15))
            ->count();

        return $recentFailedAttempts >= 5;
    }

    /**
     * Lock user account.
     */
    public function lockUser(User $user, int $minutes = 30): void
    {
        $user->update([
            'locked_until' => now()->addMinutes($minutes),
            'login_attempts' => $user->login_attempts + 1,
        ]);

        // Revoke all tokens
        $this->revokeTokens($user);
    }

    /**
     * Unlock user account.
     */
    public function unlockUser(User $user): void
    {
        $user->update([
            'locked_until' => null,
            'login_attempts' => 0,
        ]);
    }

    /**
     * Get user login statistics.
     */
    public function getUserLoginStats(User $user): array
    {
        $attempts = LoginAttempt::where('email', $user->email);

        return [
            'total_attempts' => $attempts->count(),
            'successful_attempts' => $attempts->where('successful', true)->count(),
            'failed_attempts' => $attempts->where('successful', false)->count(),
            'last_successful_login' => $attempts->where('successful', true)
                ->latest('attempted_at')
                ->first()?->attempted_at,
            'recent_failed_attempts' => $attempts->where('successful', false)
                ->where('attempted_at', '>=', now()->subHours(24))
                ->count(),
        ];
    }

    /**
     * Get active sessions for user.
     */
    public function getActiveSessions(User $user): array
    {
        return $user->tokens()
            ->where('expires_at', '>', now())
            ->orWhereNull('expires_at')
            ->get()
            ->map(function ($token) {
                return [
                    'id' => $token->id,
                    'name' => $token->name,
                    'abilities' => $token->abilities,
                    'last_used_at' => $token->last_used_at,
                    'created_at' => $token->created_at,
                    'expires_at' => $token->expires_at,
                ];
            })
            ->toArray();
    }

    /**
     * Revoke specific session.
     */
    public function revokeSession(User $user, string $tokenId): bool
    {
        return $user->tokens()->where('id', $tokenId)->delete() > 0;
    }

    /**
     * Change user password.
     */
    public function changePassword(User $user, string $newPassword, bool $revokeTokens = true): void
    {
        $user->update([
            'password' => Hash::make($newPassword),
            'login_attempts' => 0,
            'locked_until' => null,
        ]);

        if ($revokeTokens) {
            $this->revokeTokens($user);
        }
    }

    /**
     * Verify current password.
     */
    public function verifyPassword(User $user, string $password): bool
    {
        return Hash::check($password, $user->password);
    }

    /**
     * Log security event.
     */
    public function logSecurityEvent(User $user, string $event, array $data = []): void
    {
        \Log::info('Security Event', [
            'user_id' => $user->id,
            'email' => $user->email,
            'event' => $event,
            'data' => $data,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now(),
        ]);
    }

    /**
     * Send account activity alert.
     */
    public function sendAccountActivityAlert(User $user, string $activity, array $details = []): void
    {
        // Implementation would depend on your notification system
        // For now, just log it
        $this->logSecurityEvent($user, 'account_activity_alert', [
            'activity' => $activity,
            'details' => $details,
        ]);
    }

    /**
     * Check for suspicious login activity.
     */
    public function checkSuspiciousActivity(User $user, string $ipAddress, string $userAgent): bool
    {
        // Check for login from new location
        $recentLogins = LoginAttempt::where('email', $user->email)
            ->where('successful', true)
            ->where('attempted_at', '>=', now()->subDays(30))
            ->get();

        $knownIpAddresses = $recentLogins->pluck('ip_address')->unique();
        $knownUserAgents = $recentLogins->pluck('user_agent')->unique();

        $isNewIp = !$knownIpAddresses->contains($ipAddress);
        $isNewDevice = !$knownUserAgents->contains($userAgent);

        // If both IP and device are new, it's suspicious
        if ($isNewIp && $isNewDevice && $recentLogins->count() > 0) {
            $this->sendAccountActivityAlert($user, 'suspicious_login', [
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'known_ips' => $knownIpAddresses->toArray(),
            ]);
            
            return true;
        }

        return false;
    }

    /**
     * Get security recommendations for user.
     */
    public function getSecurityRecommendations(User $user): array
    {
        $recommendations = [];

        // Check if 2FA is enabled
        if (!$user->two_factor_secret) {
            $recommendations[] = [
                'type' => 'security',
                'priority' => 'high',
                'title' => 'Enable Two-Factor Authentication',
                'description' => 'Add an extra layer of security to your account.',
                'action' => 'setup_2fa',
            ];
        }

        // Check password age
        $passwordAge = $user->updated_at->diffInDays(now());
        if ($passwordAge > 90) {
            $recommendations[] = [
                'type' => 'security',
                'priority' => 'medium',
                'title' => 'Update Your Password',
                'description' => 'Consider changing your password regularly for better security.',
                'action' => 'change_password',
            ];
        }

        // Check for unused sessions
        $activeSessions = $this->getActiveSessions($user);
        if (count($activeSessions) > 3) {
            $recommendations[] = [
                'type' => 'privacy',
                'priority' => 'low',
                'title' => 'Review Active Sessions',
                'description' => 'You have multiple active sessions. Consider revoking unused ones.',
                'action' => 'manage_sessions',
            ];
        }

        return $recommendations;
    }
}