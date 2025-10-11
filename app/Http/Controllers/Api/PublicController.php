<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Skill;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    /**
     * Get all categories
     */
    public function categories()
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
            'data' => $categories,
            'categories' => $categories,
        ]);
    }

    /**
     * Get all skills
     */
    public function skills(Request $request)
    {
        $query = Skill::query();

        // Search filter
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Category filter
        if ($request->has('category_id')) {
            $query->whereHas('talentSkills', function ($q) use ($request) {
                $q->whereHas('talent.talentProfile', function ($q2) use ($request) {
                    $q2->where('primary_category_id', $request->category_id);
                });
            });
        }

        $skills = $query->orderBy('name')->get();

        return response()->json([
            'skills' => $skills
        ]);
    }

    /**
     * Get public project listings
     */
    public function projects(Request $request)
    {
        $query = Project::with(['recruiter.recruiterProfile', 'category'])
            ->where('status', 'open')
            ->where('visibility', 'public');

        // Search
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // Category filter
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Location filter
        if ($request->has('location')) {
            $query->where('location', 'like', '%' . $request->location . '%');
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
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $projects = $query->paginate($perPage);

        return response()->json($projects);
    }

    /**
     * Show single project
     */
    public function showProject($id)
    {
        $project = Project::with(['recruiter.recruiterProfile', 'category', 'skills'])
            ->where('status', 'open')
            ->where('visibility', 'public')
            ->findOrFail($id);

        // Increment view count
        $project->increment('views_count');

        return response()->json([
            'project' => $project
        ]);
    }

    /**
     * Get public talent profiles
     */
    public function talents(Request $request)
    {
        $query = User::with(['talentProfile.category', 'skills'])
            ->where('user_type', 'talent') // Changed from 'role' to 'user_type'
            ->where('account_status', 'active')
            ->whereHas('talentProfile', function ($q) {
                $q->where('is_public', true) // Changed from 'visibility'
                  ->where('is_available', true);
            });

        // Search
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->search . '%')
                  ->orWhere('last_name', 'like', '%' . $request->search . '%')
                  ->orWhere('bio', 'like', '%' . $request->search . '%')
                  ->orWhereHas('talentProfile', function ($q2) use ($request) {
                      $q2->where('summary', 'like', '%' . $request->search . '%');
                  });
            });
        }

        // Category filter
        if ($request->has('category_id')) {
            $query->whereHas('talentProfile', function ($q) use ($request) {
                $q->where('primary_category_id', $request->category_id); // Changed field name
            });
        }

        // Skills filter
        if ($request->has('skills')) {
            $skills = explode(',', $request->skills);
            $query->whereHas('talentSkills', function ($q) use ($skills) {
                $q->whereIn('skill_id', $skills);
            });
        }

        // Location filter
        if ($request->has('location')) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }

        // Experience level
        if ($request->has('experience_level')) {
            $query->whereHas('talentProfile', function ($q) use ($request) {
                $q->where('experience_level', $request->experience_level);
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $talents = $query->paginate($perPage);

        return response()->json($talents);
    }

    /**
     * Show single talent profile
     */
    public function showTalent($id)
    {
        $talent = User::with([
            'talentProfile.category',
            'talentSkills.skill',
            'portfolios',
            'experiences',
            'education',
            'reviews' => function ($q) {
                $q->where('is_approved', true)->latest()->take(5);
            }
        ])
        ->where('user_type', 'talent') // Changed from 'role'
        ->where('account_status', 'active')
        ->whereHas('talentProfile', function ($q) {
            $q->where('is_public', true); // Changed from 'visibility'
        })
        ->findOrFail($id);

        // Increment profile view count
        $talent->talentProfile->increment('profile_views'); // Changed field name

        return response()->json([
            'talent' => $talent
        ]);
    }
}