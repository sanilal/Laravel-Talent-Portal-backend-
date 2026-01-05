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
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DropdownController;
use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\ProjectTypeController;
use App\Http\Controllers\Api\SkillController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\CastingCallController;
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
    // CORS OPTIONS HANDLER (Inside v1 prefix)
    // ============================================
    Route::options('{any}', function () {
        $allowedOrigins = explode(',', env('FRONTEND_URL', 'http://localhost:3000'));
        $origin = request()->header('Origin');
        
        $response = response('', 200)
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept')
            ->header('Access-Control-Allow-Credentials', 'true')
            ->header('Access-Control-Max-Age', '86400');
        
        if (in_array($origin, $allowedOrigins)) {
            $response->header('Access-Control-Allow-Origin', $origin);
        }
        
        return $response;
    })->where('any', '.*');

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

    // Embedding service test
    Route::get('/test-embeddings', function () {
        try {
            $service = app(\App\Services\EmbeddingService::class);
            $testText = "Senior Full Stack Developer with expertise in Laravel, PostgreSQL, React, and Next.js";
            $embedding = $service->generateEmbedding($testText);

            return response()->json([
                'success' => true,
                'test_text' => $testText,
                'embedding_dimensions' => count($embedding),
                'first_5_values' => array_slice($embedding, 0, 5),
                'service_url' => config('services.embeddings.url'),
                'message' => 'Embedding service is working correctly!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    });

    // ============================================
    // PUBLIC ROUTES (No Authentication Required)
    // ============================================

    Route::prefix('public')->name('public.')->group(function () {

        // ============================================
        // CATEGORIES & SUBCATEGORIES
        // ============================================
        
        /**
         * GET /api/v1/public/categories
         * Get all categories with subcategories
         * Compatible with: yourmoca.com/api/getCategories
         */
        Route::get('/categories', [CategoryController::class, 'index'])
            ->name('categories.index');

        /**
         * GET /api/v1/public/categories/{id}
         * Get a single category with subcategories
         */
        Route::get('/categories/{id}', [CategoryController::class, 'show'])
            ->name('categories.show');

        /**
         * GET /api/v1/public/categories/{categoryId}/subcategories
         * Get subcategories for a specific category
         */
        Route::get('/categories/{categoryId}/subcategories', [CategoryController::class, 'subcategories'])
            ->name('categories.subcategories');

        /**
         * GET /api/v1/public/subcategories/{id}
         * Get a single subcategory
         */
        Route::get('/subcategories/{id}', [CategoryController::class, 'showSubcategory'])
            ->name('subcategories.show');

        // ============================================
        // DROPDOWN VALUES (Unified Endpoint)
        // ============================================
        
        /**
         * GET /api/v1/public/dropdown-list?type=1
         * Get dropdown values by type
         * Compatible with: yourmoca.com/api/getDropdownList
         * 
         * Types:
         * 1 = Height, 2 = Skin Tone, 3 = Weight, 4 = Age Range
         * 5 = Gender, 6 = Service Type, 7 = Event Type, 8 = Budget Range
         * 9 = Eye Color, 10 = Hair Color, 11 = Body Type, 12 = Vocal Range
         * 13 = Experience Level, 14 = Language Proficiency, 15 = Vehicle Type
         */
        Route::get('/dropdown-list', [DropdownController::class, 'index'])
            ->name('dropdown.index');

        /**
         * POST /api/v1/public/dropdown-list/multiple
         * Get multiple dropdown types at once
         * Body: { "types": [1, 2, 3] }
         */
        Route::post('/dropdown-list/multiple', [DropdownController::class, 'multiple'])
            ->name('dropdown.multiple');

        /**
         * GET /api/v1/public/dropdown-list/all
         * Get all dropdown types with values
         */
        Route::get('/dropdown-list/all', [DropdownController::class, 'all'])
            ->name('dropdown.all');

        /**
         * GET /api/v1/public/attributes/profile-level
         * Get available profile-level attribute definitions
         * Returns field definitions for physical attributes (height, weight, etc.)
         */
        Route::get('/attributes/profile-level', [TalentProfileController::class, 'getAvailableAttributes'])
            ->name('attributes.profile-level');

        // ============================================
        // COUNTRIES & STATES
        // ============================================
        
        /**
         * GET /api/v1/public/countries
         * Get all countries
         */
        Route::get('/countries', [CountryController::class, 'index'])
            ->name('countries.index');

        /**
         * GET /api/v1/public/countries/search
         * Search countries by name or code
         */
        Route::get('/countries/search', [CountryController::class, 'search'])
            ->name('countries.search');

        /**
         * GET /api/v1/public/countries/{id}
         * Get a specific country
         */
        Route::get('/countries/{id}', [CountryController::class, 'show'])
            ->name('countries.show');

        /**
         * GET /api/v1/public/states
         * Get states by country_id
         * Query: ?country_id={uuid}
         */
        Route::get('/states', [CountryController::class, 'states'])
            ->name('states.index');

        // ============================================
        // PROJECT TYPES
        // ============================================
        
        /**
         * GET /api/v1/public/project-types
         * Get all project types
         */
        Route::get('/project-types', [ProjectTypeController::class, 'index'])
            ->name('project-types.index');

        /**
         * GET /api/v1/public/project-types/{id}
         * Get a specific project type
         */
        Route::get('/project-types/{id}', [ProjectTypeController::class, 'show'])
            ->name('project-types.show');

        // ============================================
        // GENRES
        // ============================================
        
        /**
         * GET /api/v1/public/genres
         * Get all genres for casting calls
         */
        Route::get('/genres', function () {
            return response()->json([
                'success' => true,
                'message' => 'Genres retrieved successfully',
                'data' => \App\Models\Genre::active()->ordered()->get(),
            ]);
        })->name('genres.index');

        /**
         * GET /api/v1/public/genres/{id}
         * Get a single genre
         */
        Route::get('/genres/{id}', function ($id) {
            try {
                $genre = \App\Models\Genre::findOrFail($id);
                return response()->json([
                    'success' => true,
                    'message' => 'Genre retrieved successfully',
                    'data' => $genre,
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Genre not found',
                ], 404);
            }
        })->name('genres.show');

        // ============================================
        // SKILLS
        // ============================================
        
        /**
         * GET /api/v1/public/skills
         * Get all skills with optional filters
         */
        Route::get('/skills', [SkillController::class, 'index'])
            ->name('skills.index');

        /**
         * GET /api/v1/public/skills/{id}
         * Get a specific skill
         */
        Route::get('/skills/{id}', [SkillController::class, 'show'])
            ->name('skills.show');

        /**
         * GET /api/v1/public/skills/search
         * Search skills by name
         */
        Route::get('/skills/search', [SkillController::class, 'search'])
            ->name('skills.search');

        /**
         * GET /api/v1/public/skills/by-category
         * Get skills grouped by category
         */
        Route::get('/skills/by-category', [SkillController::class, 'byCategory'])
            ->name('skills.by-category');

        /**
         * GET /api/v1/public/skills/featured
         * Get featured skills
         */
        Route::get('/skills/featured', [SkillController::class, 'featured'])
            ->name('skills.featured');

        /**
         * GET /api/v1/public/skills/stats
         * Get skill statistics
         */
        Route::get('/skills/stats', [SkillController::class, 'stats'])
            ->name('skills.stats');

        // ============================================
        // TALENTS (Public Browse)
        // ============================================
        
        /**
         * GET /api/v1/public/talents
         * Browse all talents
         */
        Route::get('/talents', [PublicController::class, 'talents'])
            ->name('talents');

        /**
         * GET /api/v1/public/talents/{id}
         * View talent profile
         */
        Route::get('/talents/{id}', [PublicController::class, 'talentProfile'])
            ->name('talents.show');

        /**
         * GET /api/v1/public/profiles
         * Browse all profiles (talents)
         */
        Route::get('/profiles', [ProfileController::class, 'index'])
            ->name('profiles.index');

        /**
         * GET /api/v1/public/profiles/{id}
         * View a specific profile
         */
        Route::get('/profiles/{id}', [ProfileController::class, 'show'])
            ->name('profiles.show');

        // ============================================
        // PROJECTS (Public Browse)
        // ============================================
        
        /**
         * GET /api/v1/public/projects
         * Browse all projects
         */
        Route::get('/projects', [PublicController::class, 'projects'])
            ->name('projects');

        /**
         * GET /api/v1/public/projects/{id}
         * View project details
         */
        Route::get('/projects/{id}', [PublicController::class, 'projectDetail'])
            ->name('projects.show');

        // ============================================
        // CASTING CALLS (Public Browse)
        // ============================================
        
        /**
         * GET /api/v1/public/casting-calls
         * Browse all published casting calls
         * Query params: genre_id, project_type_id, location, is_featured, is_urgent, search, per_page, page
         */
        Route::get('/casting-calls', [CastingCallController::class, 'index'])
            ->name('casting-calls.index');

        /**
         * GET /api/v1/public/casting-calls/{id}
         * View single casting call details
         */
        Route::get('/casting-calls/{id}', [CastingCallController::class, 'show'])
            ->name('casting-calls.show');

    }); // End of public routes

    // ============================================
    // AUTHENTICATION ROUTES
    // ============================================

    Route::prefix('auth')->name('auth.')->group(function () {

        // Basic Authentication
        Route::post('/register', [AuthController::class, 'register'])->name('register');
        Route::post('/login', [AuthController::class, 'login'])->name('login');
        Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum')->name('logout');

        // Email Verification
        Route::prefix('email')->name('email.')->middleware('auth:sanctum')->group(function () {
            Route::get('/status', [EmailVerificationController::class, 'status'])->name('status');
            Route::post('/verify', [EmailVerificationController::class, 'verify'])->name('verify');
            Route::post('/resend', [EmailVerificationController::class, 'resend'])->name('resend');
        });

        // Password Reset
        Route::prefix('password')->name('password.')->group(function () {
            Route::post('/email', [PasswordResetController::class, 'sendResetLink'])->name('email');
            Route::post('/reset', [PasswordResetController::class, 'reset'])->name('reset');
        });

        // Two Factor Authentication
        Route::prefix('2fa')->name('2fa.')->middleware('auth:sanctum')->group(function () {
            Route::post('/enable', [TwoFactorController::class, 'enable'])->name('enable');
            Route::post('/verify', [TwoFactorController::class, 'verify'])->name('verify');
            Route::post('/confirm', [TwoFactorController::class, 'confirm'])->name('confirm');
            Route::post('/disable', [TwoFactorController::class, 'disable'])->name('disable');
        });
    });

    // ============================================
    // PROTECTED ROUTES (Authentication Required)
    // ============================================

    Route::middleware(['auth:sanctum', 'verified'])->group(function () {

        // User Profile
        Route::get('/user', [AuthController::class, 'user'])->name('user');
        Route::put('/user/profile', [AuthController::class, 'updateProfile'])->name('user.update');

        // ============================================
        // TALENT ROUTES
        // ============================================

        Route::prefix('talent')->name('talent.')->middleware('role:talent')->group(function () {
            
            // Dashboard
            Route::get('/dashboard', [TalentProfileController::class, 'dashboard'])->name('dashboard');
            
            // Talent Profile
            Route::get('/profile', [TalentProfileController::class, 'show'])->name('profile.show');
            Route::post('/profile', [TalentProfileController::class, 'store'])->name('profile.store');
            Route::put('/profile', [TalentProfileController::class, 'update'])->name('profile.update');
            Route::delete('/profile', [TalentProfileController::class, 'destroy'])->name('profile.destroy');

            // Profile Attributes
            Route::get('/profile/attributes', [TalentProfileController::class, 'getAttributes'])->name('profile.attributes');
            Route::put('/profile/attributes', [TalentProfileController::class, 'updateAttributes'])->name('profile.attributes.update');
            Route::delete('/profile/attributes/{fieldName}', [TalentProfileController::class, 'deleteAttribute'])->name('profile.attributes.delete');

            // Skills
            Route::prefix('skills')->name('skills.')->group(function () {
                Route::get('/', [TalentSkillsController::class, 'index'])->name('index');
                Route::post('/', [TalentSkillsController::class, 'store'])->name('store');
                Route::get('/{id}', [TalentSkillsController::class, 'show'])->name('show');
                Route::put('/{id}', [TalentSkillsController::class, 'update'])->name('update');
                Route::delete('/{id}', [TalentSkillsController::class, 'destroy'])->name('destroy');
                Route::post('/{id}/set-primary', [TalentSkillsController::class, 'setPrimary'])->name('set-primary');
                Route::post('/reorder', [TalentSkillsController::class, 'reorder'])->name('reorder');
                Route::get('/{skillId}/attributes', [TalentSkillsController::class, 'getSkillAttributes'])->name('attributes');
            });

            // Experience
            Route::prefix('experiences')->name('experiences.')->group(function () {
                Route::get('/', [ExperiencesController::class, 'index'])->name('index');
                Route::post('/', [ExperiencesController::class, 'store'])->name('store');
                Route::get('/{id}', [ExperiencesController::class, 'show'])->name('show');
                Route::put('/{id}', [ExperiencesController::class, 'update'])->name('update');
                Route::delete('/{id}', [ExperiencesController::class, 'destroy'])->name('destroy');
            });

            // Education
            Route::prefix('education')->name('education.')->group(function () {
                Route::get('/', [EducationController::class, 'index'])->name('index');
                Route::post('/', [EducationController::class, 'store'])->name('store');
                Route::get('/{id}', [EducationController::class, 'show'])->name('show');
                Route::put('/{id}', [EducationController::class, 'update'])->name('update');
                Route::delete('/{id}', [EducationController::class, 'destroy'])->name('destroy');
            });

            // Portfolio
            Route::prefix('portfolio')->name('portfolio.')->group(function () {
                Route::get('/', [PortfolioController::class, 'index'])->name('index');
                Route::post('/', [PortfolioController::class, 'store'])->name('store');
                Route::get('/{id}', [PortfolioController::class, 'show'])->name('show');
                Route::put('/{id}', [PortfolioController::class, 'update'])->name('update');
                Route::delete('/{id}', [PortfolioController::class, 'destroy'])->name('destroy');
            });

            // Applications
            Route::get('/applications', [ApplicationController::class, 'talentApplications'])->name('applications');

            // Casting Call Applications
            Route::post('/casting-calls/{id}/applications', [CastingCallController::class, 'submitApplication'])->name('casting-calls.apply');
        });

        // ============================================
        // RECRUITER ROUTES
        // ============================================

        Route::prefix('recruiter')->name('recruiter.')->middleware('role:recruiter')->group(function () {
            
            // Recruiter Profile
            Route::get('/dashboard', [RecruiterController::class, 'dashboard'])->name('dashboard');
            Route::get('/profile', [RecruiterController::class, 'showProfile'])->name('profile.show');
            Route::post('/profile', [RecruiterController::class, 'createProfile'])->name('profile.store');
            Route::put('/profile', [RecruiterController::class, 'updateProfile'])->name('profile.update');
            
            // Projects
            Route::prefix('projects')->name('projects.')->group(function () {
                Route::get('/', [ProjectController::class, 'index'])->name('index');
                Route::post('/', [ProjectController::class, 'store'])->name('store');
                Route::get('/{id}', [ProjectController::class, 'show'])->name('show');
                Route::put('/{id}', [ProjectController::class, 'update'])->name('update');
                Route::delete('/{id}', [ProjectController::class, 'destroy'])->name('destroy');
                Route::post('/{id}/publish', [ProjectController::class, 'publish'])->name('publish');
                Route::post('/{id}/close', [ProjectController::class, 'close'])->name('close');
            });

            // Casting Calls
            Route::prefix('casting-calls')->name('casting-calls.')->group(function () {
                /**
                 * GET /api/v1/recruiter/casting-calls
                 * Get recruiter's casting calls
                 * Query params: status, per_page, page, sort_by, sort_order
                 */
                Route::get('/', [CastingCallController::class, 'recruiterIndex'])->name('index');
                
                /**
                 * POST /api/v1/recruiter/casting-calls
                 * Create new casting call
                 */
                Route::post('/', [CastingCallController::class, 'store'])->name('store');
                
                /**
                 * GET /api/v1/recruiter/casting-calls/{id}
                 * Get single casting call (owned by recruiter)
                 */
                Route::get('/{id}', [CastingCallController::class, 'show'])->name('show');
                
                /**
                 * PUT /api/v1/recruiter/casting-calls/{id}
                 * Update casting call
                 */
                Route::put('/{id}', [CastingCallController::class, 'update'])->name('update');
                
                /**
                 * DELETE /api/v1/recruiter/casting-calls/{id}
                 * Delete casting call
                 */
                Route::delete('/{id}', [CastingCallController::class, 'destroy'])->name('destroy');
                
                /**
                 * POST /api/v1/recruiter/casting-calls/{id}/publish
                 * Publish casting call (change status from draft to published)
                 */
                Route::post('/{id}/publish', [CastingCallController::class, 'publish'])->name('publish');
                
                /**
                 * POST /api/v1/recruiter/casting-calls/{id}/close
                 * Close casting call (stop accepting applications)
                 */
                Route::post('/{id}/close', [CastingCallController::class, 'close'])->name('close');
            });
            
            // Applications
            Route::get('/applications', [ApplicationController::class, 'recruiterApplications'])->name('applications');
        });

        // ============================================
        // SHARED ROUTES (Both Talent & Recruiter)
        // ============================================

        // Projects (View/Apply)
        Route::prefix('projects')->name('projects.')->group(function () {
            Route::get('/', [ProjectController::class, 'publicIndex'])->name('public.index');
            Route::get('/{id}', [ProjectController::class, 'publicShow'])->name('public.show');
            Route::post('/{id}/apply', [ApplicationController::class, 'apply'])->name('apply');
        });

        // Casting Calls (Apply - For Talents)
        Route::prefix('casting-calls')->name('casting-calls.')->group(function () {
            /**
             * POST /api/v1/casting-calls/{id}/apply
             * Apply to a casting call (talents only)
             */
            Route::post('/{id}/apply', [ApplicationController::class, 'apply'])->name('apply');
        });

        // Applications
        Route::prefix('applications')->name('applications.')->group(function () {
            Route::get('/{id}', [ApplicationController::class, 'show'])->name('show');
            Route::put('/{id}/status', [ApplicationController::class, 'updateStatus'])->name('update-status');
            Route::delete('/{id}', [ApplicationController::class, 'destroy'])->name('destroy');
        });

        // Reviews
        Route::prefix('reviews')->name('reviews.')->group(function () {
            Route::get('/', [ReviewController::class, 'index'])->name('index');
            Route::post('/', [ReviewController::class, 'store'])->name('store');
            Route::get('/{id}', [ReviewController::class, 'show'])->name('show');
            Route::put('/{id}', [ReviewController::class, 'update'])->name('update');
            Route::delete('/{id}', [ReviewController::class, 'destroy'])->name('destroy');
        });

        // Messages
        Route::prefix('messages')->name('messages.')->group(function () {
            Route::get('/', [MessageController::class, 'index'])->name('index');
            Route::post('/', [MessageController::class, 'store'])->name('store');
            Route::get('/conversations', [MessageController::class, 'conversations'])->name('conversations');
            Route::get('/{id}', [MessageController::class, 'show'])->name('show');
            Route::delete('/{id}', [MessageController::class, 'destroy'])->name('destroy');
        });

        // Notifications
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', [NotificationController::class, 'index'])->name('index');
            Route::get('/unread', [NotificationController::class, 'unread'])->name('unread');
            Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('read-all');
            Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('read');
            Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
            Route::delete('/read/all', [NotificationController::class, 'deleteAllRead'])->name('delete-all-read');
        });

        // Media Upload
        Route::prefix('media')->name('media.')->group(function () {
            Route::post('/upload', [MediaController::class, 'upload'])->name('upload');
            Route::get('/{id}', [MediaController::class, 'show'])->name('show');
            Route::delete('/{id}', [MediaController::class, 'delete'])->name('delete');
        });

        // ============================================
        // SEARCH & MATCHING API ROUTES
        // ============================================

        Route::prefix('search')->name('search.')->group(function () {
            /**
             * Semantic Talent Search
             * POST /api/v1/search/talents
             * Body: { "query": "React developer with 5 years", "limit": 20, "min_similarity": 0.7 }
             */
            Route::post('/talents', [SearchController::class, 'searchTalents'])->name('talents');
        });

        /**
         * Project-to-Talent Matching
         * POST /api/v1/projects/{project}/match-talents
         */
        Route::post('/projects/{project}/match-talents', [SearchController::class, 'matchTalentsToProject'])
            ->name('projects.match-talents');

        /**
         * Talent-to-Project Matching
         * POST /api/v1/talents/{talent}/match-projects
         */
        Route::post('/talents/{talent}/match-projects', [SearchController::class, 'matchProjectsToTalent'])
            ->name('talents.match-projects');

        /**
         * Similar Portfolios
         * GET /api/v1/portfolios/{portfolio}/similar
         */
        Route::get('/portfolios/{portfolio}/similar', [SearchController::class, 'similarPortfolios'])
            ->name('portfolios.similar');

        /**
         * Related Skills
         * GET /api/v1/skills/{skill}/related
         */
        Route::get('/skills/{skill}/related', [SearchController::class, 'relatedSkills'])
            ->name('skills.related');

        /**
         * Smart Recommendations
         * GET /api/v1/talents/{talent}/recommendations
         */
        Route::get('/talents/{talent}/recommendations', [SearchController::class, 'recommendations'])
            ->name('talents.recommendations');

    }); // End of protected routes

    // ============================================
    // ADMIN ROUTES (Admin Role Required)
    // ============================================

    Route::prefix('admin')->middleware(['auth:sanctum', 'role:admin'])->name('admin.')->group(function () {

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