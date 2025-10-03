<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\EmailVerificationController;
use App\Http\Controllers\Api\Auth\PasswordResetController;
use App\Http\Controllers\Api\Auth\TwoFactorController;
use App\Http\Controllers\Api\Talent\TalentProfileController;
use App\Http\Controllers\Api\Talent\TalentSkillsController;
use App\Http\Controllers\Api\Talent\ExperiencesController;
use App\Http\Controllers\Api\Talent\EducationController;
use App\Http\Controllers\Api\Talent\PortfolioController;
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

Route::prefix('v1')->group(function () {
    
    // ============================================
    // HEALTH & TEST ENDPOINTS
    // ============================================
    
    Route::get('/test', function () {
        return response()->json(['status' => 'ok']);
    });
    
    Route::get('/health', function () {
        return response()->json([
            'status' => 'ok',
            'version' => '1.0.0',
            'timestamp' => now()->toIso8601String()
        ]);
    });
    
    // ============================================
    // PUBLIC ROUTES (No Authentication Required)
    // ============================================
    
    Route::prefix('public')->name('public.')->group(function () {
        Route::get('/categories', [PublicController::class, 'categories'])->name('categories');
        Route::get('/skills', [PublicController::class, 'skills'])->name('skills');
        Route::get('/projects', [PublicController::class, 'projects'])->name('projects');
        Route::get('/projects/{id}', [PublicController::class, 'showProject'])->name('projects.show');
        Route::get('/talents', [PublicController::class, 'talents'])->name('talents');
        Route::get('/talents/{id}', [PublicController::class, 'showTalent'])->name('talents.show');
    });
    
    // ============================================
    // AUTHENTICATION ROUTES
    // ============================================
    
    Route::prefix('auth')->name('auth.')->group(function () {
        
        // Public authentication endpoints
        Route::post('/register', [AuthController::class, 'register'])->name('register');
        Route::post('/login', [AuthController::class, 'login'])->name('login');
        Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])
            ->middleware('throttle:5,1')
            ->name('forgot-password');
        Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('reset-password');
        
        // Protected authentication endpoints
        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/me', [AuthController::class, 'me'])->name('me');
            Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
            Route::post('/logout-all-devices', [AuthController::class, 'logoutAllDevices'])->name('logout-all');
            Route::post('/refresh-token', [AuthController::class, 'refreshToken'])->name('refresh');
            Route::put('/update-profile', [AuthController::class, 'updateProfile'])->name('update-profile');
            Route::post('/change-password', [AuthController::class, 'changePassword'])->name('change-password');
            
            // Session management
            Route::get('/sessions', [AuthController::class, 'sessions'])->name('sessions');
            Route::delete('/sessions/{tokenId}', [AuthController::class, 'revokeSession'])->name('sessions.revoke');
        });
    });
    
    // ============================================
    // EMAIL VERIFICATION (API-based)
    // ============================================
    
    Route::prefix('email')->middleware('auth:sanctum')->name('email.')->group(function () {
        Route::post('/verification-notification', [EmailVerificationController::class, 'send'])
            ->middleware('throttle:6,1')
            ->name('verification.send');
        Route::post('/verify', [EmailVerificationController::class, 'verify'])->name('verify');
        Route::get('/verification-status', [EmailVerificationController::class, 'status'])->name('verification.status');
    });
    
    // ============================================
    // TWO-FACTOR AUTHENTICATION
    // ============================================
    
    Route::prefix('two-factor')->name('2fa.')->group(function () {
        
        // 2FA verification during login (no auth required)
        Route::post('/verify', [TwoFactorController::class, 'verify'])->name('verify');
        
        // Protected 2FA management
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/enable', [TwoFactorController::class, 'enable'])->name('enable');
            Route::post('/confirm', [TwoFactorController::class, 'confirm'])->name('confirm');
            Route::delete('/disable', [TwoFactorController::class, 'disable'])->name('disable');
            Route::get('/qr-code', [TwoFactorController::class, 'qrCode'])->name('qr-code');
            Route::get('/recovery-codes', [TwoFactorController::class, 'recoveryCodes'])->name('recovery-codes');
            Route::post('/recovery-codes', [TwoFactorController::class, 'regenerateRecoveryCodes'])->name('recovery-codes.regenerate');
        });
    });
    
    // ============================================
    // PROTECTED ROUTES (Require Authentication)
    // ============================================
    
    Route::middleware('auth:sanctum')->group(function () {
        
        // ============================================
        // TALENT ROUTES (Talent Role Required)
        // ============================================
        
        Route::prefix('talent')->middleware('ability:talent')->name('talent.')->group(function () {
            
            // Profile management
            Route::get('/profile', [TalentProfileController::class, 'show'])->name('profile');
            Route::put('/profile', [TalentProfileController::class, 'update'])->name('profile.update');
            Route::post('/profile/avatar', [TalentProfileController::class, 'uploadAvatar'])->name('profile.avatar');
            Route::get('/dashboard', [TalentProfileController::class, 'dashboard'])->name('dashboard');

            // Skills management
            Route::get('/skills', [TalentSkillsController::class, 'index'])->name('skills.index');
            Route::post('/skills', [TalentSkillsController::class, 'store'])->name('skills.store');
            Route::put('/skills/{id}', [TalentSkillsController::class, 'update'])->name('skills.update');
            Route::delete('/skills/{id}', [TalentSkillsController::class, 'destroy'])->name('skills.destroy');

            // Experience management
            Route::get('/experiences', [ExperiencesController::class, 'index'])->name('experiences.index');
            Route::post('/experiences', [ExperiencesController::class, 'store'])->name('experiences.store');
            Route::get('/experiences/{id}', [ExperiencesController::class, 'show'])->name('experiences.show');
            Route::put('/experiences/{id}', [ExperiencesController::class, 'update'])->name('experiences.update');
            Route::delete('/experiences/{id}', [ExperiencesController::class, 'destroy'])->name('experiences.destroy');

            // Education management
            Route::get('/education', [EducationController::class, 'index'])->name('education.index');
            Route::post('/education', [EducationController::class, 'store'])->name('education.store');
            Route::get('/education/{id}', [EducationController::class, 'show'])->name('education.show');
            Route::put('/education/{id}', [EducationController::class, 'update'])->name('education.update');
            Route::delete('/education/{id}', [EducationController::class, 'destroy'])->name('education.destroy');

            // Portfolio management
            Route::get('/portfolios', [PortfolioController::class, 'index'])->name('portfolios.index');
            Route::post('/portfolios', [PortfolioController::class, 'store'])->name('portfolios.store');
            Route::get('/portfolios/{id}', [PortfolioController::class, 'show'])->name('portfolios.show');
            Route::post('/portfolios/{id}', [PortfolioController::class, 'update'])->name('portfolios.update');
            Route::delete('/portfolios/{id}', [PortfolioController::class, 'destroy'])->name('portfolios.destroy');

            // Talent applications
            Route::get('/applications', [ApplicationController::class, 'index'])->name('applications.index');
            Route::get('/applications/{id}', [ApplicationController::class, 'show'])->name('applications.show');
        });
        
        // ============================================
        // RECRUITER ROUTES (Recruiter Role Required)
        // ============================================
        
        Route::prefix('recruiter')->middleware('ability:recruiter')->name('recruiter.')->group(function () {
            
            // Profile management
            Route::get('/profile', [RecruiterController::class, 'profile'])->name('profile');
            Route::put('/profile', [RecruiterController::class, 'updateProfile'])->name('profile.update');
            Route::post('/profile/logo', [RecruiterController::class, 'updateLogo'])->name('profile.logo');
            
            // Dashboard
            Route::get('/dashboard', [RecruiterController::class, 'dashboard'])->name('dashboard');
            
            // Talent search and management
            Route::get('/talents/search', [RecruiterController::class, 'searchTalents'])->name('talents.search');
            Route::get('/talents/{id}', [RecruiterController::class, 'viewTalent'])->name('talents.show');
            Route::post('/talents/{id}/save', [RecruiterController::class, 'saveTalent'])->name('talents.save');
            Route::delete('/talents/{id}/unsave', [RecruiterController::class, 'unsaveTalent'])->name('talents.unsave');
        });
        
        // ============================================
        // PROJECT ROUTES
        // ============================================
        
        Route::prefix('projects')->name('projects.')->group(function () {
            
            // Public project routes (authenticated users)
            Route::get('/', [ProjectController::class, 'index'])->name('index');
            Route::get('/search', [ProjectController::class, 'search'])->name('search');
            Route::get('/{id}', [ProjectController::class, 'show'])->name('show');
            
            // Recruiter-only project routes
            Route::middleware('ability:recruiter')->group(function () {
                Route::post('/', [ProjectController::class, 'store'])->name('store');
                Route::put('/{id}', [ProjectController::class, 'update'])->name('update');
                Route::delete('/{id}', [ProjectController::class, 'destroy'])->name('destroy');
                Route::post('/{id}/publish', [ProjectController::class, 'publish'])->name('publish');
                Route::post('/{id}/close', [ProjectController::class, 'close'])->name('close');
                Route::get('/{id}/applications', [ProjectController::class, 'applications'])->name('applications');
            });
        });
        
        // ============================================
        // APPLICATION ROUTES
        // ============================================
        
        Route::prefix('applications')->name('applications.')->group(function () {
            
            // Apply to project (talent only)
            Route::post('/', [ApplicationController::class, 'store'])
                ->middleware('ability:talent')
                ->name('store');
            
            // View application (accessible to both talent and recruiter)
            Route::get('/{id}', [ApplicationController::class, 'show'])->name('show');
            
            // Update application status (recruiter or talent can update based on logic in controller)
            Route::put('/{id}/status', [ApplicationController::class, 'updateStatus'])->name('status');
            
            // Add notes (recruiter only - enforced in controller)
            Route::post('/{id}/notes', [ApplicationController::class, 'addNotes'])->name('notes');
            
            // Withdraw application (talent only - enforced in controller)
            Route::delete('/{id}', [ApplicationController::class, 'destroy'])->name('withdraw');
        });
        
        // ============================================
        // REVIEW ROUTES
        // ============================================
        
        Route::prefix('reviews')->name('reviews.')->group(function () {
            Route::get('/user/{userId}', [ReviewController::class, 'getUserReviews'])->name('user');
            Route::post('/', [ReviewController::class, 'store'])->name('store');
            Route::get('/{id}', [ReviewController::class, 'show'])->name('show');
            Route::put('/{id}', [ReviewController::class, 'update'])->name('update');
            Route::delete('/{id}', [ReviewController::class, 'destroy'])->name('destroy');
        });
        
        // ============================================
        // MESSAGE ROUTES
        // ============================================
        
        Route::prefix('messages')->name('messages.')->group(function () {
            Route::get('/', [MessageController::class, 'index'])->name('index');
            Route::get('/conversations', [MessageController::class, 'conversations'])->name('conversations');
            Route::get('/conversations/{userId}', [MessageController::class, 'getConversation'])->name('conversation');
            Route::post('/', [MessageController::class, 'store'])->name('store');
            Route::get('/{id}', [MessageController::class, 'show'])->name('show');
            Route::put('/{id}/read', [MessageController::class, 'markAsRead'])->name('read');
            Route::delete('/{id}', [MessageController::class, 'destroy'])->name('destroy');
        });
        
        // ============================================
        // NOTIFICATION ROUTES
        // ============================================
        
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', [NotificationController::class, 'index'])->name('index');
            Route::get('/unread', [NotificationController::class, 'unread'])->name('unread');
            Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('read-all');
            Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('read');
            Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
            Route::delete('/read/all', [NotificationController::class, 'deleteAllRead'])->name('delete-all-read');
        });
        
        // ============================================
        // MEDIA UPLOAD ROUTES
        // ============================================
        
        Route::prefix('media')->name('media.')->group(function () {
            Route::post('/upload', [MediaController::class, 'upload'])->name('upload');
            Route::get('/{id}', [MediaController::class, 'show'])->name('show');
            Route::delete('/{id}', [MediaController::class, 'delete'])->name('delete');
        });
        
    }); // End of protected routes
    
    // ============================================
    // ADMIN ROUTES (Admin Role Required)
    // ============================================
    
    Route::prefix('admin')->middleware(['auth:sanctum', 'ability:admin'])->name('admin.')->group(function () {
        
        // Dashboard
        Route::get('/dashboard', function () {
            return response()->json([
                'message' => 'Admin dashboard data',
                'stats' => [
                    'total_users' => \App\Models\User::count(),
                    'total_projects' => \App\Models\Project::count(),
                    'total_applications' => \App\Models\Application::count(),
                ]
            ]);
        })->name('dashboard');
        
        // User management
        Route::get('/users', function () {
            $users = \App\Models\User::paginate(20);
            return response()->json($users);
        })->name('users');
        
        Route::put('/users/{id}/status', function ($id) {
            return response()->json(['message' => "Update user $id status"]);
        })->name('users.status');
        
        // Project moderation
        Route::get('/projects/pending', function () {
            return response()->json(['message' => 'Pending projects']);
        })->name('projects.pending');
        
        Route::post('/projects/{id}/approve', function ($id) {
            return response()->json(['message' => "Approve project $id"]);
        })->name('projects.approve');
        
        // Reports and analytics
        Route::get('/reports', function () {
            return response()->json(['message' => 'Reports and analytics']);
        })->name('reports');
    });
    
}); // End of v1 prefix

// ============================================
// FALLBACK ROUTE
// ============================================

Route::fallback(function () {
    return response()->json([
        'message' => 'API endpoint not found',
        'status' => 404,
        'available_endpoints' => '/api/v1/health'
    ], 404);
});