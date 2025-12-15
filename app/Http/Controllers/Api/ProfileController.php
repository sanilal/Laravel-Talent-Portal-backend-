<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TalentProfile;
use App\Models\User;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Get profiles with advanced filtering
     * GET /api/v1/public/profiles
     * Compatible with yourmoca.com/api/getProfiles
     * 
     * Query Parameters:
     * - categoryId: Filter by category
     * - subcategoryId: Filter by subcategory
     * - country: Filter by country
     * - state: Filter by state
     * - budgetMin: Minimum budget
     * - budgetMax: Maximum budget
     * - height: Filter by height
     * - weight: Filter by weight range
     * - skinTone: Filter by skin tone
     * - ageMin: Minimum age
     * - ageMax: Maximum age
     * - gender: Filter by gender
     * - page: Page number (default: 1)
     * - limit: Results per page (default: 15)
     * - sortBy: Sort field (default: created_at)
     * - sortOrder: asc or desc (default: desc)
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'categoryId' => 'nullable|uuid|exists:categories,id',
            'subcategoryId' => 'nullable|uuid|exists:subcategories,id',
            'country' => 'nullable|integer|exists:countries,id',
            'state' => 'nullable|integer|exists:states,id',
            'budgetMin' => 'nullable|numeric|min:0',
            'budgetMax' => 'nullable|numeric|min:0',
            'height' => 'nullable|string',
            'weight' => 'nullable|string',
            'skinTone' => 'nullable|string',
            'ageMin' => 'nullable|integer|min:0|max:100',
            'ageMax' => 'nullable|integer|min:0|max:100',
            'gender' => 'nullable|string|in:male,female,other,prefer_not_to_say',
            'page' => 'nullable|integer|min:1',
            'limit' => 'nullable|integer|min:1|max:50',
            'sortBy' => 'nullable|string|in:created_at,profile_views,average_rating',
            'sortOrder' => 'nullable|string|in:asc,desc',
        ]);

        $page = $request->input('page', 1);
        $limit = $request->input('limit', 15);
        $sortBy = $request->input('sortBy', 'created_at');
        $sortOrder = $request->input('sortOrder', 'desc');

        // Build query
        $query = TalentProfile::with([
            'user',
            'primaryCategory',
            'subcategory',
            'user.country',
            'user.state'
        ])
            ->where('is_public', true)
            ->whereHas('user', function ($q) {
                $q->where('account_status', 'active');
            });

        // Apply filters
        if ($request->filled('categoryId')) {
            $query->where('primary_category_id', $request->categoryId);
        }

        if ($request->filled('subcategoryId')) {
            $query->where('subcategory_id', $request->subcategoryId);
        }

        if ($request->filled('country')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('country_id', $request->country);
            });
        }

        if ($request->filled('state')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('state_id', $request->state);
            });
        }

        if ($request->filled('budgetMin')) {
            $query->where('hourly_rate_min', '>=', $request->budgetMin);
        }

        if ($request->filled('budgetMax')) {
            $query->where('hourly_rate_max', '<=', $request->budgetMax);
        }

        if ($request->filled('height')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('height', $request->height);
            });
        }

        if ($request->filled('weight')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('weight', $request->weight);
            });
        }

        if ($request->filled('gender')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('gender', $request->gender);
            });
        }

        if ($request->filled('ageMin') || $request->filled('ageMax')) {
            $query->whereHas('user', function ($q) use ($request) {
                if ($request->filled('ageMin')) {
                    $minDate = now()->subYears($request->ageMax ?? 100)->format('Y-m-d');
                    $q->where('date_of_birth', '<=', $minDate);
                }
                if ($request->filled('ageMax')) {
                    $maxDate = now()->subYears($request->ageMin ?? 0)->format('Y-m-d');
                    $q->where('date_of_birth', '>=', $maxDate);
                }
            });
        }

        // Get total count before pagination
        $totalCount = $query->count();

        // Apply sorting and pagination
        $profiles = $query->orderBy($sortBy, $sortOrder)
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        // Format response compatible with competitor structure
        $formattedProfiles = $profiles->map(function ($profile) {
            $user = $profile->user;
            
            // Calculate age from date_of_birth
            $age = $user->date_of_birth 
                ? \Carbon\Carbon::parse($user->date_of_birth)->age 
                : 0;

            return [
                'userDetails' => [
                    'id' => $user->id,
                    'coverImage' => $user->cover_image,
                    'profileImage' => $user->avatar,
                    'firstName' => $user->first_name,
                    'lastName' => $user->last_name,
                    'company' => $user->first_name . ' ' . $user->last_name,
                    'description' => $user->bio,
                    'state' => $user->state_id,
                    'country' => $user->country_id,
                    'age' => $age,
                    'celebrityBadge' => 0, // Can be implemented later
                ],
                'categoryId' => $profile->primary_category_id,
                'subCategoryId' => $profile->subcategory_id,
                'tagName' => $profile->subcategory ? $profile->subcategory->name : null,
                'rateStartsFrom' => $profile->hourly_rate_min 
                    ? number_format($profile->hourly_rate_min, 2) 
                    : 'NA',
                'isFavourited' => 0, // Can be implemented with favorites system
                'reviews' => $profile->total_ratings,
                'ratings' => (string) $profile->average_rating,
            ];
        });

        // Get category and subcategory info if provided
        $profileCategory = null;
        $profileSubCategory = null;

        if ($request->filled('categoryId')) {
            $category = Category::find($request->categoryId);
            if ($category) {
                $profileCategory = [
                    'id' => $category->id,
                    'categoryName' => $category->name,
                    'orderType' => $category->sort_order,
                ];
            }
        }

        if ($request->filled('subcategoryId')) {
            $subcategory = Subcategory::find($request->subcategoryId);
            if ($subcategory) {
                $profileSubCategory = [
                    'id' => $subcategory->id,
                    'subcategoryName' => $subcategory->name,
                ];
            }
        }

        return response()->json([
            'message' => 'success',
            'status' => 1,
            'data' => [
                'type' => 1,
                'count' => $totalCount,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => ceil($totalCount / $limit),
                'profileCategory' => $profileCategory ?? new \stdClass(),
                'profilesSubCategory' => $profileSubCategory ?? new \stdClass(),
                'profilesSuperSubCategory' => new \stdClass(),
                'profiles' => $formattedProfiles,
            ],
        ]);
    }

    /**
     * Get a single profile by ID
     * GET /api/v1/public/profiles/{id}
     */
    public function show($id): JsonResponse
    {
        $profile = TalentProfile::with([
            'user',
            'primaryCategory',
            'subcategory',
            'user.country',
            'user.state',
            'experiences',
            'education',
            'portfolios',
            'talentSkills.skill'
        ])->findOrFail($id);

        $user = $profile->user;
        $age = $user->date_of_birth 
            ? \Carbon\Carbon::parse($user->date_of_birth)->age 
            : 0;

        return response()->json([
            'status' => 1,
            'message' => 'Profile retrieved successfully',
            'data' => [
                'id' => $profile->id,
                'userDetails' => [
                    'id' => $user->id,
                    'firstName' => $user->first_name,
                    'lastName' => $user->last_name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'profileImage' => $user->avatar,
                    'coverImage' => $user->cover_image,
                    'bio' => $user->bio,
                    'age' => $age,
                    'gender' => $user->gender,
                    'location' => $user->location,
                    'city' => $user->city,
                    'country' => $user->country ? $user->country->country_name : null,
                    'state' => $user->state ? $user->state->state_name : null,
                    'height' => $user->height,
                    'weight' => $user->weight,
                    'eyeColor' => $user->eye_color,
                    'hairColor' => $user->hair_color,
                ],
                'profileDetails' => [
                    'professionalTitle' => $profile->professional_title,
                    'summary' => $profile->summary,
                    'experienceLevel' => $profile->experience_level,
                    'hourlyRateMin' => $profile->hourly_rate_min,
                    'hourlyRateMax' => $profile->hourly_rate_max,
                    'currency' => $profile->currency,
                    'isAvailable' => $profile->is_available,
                    'profileViews' => $profile->profile_views,
                    'averageRating' => $profile->average_rating,
                    'totalRatings' => $profile->total_ratings,
                ],
                'category' => $profile->primaryCategory ? [
                    'id' => $profile->primaryCategory->id,
                    'name' => $profile->primaryCategory->name,
                ] : null,
                'subcategory' => $profile->subcategory ? [
                    'id' => $profile->subcategory->id,
                    'name' => $profile->subcategory->name,
                ] : null,
                'skills' => $profile->talentSkills->map(function ($ts) {
                    return [
                        'id' => $ts->skill->id,
                        'name' => $ts->skill->name,
                        'proficiencyLevel' => $ts->proficiency_level,
                        'yearsOfExperience' => $ts->years_of_experience,
                    ];
                }),
                'experiences' => $profile->experiences,
                'education' => $profile->education,
                'portfolios' => $profile->portfolios,
            ],
        ]);
    }
}