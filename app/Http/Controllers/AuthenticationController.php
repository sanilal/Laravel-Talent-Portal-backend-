<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Models\TalentProfile;
use App\Models\RecruiterProfile;
use App\Models\LoginAttempt;
use App\Models\EmailVerificationAttempt;
use App\Services\AuthenticationService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthenticationController extends Controller
{
    public function __construct(
        private AuthenticationService $authService
    ) {}

    /**
     * Display registration form.
     */
    public function showRegister(): View
    {
        return view('auth.register');
    }

    /**
     * Handle registration request.
     */
    public function register(RegisterRequest $request): RedirectResponse
    {
        $executed = RateLimiter::attempt(
            'register:'.$request->ip(),
            $perMinute = 3,
            function () use ($request) {
                return $this->createUser($request);
            }
        );

        if (!$executed) {
            return back()->withErrors([
                'email' => 'Too many registration attempts. Please try again in a minute.'
            ]);
        }

        return redirect()->route('verification.notice')
            ->with('message', 'Registration successful! Please check your email to verify your account.');
    }

    /**
     * Create a new user.
     */
    protected function createUser(RegisterRequest $request)
    {
        return DB::transaction(function () use ($request) {
            // Create user
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => $request->password,
                'user_type' => $request->user_type,
                'phone' => $request->phone,
                'location' => $request->location,
                'status' => User::STATUS_INACTIVE, // Inactive until email verified
            ]);

            // Create profile based on user type
            $this->createUserProfile($user, $request);

            // Send email verification
            $user->sendEmailVerificationNotification();

            event(new Registered($user));

            return $user;
        });
    }

    /**
     * Create user profile based on type.
     */
    protected function createUserProfile(User $user, RegisterRequest $request): void
    {
        switch ($user->user_type) {
            case User::TYPE_TALENT:
                TalentProfile::create([
                    'user_id' => $user->id,
                    'category_id' => $request->category_id,
                    'headline' => $request->headline,
                    'availability_status' => TalentProfile::STATUS_AVAILABLE,
                    'profile_completion' => 20, // Initial completion
                ]);
                break;

            case User::TYPE_RECRUITER:
                RecruiterProfile::create([
                    'user_id' => $user->id,
                    'company_name' => $request->company_name,
                    'company_type' => $request->company_type,
                    'industry' => $request->industry,
                    'subscription_tier' => RecruiterProfile::TIER_FREE,
                ]);
                break;
        }
    }

    /**
     * Display login form.
     */
    public function showLogin(): View
    {
        return view('auth.login');
    }

    /**
     * Handle login request.
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        // Check if user is locked out
        if ($this->hasTooManyLoginAttempts($request)) {
            return back()->withErrors([
                'email' => 'Too many login attempts. Please try again later.'
            ]);
        }

        // Record login attempt
        $this->recordLoginAttempt($request, false);

        // Attempt authentication
        if (Auth::attempt(
            $request->only('email', 'password'),
            $request->boolean('remember')
        )) {
            $user = Auth::user();
            
            // Check if user is active
            if (!$user->isActive()) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Your account is inactive. Please contact support.'
                ]);
            }

            // Check if email is verified
            if (!$user->hasVerifiedEmail()) {
                Auth::logout();
                return redirect()->route('verification.notice')
                    ->withErrors(['email' => 'Please verify your email address.']);
            }

            // Record successful login
            $this->recordLoginAttempt($request, true);
            
            // Update user login info
            $user->update([
                'last_login_at' => now(),
                'login_attempts' => 0,
                'locked_until' => null,
            ]);

            $request->session()->regenerate();

            return $this->redirectToDashboard($user);
        }

        // Increment failed attempts
        $this->incrementLoginAttempts($request);

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Record login attempt.
     */
    protected function recordLoginAttempt(Request $request, bool $successful): void
    {
        LoginAttempt::create([
            'email' => $request->email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'successful' => $successful,
            'attempted_at' => now(),
            'failure_reason' => $successful ? null : 'Invalid credentials',
            'location_data' => $this->getLocationData($request->ip()),
        ]);
    }

    /**
     * Get location data from IP.
     */
    protected function getLocationData(string $ip): array
    {
        // In production, use a service like MaxMind GeoIP
        // For now, return basic info
        return [
            'ip' => $ip,
            'country' => 'Unknown',
            'city' => 'Unknown',
        ];
    }

    /**
     * Check if user has too many login attempts.
     */
    protected function hasTooManyLoginAttempts(Request $request): bool
    {
        $key = 'login.' . $request->ip() . '.' . $request->email;
        return RateLimiter::tooManyAttempts($key, 5); // 5 attempts per minute
    }

    /**
     * Increment login attempts.
     */
    protected function incrementLoginAttempts(Request $request): void
    {
        $key = 'login.' . $request->ip() . '.' . $request->email;
        RateLimiter::hit($key, 60); // Lock for 1 minute

        // Check if user should be locked
        $user = User::where('email', $request->email)->first();
        if ($user) {
            $user->increment('login_attempts');
            
            if ($user->login_attempts >= 5) {
                $user->update(['locked_until' => now()->addMinutes(30)]);
            }
        }
    }

    /**
     * Redirect to appropriate dashboard.
     */
    protected function redirectToDashboard(User $user): RedirectResponse
    {
        return match($user->user_type) {
            User::TYPE_TALENT => redirect()->route('talent.dashboard'),
            User::TYPE_RECRUITER => redirect()->route('recruiter.dashboard'),
            User::TYPE_ADMIN => redirect()->route('admin.dashboard'),
            default => redirect()->route('dashboard')
        };
    }

    /**
     * Logout user.
     */
    public function logout(Request $request): RedirectResponse
    {
        // Update last activity
        if ($user = Auth::user()) {
            $user->update(['last_activity_at' => now()]);
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('message', 'You have been logged out successfully.');
    }

    /**
     * Show email verification notice.
     */
    public function showVerificationNotice(): View
    {
        return view('auth.verify-email');
    }

    /**
     * Handle email verification.
     */
    public function verifyEmail(Request $request): RedirectResponse
    {
        $request->validate([
            'id' => 'required|string',
            'hash' => 'required|string',
        ]);

        $user = User::findOrFail($request->id);

        if (!hash_equals((string) $request->hash, sha1($user->getEmailForVerification()))) {
            abort(403);
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('login')
                ->with('message', 'Email already verified.');
        }

        // Record verification attempt
        EmailVerificationAttempt::create([
            'email' => $user->email,
            'token' => $request->hash,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'successful' => true,
            'attempted_at' => now(),
        ]);

        if ($user->markEmailAsVerified()) {
            // Activate user account
            $user->update(['status' => User::STATUS_ACTIVE]);

            event(new \Illuminate\Auth\Events\Verified($user));

            return redirect()->route('login')
                ->with('message', 'Email verified successfully! You can now login.');
        }

        return redirect()->route('verification.notice')
            ->withErrors(['email' => 'Failed to verify email. Please try again.']);
    }

    /**
     * Resend verification email.
     */
    public function resendVerificationEmail(Request $request): RedirectResponse
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $executed = RateLimiter::attempt(
            'verification:'.$request->ip(),
            $perMinute = 2,
            function () use ($request) {
                $user = User::where('email', $request->email)->first();
                
                if ($user && !$user->hasVerifiedEmail()) {
                    $user->sendEmailVerificationNotification();
                    return true;
                }
                return false;
            }
        );

        if (!$executed) {
            return back()->withErrors([
                'email' => 'Too many verification requests. Please try again later.'
            ]);
        }

        return back()->with('message', 'Verification email sent!');
    }
}