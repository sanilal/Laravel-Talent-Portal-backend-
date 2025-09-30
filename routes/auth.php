<?php

use App\Http\Controllers\Auth\AuthenticationController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\Auth\SocialAuthController;
use Illuminate\Support\Facades\Route;

// Authentication Routes
Route::middleware('guest')->group(function () {
    // Registration
    Route::get('/register', [AuthenticationController::class, 'showRegister'])
        ->name('register');
    Route::post('/register', [AuthenticationController::class, 'register']);

    // Login
    Route::get('/login', [AuthenticationController::class, 'showLogin'])
        ->name('login');
    Route::post('/login', [AuthenticationController::class, 'login']);

    // Password Reset
    Route::get('/forgot-password', [PasswordResetController::class, 'showForgotPassword'])
        ->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetEmail'])
        ->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetPassword'])
        ->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])
        ->name('password.store');

    // Social Authentication
    Route::get('/auth/{provider}', [SocialAuthController::class, 'redirect'])
        ->name('social.redirect')
        ->where('provider', 'google|linkedin');
    Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])
        ->name('social.callback')
        ->where('provider', 'google|linkedin');
});

// Email Verification Routes
Route::middleware('auth')->group(function () {
    Route::get('/email/verify', [AuthenticationController::class, 'showVerificationNotice'])
        ->name('verification.notice');
    
    Route::get('/email/verify/{id}/{hash}', [AuthenticationController::class, 'verifyEmail'])
        ->middleware(['signed'])
        ->name('verification.verify');
    
    Route::post('/email/verification-notification', [AuthenticationController::class, 'resendVerificationEmail'])
        ->middleware(['throttle:6,1'])
        ->name('verification.send');
});

// Two-Factor Authentication Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/two-factor-challenge', [TwoFactorController::class, 'showChallenge'])
        ->name('two-factor.challenge');
    Route::post('/two-factor-challenge', [TwoFactorController::class, 'verifyChallenge']);

    Route::get('/user/two-factor-authentication', [TwoFactorController::class, 'show'])
        ->name('two-factor.show');
    Route::post('/user/two-factor-authentication', [TwoFactorController::class, 'store'])
        ->name('two-factor.store');
    Route::delete('/user/two-factor-authentication', [TwoFactorController::class, 'destroy'])
        ->name('two-factor.destroy');

    Route::get('/user/two-factor-qr-code', [TwoFactorController::class, 'qrCode'])
        ->name('two-factor.qr-code');
    Route::get('/user/two-factor-recovery-codes', [TwoFactorController::class, 'recoveryCodes'])
        ->name('two-factor.recovery-codes');
    Route::post('/user/two-factor-recovery-codes', [TwoFactorController::class, 'generateRecoveryCodes'])
        ->name('two-factor.recovery-codes.store');
});

// Logout Route
Route::post('/logout', [AuthenticationController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Role-based Dashboard Routes
Route::middleware(['auth', 'verified', 'account.status'])->group(function () {
    // General dashboard (fallback)
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Talent dashboard
    Route::middleware('role:talent')->prefix('talent')->name('talent.')->group(function () {
        Route::get('/dashboard', function () {
            return view('talent.dashboard');
        })->name('dashboard');
        
        Route::get('/profile', function () {
            return view('talent.profile');
        })->name('profile');
        
        Route::get('/applications', function () {
            return view('talent.applications');
        })->name('applications');
        
        Route::get('/projects', function () {
            return view('talent.projects');
        })->name('projects');
    });

    // Recruiter dashboard
    Route::middleware('role:recruiter')->prefix('recruiter')->name('recruiter.')->group(function () {
        Route::get('/dashboard', function () {
            return view('recruiter.dashboard');
        })->name('dashboard');
        
        Route::get('/profile', function () {
            return view('recruiter.profile');
        })->name('profile');
        
        Route::get('/projects', function () {
            return view('recruiter.projects');
        })->name('projects');
        
        Route::get('/applications', function () {
            return view('recruiter.applications');
        })->name('applications');
    });

    // Admin dashboard
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('dashboard');
        
        Route::get('/users', function () {
            return view('admin.users');
        })->name('users');
        
        Route::get('/projects', function () {
            return view('admin.projects');
        })->name('projects');
        
        Route::get('/reports', function () {
            return view('admin.reports');
        })->name('reports');
    });
});