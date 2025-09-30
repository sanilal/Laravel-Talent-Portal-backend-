<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\Crypt;

class TwoFactorController extends Controller
{
    protected $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Enable 2FA for user
     */
    public function enable(Request $request)
    {
        $user = $request->user();

        if ($user->two_factor_enabled) {
            return response()->json([
                'message' => '2FA is already enabled'
            ], 400);
        }

        // Generate secret key
        $secret = $this->google2fa->generateSecretKey();

        // Store encrypted secret temporarily
        $user->update([
            'two_factor_secret' => Crypt::encryptString($secret),
        ]);

        // Generate QR code URL
        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        return response()->json([
            'message' => '2FA setup initiated. Please confirm with a code to complete.',
            'secret' => $secret,
            'qr_code_url' => $qrCodeUrl,
        ]);
    }

    /**
     * Confirm and activate 2FA
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = $request->user();

        if ($user->two_factor_enabled) {
            return response()->json([
                'message' => '2FA is already enabled'
            ], 400);
        }

        $secret = Crypt::decryptString($user->two_factor_secret);

        // Verify the code
        $valid = $this->google2fa->verifyKey($secret, $request->code);

        if (!$valid) {
            return response()->json([
                'message' => 'Invalid verification code'
            ], 400);
        }

        // Generate recovery codes
        $recoveryCodes = [];
        for ($i = 0; $i < 8; $i++) {
            $recoveryCodes[] = strtoupper(substr(md5(random_bytes(10)), 0, 10));
        }

        // Enable 2FA
        $user->update([
            'two_factor_enabled' => true,
            'two_factor_recovery_codes' => Crypt::encryptString(json_encode($recoveryCodes)),
        ]);

        return response()->json([
            'message' => '2FA enabled successfully',
            'recovery_codes' => $recoveryCodes,
        ]);
    }

    /**
     * Disable 2FA
     */
    public function disable(Request $request)
    {
        $request->validate([
            'password' => 'required',
        ]);

        $user = $request->user();

        if (!password_verify($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid password'
            ], 400);
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
     * Verify 2FA code during login
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'temp_token' => 'required|string',
        ]);

        // Get user from temporary token
        $user = auth('sanctum')->user();

        if (!$user || !$user->two_factor_enabled) {
            return response()->json([
                'message' => 'Invalid request'
            ], 400);
        }

        $secret = Crypt::decryptString($user->two_factor_secret);
        
        // Check if it's a recovery code
        if (strlen($request->code) === 10) {
            $recoveryCodes = json_decode(Crypt::decryptString($user->two_factor_recovery_codes), true);
            
            if (in_array(strtoupper($request->code), $recoveryCodes)) {
                // Remove used recovery code
                $recoveryCodes = array_diff($recoveryCodes, [strtoupper($request->code)]);
                $user->update([
                    'two_factor_recovery_codes' => Crypt::encryptString(json_encode(array_values($recoveryCodes)))
                ]);

                // Create full access token
                $token = $user->createToken('auth-token', [$user->role])->plainTextToken;

                return response()->json([
                    'message' => '2FA verified with recovery code',
                    'token' => $token,
                    'recovery_codes_remaining' => count($recoveryCodes),
                ]);
            }
        }

        // Verify regular code
        $valid = $this->google2fa->verifyKey($secret, $request->code);

        if (!$valid) {
            return response()->json([
                'message' => 'Invalid verification code'
            ], 400);
        }

        // Delete temporary token
        $user->tokens()->where('name', '2fa-temp')->delete();

        // Create full access token
        $token = $user->createToken('auth-token', [$user->role])->plainTextToken;

        // Update last login
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        return response()->json([
            'message' => '2FA verified successfully',
            'user' => $user->load($user->role . 'Profile'),
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Get QR code for 2FA
     */
    public function qrCode(Request $request)
    {
        $user = $request->user();

        if (!$user->two_factor_secret) {
            return response()->json([
                'message' => '2FA not set up'
            ], 400);
        }

        $secret = Crypt::decryptString($user->two_factor_secret);

        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        return response()->json([
            'qr_code_url' => $qrCodeUrl,
        ]);
    }

    /**
     * Get recovery codes
     */
    public function recoveryCodes(Request $request)
    {
        $user = $request->user();

        if (!$user->two_factor_enabled) {
            return response()->json([
                'message' => '2FA not enabled'
            ], 400);
        }

        $recoveryCodes = json_decode(Crypt::decryptString($user->two_factor_recovery_codes), true);

        return response()->json([
            'recovery_codes' => $recoveryCodes,
        ]);
    }

    /**
     * Regenerate recovery codes
     */
    public function regenerateRecoveryCodes(Request $request)
    {
        $request->validate([
            'password' => 'required',
        ]);

        $user = $request->user();

        if (!password_verify($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid password'
            ], 400);
        }

        // Generate new recovery codes
        $recoveryCodes = [];
        for ($i = 0; $i < 8; $i++) {
            $recoveryCodes[] = strtoupper(substr(md5(random_bytes(10)), 0, 10));
        }

        $user->update([
            'two_factor_recovery_codes' => Crypt::encryptString(json_encode($recoveryCodes)),
        ]);

        return response()->json([
            'message' => 'Recovery codes regenerated',
            'recovery_codes' => $recoveryCodes,
        ]);
    }
}