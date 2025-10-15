<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Skill;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    /**
     * Get all categories with talent counts.
     */
    public function categories(): JsonResponse
    {
        $categories = Category::withCount(['talentProfiles as talents_count' => function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('user_type', 'talent')
                      ->where('account_status', 'active');
                })
                ->where('is_public', true)
                ->where('is_available', true);
            }])
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * Get all active skills.
     */
    public function skills(Request $request): JsonResponse
    {
        $query = Skill::where('is_active', true);

        // Search filter
        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        // Category filter
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Only return skills with talents if requested
        if ($request->get('with_talents', false)) {
            $query->where('talents_count', '>', 0);
        }

        $skills = $query->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $skills,
        ]);
    }

    /**
     * Get public project listings.
     */
    public function projects(Request $request): JsonResponse
    {
        $query = Project::with(['recruiter.recruiterProfile', 'category'])
            ->where('status', 'open')
            ->where('visibility', 'public');

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Location filter
        if ($request->has('location')) {
            $query->where('location', 'like', "%{$request->location}%");
        }

        // Budget range
        if ($request->has('min_budget')) {
            $query->where('budget_max', '>=', $request->min_budget);
        }
        if ($request->has('max_budget')) {
            $query->where('budget_min', '<=', $request->max_budget);
        }

        // Project type
        if ($request->has('project_type')) {
            $query->where('project_type', $request->project_type);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        $allowedSorts = ['created_at', 'budget_min', 'budget_max', 'title'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Pagination
        $perPage = min($request->get('per_page', 15), 50);
        $projects = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $projects,
        ]);
    }

    /**
     * Show single project.
     */
    public function showProject(string $id): JsonResponse
    {
        $project = Project::with(['recruiter.recruiterProfile', 'category', 'skills'])
            ->where('status', 'open')
            ->where('visibility', 'public')
            ->find($id);

        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => 'Project not found or not available.',
            ], 404);
        }

        // Increment view count
        $project->increment('views_count');

        return response()->json([
            'success' => true,
            'data' => $project,
        ]);
    }

    /**
     * Get public talent directory with filtering and search.
     */
    public function talents(Request $request): JsonResponse
    {
        $query = User::with([
                'talentProfile.category',
                'talentSkills' => function ($query) {
                    $query->where('show_on_profile', true)
                          ->orderBy('display_order', 'asc')
                          ->orderBy('is_primary', 'desc')
                          ->with('skill');
                },
            ])
            ->where('user_type', 'talent')
            ->where('account_status', 'active')
            ->whereHas('talentProfile', function ($q) {
                $q->where('is_public', true)
                  ->where('is_available', true);
            });

        // Search by name, title, or bio
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('professional_title', 'like', "%{$search}%")
                  ->orWhere('bio', 'like', "%{$search}%")
                  ->orWhereHas('talentProfile', function ($q2) use ($search) {
                      $q2->where('summary', 'like', "%{$search}%")
                         ->orWhere('professional_title', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by category
        if ($request->has('category_id')) {
            $query->whereHas('talentProfile', function ($q) use ($request) {
                $q->where('primary_category_id', $request->category_id);
            });
        }

        // Filter by skills (multiple skills)
        if ($request->has('skills')) {
            $skills = is_array($request->skills) 
                ? $request->skills 
                : explode(',', $request->skills);
                
            $query->whereHas('talentSkills', function ($q) use ($skills) {
                $q->whereIn('skill_id', $skills);
            });
        }

        // Filter by experience level
        if ($request->has('experience_level')) {
            $query->where('experience_level', $request->experience_level);
        }

        // Filter by availability status
        if ($request->has('availability_status')) {
            $query->where('availability_status', $request->availability_status);
        }

        // Filter by availability type (full-time, part-time, contract, etc.)
        if ($request->has('availability_type')) {
            $query->whereHas('talentProfile', function ($q) use ($request) {
                $q->whereJsonContains('availability_types', $request->availability_type);
            });
        }

        // Filter by hourly rate range (from talent_profiles)
        if ($request->has('min_rate') || $request->has('max_rate')) {
            $query->whereHas('talentProfile', function ($q) use ($request) {
                if ($request->has('min_rate')) {
                    $q->where('hourly_rate_max', '>=', $request->min_rate);
                }
                if ($request->has('max_rate')) {
                    $q->where('hourly_rate_min', '<=', $request->max_rate);
                }
            });
        }

        // Filter by location
        if ($request->has('location')) {
            $location = $request->location;
            $query->where(function ($q) use ($location) {
                $q->where('city', 'like', "%{$location}%")
                  ->orWhere('state', 'like', "%{$location}%")
                  ->orWhere('country', 'like', "%{$location}%")
                  ->orWhere('location', 'like', "%{$location}%")
                  ->orWhereHas('talentProfile', function ($q2) use ($location) {
                      $q2->whereJsonContains('preferred_locations', $location);
                  });
            });
        }

        // Filter by primary skill
        if ($request->has('primary_skill')) {
            $query->whereHas('talentSkills', function ($q) use ($request) {
                $q->where('skill_id', $request->primary_skill)
                  ->where('is_primary', true);
            });
        }

        // Filter by languages
        if ($request->has('language')) {
            $query->whereJsonContains('languages', $request->language);
        }

        // Filter featured talents
        if ($request->get('featured', false)) {
            $query->whereHas('talentProfile', function ($q) {
                $q->where('is_featured', true);
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        $allowedSorts = ['created_at', 'first_name', 'last_name', 'profile_views'];
        
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        } elseif ($sortBy === 'hourly_rate') {
            // Sort by talent profile hourly rate
            $query->join('talent_profiles', 'users.id', '=', 'talent_profiles.user_id')
                  ->orderBy('talent_profiles.hourly_rate_min', $sortOrder)
                  ->select('users.*');
        } elseif ($sortBy === 'rating') {
            // Sort by average rating from talent profile
            $query->join('talent_profiles', 'users.id', '=', 'talent_profiles.user_id')
                  ->orderBy('talent_profiles.average_rating', $sortOrder)
                  ->select('users.*');
        }

        // Pagination
        $perPage = min($request->get('per_page', 15), 50);
        $talents = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $talents,
        ]);
    }

    /**
     * Show individual talent profile (public view).
     */
    public function showTalent(string $id): JsonResponse
    {
        $talent = User::with([
                'talentProfile.category',
                'talentSkills' => function ($query) {
                    $query->where('show_on_profile', true)
                          ->orderBy('display_order', 'asc')
                          ->orderBy('is_primary', 'desc')
                          ->with('skill.category');
                },
                'portfolios' => function ($query) {
                    $query->orderBy('display_order', 'asc');
                },
                'experiences' => function ($query) {
                    $query->orderBy('start_date', 'desc');
                },
                'education' => function ($query) {
                    $query->orderBy('start_date', 'desc');
                },
                'receivedReviews' => function ($query) {
                    $query->where('is_approved', true)
                          ->with('reviewer:id,first_name,last_name,avatar')
                          ->latest()
                          ->limit(10);
                }
            ])
            ->where('user_type', 'talent')
            ->where('account_status', 'active')
            ->whereHas('talentProfile', function ($q) {
                $q->where('is_public', true);
            })
            ->find($id);

        if (!$talent) {
            return response()->json([
                'success' => false,
                'message' => 'Talent not found or not available.',
            ], 404);
        }

        // Increment profile views (both places for consistency)
        $talent->increment('profile_views');
        if ($talent->talentProfile) {
            $talent->talentProfile->increment('profile_views');
        }

        // Calculate stats
        $completedProjects = $talent->applications()
            ->whereHas('project', function ($q) {
                $q->where('status', 'completed');
            })
            ->count();

        $stats = [
            'total_projects' => $completedProjects,
            'total_reviews' => $talent->talentProfile->total_ratings ?? 0,
            'average_rating' => $talent->talentProfile->average_rating ?? 0,
            'profile_views' => $talent->talentProfile->profile_views ?? 0,
            'profile_completion' => $talent->talentProfile->profile_completion_percentage ?? 0,
            'member_since' => $talent->created_at->format('Y-m-d'),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'talent' => $talent,
                'stats' => $stats,
            ],
        ]);
    }

    /**
     * Global search across talents, skills, and projects.
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        
        if (empty($query) || strlen($query) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'Search query must be at least 2 characters.',
            ], 400);
        }

        // Search talents
        $talents = User::where('user_type', 'talent')
            ->where('account_status', 'active')
            ->whereHas('talentProfile', function ($q) {
                $q->where('is_public', true)
                  ->where('is_available', true);
            })
            ->where(function ($q) use ($query) {
                $q->where('first_name', 'like', "%{$query}%")
                  ->orWhere('last_name', 'like', "%{$query}%")
                  ->orWhere('professional_title', 'like', "%{$query}%")
                  ->orWhere('bio', 'like', "%{$query}%")
                  ->orWhereHas('talentProfile', function ($q2) use ($query) {
                      $q2->where('summary', 'like', "%{$query}%")
                         ->orWhere('professional_title', 'like', "%{$query}%");
                  });
            })
            ->with([
                'talentProfile:id,user_id,professional_title,average_rating,hourly_rate_min,hourly_rate_max,currency',
                'talentSkills' => function ($q) {
                    $q->where('show_on_profile', true)
                      ->where('is_primary', true)
                      ->with('skill:id,name,slug,icon')
                      ->limit(3);
                }
            ])
            ->limit(10)
            ->get();

        // Search skills
        $skills = Skill::where('is_active', true)
            ->where('name', 'like', "%{$query}%")
            ->orderBy('talents_count', 'desc')
            ->limit(10)
            ->get();

        // Search projects
        $projects = Project::where('status', 'open')
            ->where('visibility', 'public')
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            })
            ->with([
                'recruiter:id,first_name,last_name,avatar',
                'category:id,name,slug'
            ])
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'talents' => $talents,
                'skills' => $skills,
                'projects' => $projects,
            ],
            'meta' => [
                'query' => $query,
                'total_results' => $talents->count() + $skills->count() + $projects->count(),
            ]
        ]);
    }
}