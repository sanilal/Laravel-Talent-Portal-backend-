<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Skill;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SkillController extends Controller
{
    /**
     * Get all active skills for public access
     * GET /api/v1/public/skills
     * 
     * Used by:
     * - Recruiters when creating projects (to select required skills)
     * - Talents when browsing available skills to add
     */
    public function index(Request $request): JsonResponse
    {
        $query = Skill::with(['category', 'subcategory']);

        // Filter by active status (default: only active)
        if (!$request->has('include_inactive')) {
            $query->where('is_active', true);
        }

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by subcategory
        if ($request->has('subcategory_id')) {
            $query->where('subcategory_id', $request->subcategory_id);
        }

        // Search by name
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter featured skills
        if ($request->has('featured') && $request->featured) {
            $query->where('is_featured', true);
        }

        // Order results
        $sortBy = $request->input('sort_by', 'name');
        $sortOrder = $request->input('sort_order', 'asc');
        
        if ($sortBy === 'popular') {
            $query->orderBy('talents_count', 'desc');
        } elseif ($sortBy === 'usage') {
            $query->orderBy('usage_count', 'desc');
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Paginate or get all
        if ($request->has('paginate') && $request->paginate !== 'false') {
            $perPage = $request->input('per_page', 50);
            $skills = $query->paginate($perPage);
        } else {
            $skills = $query->get();
        }

        // Transform response
        $transformedSkills = is_a($skills, 'Illuminate\Pagination\LengthAwarePaginator') 
            ? $skills->getCollection() 
            : $skills;

        $transformedSkills->transform(function ($skill) {
            return [
                'id' => $skill->id,
                'name' => $skill->name,
                'slug' => $skill->slug,
                'description' => $skill->description,
                'icon' => $skill->icon,
                'category' => $skill->category ? [
                    'id' => $skill->category->id,
                    'name' => $skill->category->name,
                    'slug' => $skill->category->slug,
                ] : null,
                'subcategory' => $skill->subcategory ? [
                    'id' => $skill->subcategory->id,
                    'name' => $skill->subcategory->name,
                    'slug' => $skill->subcategory->slug,
                ] : null,
                'is_featured' => $skill->is_featured,
                'is_active' => $skill->is_active,
                'usage_count' => $skill->usage_count,
                'talents_count' => $skill->talents_count,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => is_a($skills, 'Illuminate\Pagination\LengthAwarePaginator') 
                ? $skills 
                : $transformedSkills,
        ]);
    }

    /**
     * Get a single skill by ID
     * GET /api/v1/public/skills/{id}
     */
    public function show(string $id): JsonResponse
    {
        $skill = Skill::with(['category', 'subcategory'])
            ->where('id', $id)
            ->where('is_active', true)
            ->first();

        if (!$skill) {
            return response()->json([
                'success' => false,
                'message' => 'Skill not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $skill->id,
                'name' => $skill->name,
                'slug' => $skill->slug,
                'description' => $skill->description,
                'icon' => $skill->icon,
                'category' => $skill->category ? [
                    'id' => $skill->category->id,
                    'name' => $skill->category->name,
                    'slug' => $skill->category->slug,
                ] : null,
                'subcategory' => $skill->subcategory ? [
                    'id' => $skill->subcategory->id,
                    'name' => $skill->subcategory->name,
                    'slug' => $skill->subcategory->slug,
                ] : null,
                'is_featured' => $skill->is_featured,
                'is_active' => $skill->is_active,
                'usage_count' => $skill->usage_count,
                'talents_count' => $skill->talents_count,
                'metadata' => $skill->metadata,
            ],
        ]);
    }

    /**
     * Get skills grouped by category
     * GET /api/v1/public/skills/by-category
     */
    public function byCategory(Request $request): JsonResponse
    {
        $skills = Skill::with('category')
            ->where('is_active', true)
            ->orderBy('name', 'asc')
            ->get();

        $grouped = $skills->groupBy(function ($skill) {
            return $skill->category ? $skill->category->name : 'Uncategorized';
        })->map(function ($categorySkills, $categoryName) {
            $category = $categorySkills->first()->category;
            
            return [
                'category' => $category ? [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                ] : null,
                'skills' => $categorySkills->map(function ($skill) {
                    return [
                        'id' => $skill->id,
                        'name' => $skill->name,
                        'slug' => $skill->slug,
                        'icon' => $skill->icon,
                        'description' => $skill->description,
                        'talents_count' => $skill->talents_count,
                    ];
                })->values(),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $grouped,
        ]);
    }

    /**
     * Search skills by query
     * GET /api/v1/public/skills/search?q=keyword
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->input('q', '');
        
        if (empty($query)) {
            return response()->json([
                'success' => false,
                'message' => 'Search query is required.',
            ], 400);
        }

        $skills = Skill::with('category')
            ->where('is_active', true)
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            })
            ->orderBy('talents_count', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($skill) {
                return [
                    'id' => $skill->id,
                    'name' => $skill->name,
                    'slug' => $skill->slug,
                    'icon' => $skill->icon,
                    'description' => $skill->description,
                    'category' => $skill->category ? [
                        'id' => $skill->category->id,
                        'name' => $skill->category->name,
                    ] : null,
                    'talents_count' => $skill->talents_count,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $skills,
            'query' => $query,
        ]);
    }

    /**
     * Get featured/popular skills
     * GET /api/v1/public/skills/featured
     */
    public function featured(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 10);
        
        $skills = Skill::with('category')
            ->where('is_active', true)
            ->where(function($q) {
                $q->where('is_featured', true)
                  ->orWhere('talents_count', '>', 0);
            })
            ->orderBy('is_featured', 'desc')
            ->orderBy('talents_count', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($skill) {
                return [
                    'id' => $skill->id,
                    'name' => $skill->name,
                    'slug' => $skill->slug,
                    'icon' => $skill->icon,
                    'description' => $skill->description,
                    'category' => $skill->category ? [
                        'id' => $skill->category->id,
                        'name' => $skill->category->name,
                    ] : null,
                    'is_featured' => $skill->is_featured,
                    'talents_count' => $skill->talents_count,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $skills,
        ]);
    }

    /**
     * Get skill statistics
     * GET /api/v1/public/skills/stats
     */
    public function stats(): JsonResponse
    {
        $stats = [
            'total_skills' => Skill::where('is_active', true)->count(),
            'featured_skills' => Skill::where('is_active', true)->where('is_featured', true)->count(),
            'total_categories' => Skill::where('is_active', true)->distinct('category_id')->count('category_id'),
            'most_popular' => Skill::where('is_active', true)
                ->orderBy('talents_count', 'desc')
                ->limit(5)
                ->get(['id', 'name', 'icon', 'talents_count']),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}