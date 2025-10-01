<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\EmailVerificationController;
use App\Http\Controllers\Api\Auth\PasswordResetController;
use App\Http\Controllers\Api\Auth\TwoFactorController;
use App\Http\Controllers\Api\TalentController;
use App\Http\Controllers\Api\RecruiterController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ApplicationController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\MediaController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PublicController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| All routes return JSON responses for Next.js frontend consumption
| Base URL: /api/v1/*
*/


Route::get('/v1/test', function () {
    return ['status' => 'ok'];
});
Route::prefix('v1')->group(function () {
    
    // ============================================
    // PUBLIC ROUTES (No Authentication Required)
    // ============================================
    
    // Health check and API info
    Route::get('/health', function () {
        return response()->json([
            'status' => 'ok',
            'version' => '1.0.0',
            'timestamp' => now()->toIso8601String()
        ]);
    });
    
    // Public listing endpoints
    Route::prefix('public')->group(function () {
        Route::get('/categories', [PublicController::class, 'categories']);
        Route::get('/skills', [PublicController::class, 'skills']);
        Route::get('/projects', [PublicController::class, 'projects']);
        Route::get('/projects/{id}', [PublicController::class, 'showProject']);
        Route::get('/talents', [PublicController::class, 'talents']);
        Route::get('/talents/{id}', [PublicController::class, 'showTalent']);
    });
    
    // ============================================
    // AUTHENTICATION ROUTES
    // ============================================
    
    Route::prefix('auth')->group(function () {
        
        // Public auth endpoints
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink']);
        Route::post('/reset-password', [PasswordResetController::class, 'reset']);
        
        // Protected auth endpoints
        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/me', [AuthController::class, 'me']);
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/logout-all-devices', [AuthController::class, 'logoutAllDevices']);
            Route::post('/refresh-token', [AuthController::class, 'refreshToken']);
            Route::put('/update-profile', [AuthController::class, 'updateProfile']);
            Route::post('/change-password', [AuthController::class, 'changePassword']);
            
            // Active sessions management
            Route::get('/sessions', [AuthController::class, 'sessions']);
            Route::delete('/sessions/{tokenId}', [AuthController::class, 'revokeSession']);
        });
    });
    
    // ============================================
    // EMAIL VERIFICATION (API-based)
    // ============================================
    
    Route::prefix('email')->middleware('auth:sanctum')->group(function () {
        Route::post('/verification-notification', [EmailVerificationController::class, 'send'])
            ->middleware('throttle:6,1');
        Route::post('/verify', [EmailVerificationController::class, 'verify']);
        Route::get('/verification-status', [EmailVerificationController::class, 'status']);
    });
    
    // ============================================
    // TWO-FACTOR AUTHENTICATION
    // ============================================
    
    Route::prefix('two-factor')->middleware('auth:sanctum')->group(function () {
        // Setup and manage 2FA
        Route::post('/enable', [TwoFactorController::class, 'enable']);
        Route::post('/confirm', [TwoFactorController::class, 'confirm']);
        Route::delete('/disable', [TwoFactorController::class, 'disable']);
        Route::get('/qr-code', [TwoFactorController::class, 'qrCode']);
        Route::get('/recovery-codes', [TwoFactorController::class, 'recoveryCodes']);
        Route::post('/recovery-codes', [TwoFactorController::class, 'regenerateRecoveryCodes']);
        
        // Verify 2FA code during login (called after login, before full access)
        Route::post('/verify', [TwoFactorController::class, 'verify'])
            ->withoutMiddleware('auth:sanctum');
    });
    
    // ============================================
    // PROTECTED ROUTES (Require Authentication)
    // ============================================
    
    Route::middleware(['auth:sanctum', 'verified'])->group(function () {
        
        // -------------------------------------
        // TALENT ROUTES
        // -------------------------------------
        Route::prefix('talent')->middleware('role:talent')->group(function () {
            // Profile management
            Route::get('/profile', [TalentController::class, 'profile']);
            Route::put('/profile', [TalentController::class, 'updateProfile']);
            Route::post('/profile/avatar', [TalentController::class, 'updateAvatar']);
            
            // Portfolio management
            Route::get('/portfolios', [TalentController::class, 'portfolios']);
            Route::post('/portfolios', [TalentController::class, 'createPortfolio']);
            Route::put('/portfolios/{id}', [TalentController::class, 'updatePortfolio']);
            Route::delete('/portfolios/{id}', [TalentController::class, 'deletePortfolio']);
            
            // Skills management
            Route::get('/skills', [TalentController::class, 'skills']);
            Route::post('/skills', [TalentController::class, 'attachSkill']);
            Route::put('/skills/{id}', [TalentController::class, 'updateSkill']);
            Route::delete('/skills/{id}', [TalentController::class, 'detachSkill']);
            
            // Experience management
            Route::get('/experiences', [TalentController::class, 'experiences']);
            Route::post('/experiences', [TalentController::class, 'createExperience']);
            Route::put('/experiences/{id}', [TalentController::class, 'updateExperience']);
            Route::delete('/experiences/{id}', [TalentController::class, 'deleteExperience']);
            
            // Education management
            Route::get('/education', [TalentController::class, 'education']);
            Route::post('/education', [TalentController::class, 'createEducation']);
            Route::put('/education/{id}', [TalentController::class, 'updateEducation']);
            Route::delete('/education/{id}', [TalentController::class, 'deleteEducation']);
            
            // Applications
            Route::get('/applications', [TalentController::class, 'applications']);
            Route::get('/applications/{id}', [TalentController::class, 'showApplication']);
            
            // Dashboard stats
            Route::get('/dashboard', [TalentController::class, 'dashboard']);
        });
        
        // -------------------------------------
        // RECRUITER ROUTES
        // -------------------------------------
        Route::prefix('recruiter')->middleware('role:recruiter')->group(function () {
            // Profile management
            Route::get('/profile', [RecruiterController::class, 'profile']);
            Route::put('/profile', [RecruiterController::class, 'updateProfile']);
            Route::post('/profile/logo', [RecruiterController::class, 'updateLogo']);
            
            // Dashboard stats
            Route::get('/dashboard', [RecruiterController::class, 'dashboard']);
            
            // Talent search and discovery
            Route::get('/talents/search', [RecruiterController::class, 'searchTalents']);
            Route::get('/talents/{id}', [RecruiterController::class, 'viewTalent']);
            Route::post('/talents/{id}/save', [RecruiterController::class, 'saveTalent']);
            Route::delete('/talents/{id}/unsave', [RecruiterController::class, 'unsaveTalent']);
        });
        
        // -------------------------------------
        // PROJECT ROUTES
        // -------------------------------------
        Route::prefix('projects')->group(function () {
            // List and search
            Route::get('/', [ProjectController::class, 'index']);
            Route::get('/search', [ProjectController::class, 'search']);
            Route::get('/{id}', [ProjectController::class, 'show']);
            
            // Create and manage (recruiter only)
            Route::middleware('role:recruiter')->group(function () {
                Route::post('/', [ProjectController::class, 'store']);
                Route::put('/{id}', [ProjectController::class, 'update']);
                Route::delete('/{id}', [ProjectController::class, 'destroy']);
                Route::post('/{id}/publish', [ProjectController::class, 'publish']);
                Route::post('/{id}/close', [ProjectController::class, 'close']);
                Route::get('/{id}/applications', [ProjectController::class, 'applications']);
            });
        });
        
        // -------------------------------------
        // APPLICATION ROUTES
        // -------------------------------------
        Route::prefix('applications')->group(function () {
            // Apply to project (talent only)
            Route::post('/', [ApplicationController::class, 'store'])
                ->middleware('role:talent');
            
            // View application details
            Route::get('/{id}', [ApplicationController::class, 'show']);
            
            // Withdraw application (talent only)
            Route::delete('/{id}', [ApplicationController::class, 'withdraw'])
                ->middleware('role:talent');
            
            // Update application status (recruiter only)
            Route::put('/{id}/status', [ApplicationController::class, 'updateStatus'])
                ->middleware('role:recruiter');
            
            // Add notes (recruiter only)
            Route::post('/{id}/notes', [ApplicationController::class, 'addNotes'])
                ->middleware('role:recruiter');
        });
        
        // -------------------------------------
        // MESSAGING ROUTES
        // -------------------------------------
        Route::prefix('messages')->group(function () {
            Route::get('/', [MessageController::class, 'index']);
            Route::post('/', [MessageController::class, 'send']);
            Route::get('/conversations', [MessageController::class, 'conversations']);
            Route::get('/conversations/{userId}', [MessageController::class, 'conversation']);
            Route::put('/{id}/read', [MessageController::class, 'markAsRead']);
            Route::delete('/{id}', [MessageController::class, 'delete']);
        });
        
        // -------------------------------------
        // MEDIA UPLOAD ROUTES
        // -------------------------------------
        Route::prefix('media')->group(function () {
            Route::post('/upload', [MediaController::class, 'upload']);
            Route::delete('/{id}', [MediaController::class, 'delete']);
            Route::get('/{id}', [MediaController::class, 'show']);
        });
        
        // -------------------------------------
        // REVIEW ROUTES
        // -------------------------------------
        Route::prefix('reviews')->group(function () {
            Route::post('/', [ReviewController::class, 'store']);
            Route::get('/user/{userId}', [ReviewController::class, 'userReviews']);
            Route::put('/{id}', [ReviewController::class, 'update']);
            Route::delete('/{id}', [ReviewController::class, 'destroy']);
        });
        
        // -------------------------------------
        // NOTIFICATION ROUTES
        // -------------------------------------
        Route::prefix('notifications')->group(function () {
            Route::get('/', [NotificationController::class, 'index']);
            Route::get('/unread', [NotificationController::class, 'unread']);
            Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
            Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
            Route::delete('/{id}', [NotificationController::class, 'delete']);
        });
        
    });
    
    // ============================================
    // ADMIN ROUTES (Require Admin Role)
    // ============================================
    
    Route::prefix('admin')->middleware(['auth:sanctum', 'verified', 'role:admin'])->group(function () {
        // Dashboard
        Route::get('/dashboard', function () {
            return response()->json([
                'message' => 'Admin dashboard data',
                // Add admin stats here
            ]);
        });
        
        // User management
        Route::get('/users', function () {
            return response()->json(['message' => 'User list']);
        });
        Route::put('/users/{id}/status', function ($id) {
            return response()->json(['message' => "Update user $id status"]);
        });
        
        // Project moderation
        Route::get('/projects/pending', function () {
            return response()->json(['message' => 'Pending projects']);
        });
        Route::post('/projects/{id}/approve', function ($id) {
            return response()->json(['message' => "Approve project $id"]);
        });
        
        // Reports and analytics
        Route::get('/reports', function () {
            return response()->json(['message' => 'Reports']);
        });
    });
    
});

// Fallback route for undefined API endpoints
Route::fallback(function () {
    return response()->json([
        'message' => 'API endpoint not found',
        'status' => 404
    ], 404);
});