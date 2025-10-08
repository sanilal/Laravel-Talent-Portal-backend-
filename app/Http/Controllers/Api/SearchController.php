<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SearchService;
use App\Models\TalentProfile;
use App\Models\Project;
use App\Models\Portfolio;
use App\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class SearchController extends Controller
{
    public function __construct(
        private SearchService $searchService
    ) {}

    /**
     * Search talents using natural language query
     * 
     * POST /api/v1/search/talents
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function searchTalents(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:2|max:500',
            'limit' => 'nullable|integer|min:1|max:100',
            'min_similarity' => 'nullable|numeric|min:0|max:1',
            'filters' => 'nullable|array',
            'filters.experience_level' => 'nullable|in:junior,mid,senior,expert',
            'filters.hourly_rate_max' => 'nullable|numeric|min:0',
            'filters.availability' => 'nullable|boolean',
            'filters.category_id' => 'nullable|uuid|exists:categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $results = $this->searchService->searchTalents(
                query: $request->input('query'),
                filters: $request->input('filters', []),
                limit: $request->input('limit', 20),
                minSimilarity: $request->input('min_similarity', 0.5)
            );

            return response()->json([
                'success' => true,
                'data' => $results
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred'
            ], 500);
        }
    }

    /**
     * Find best matching talents for a project
     * 
     * POST /api/v1/projects/{project}/match-talents
     * 
     * @param Request $request
     * @param string $projectId
     * @return JsonResponse
     */
    public function matchTalentsToProject(Request $request, string $projectId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'nullable|integer|min:1|max:100',
            'min_similarity' => 'nullable|numeric|min:0|max:1',
            'filters' => 'nullable|array',
            'filters.availability' => 'nullable|boolean',
            'filters.max_hourly_rate' => 'nullable|numeric|min:0',
            'filters.experience_level' => 'nullable|in:junior,mid,senior,expert',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Find project
        $project = Project::find($projectId);

        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => 'Project not found'
            ], 404);
        }

        // Check authorization (only project owner or admin)
        $user = $request->user();
        if ($project->recruiter_id !== $user->id && $user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this project'
            ], 403);
        }

        try {
            $results = $this->searchService->matchTalentsToProject(
                project: $project,
                filters: $request->input('filters', []),
                limit: $request->input('limit', 20),
                minSimilarity: $request->input('min_similarity', 0.6)
            );

            return response()->json([
                'success' => true,
                'data' => $results
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Matching failed',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred'
            ], 500);
        }
    }

    /**
     * Find suitable projects for a talent
     * 
     * POST /api/v1/talents/{talent}/match-projects
     * 
     * @param Request $request
     * @param string $talentId
     * @return JsonResponse
     */
    public function matchProjectsToTalent(Request $request, string $talentId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'nullable|integer|min:1|max:100',
            'min_similarity' => 'nullable|numeric|min:0|max:1',
            'filters' => 'nullable|array',
            'filters.project_type' => 'nullable|in:full-time,part-time,contract,freelance',
            'filters.work_type' => 'nullable|in:remote,onsite,hybrid',
            'filters.budget_max' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Find talent profile
        $talent = TalentProfile::find($talentId);

        if (!$talent) {
            return response()->json([
                'success' => false,
                'message' => 'Talent profile not found'
            ], 404);
        }

        // Check authorization (only talent owner or admin)
        $user = $request->user();
        if ($talent->user_id !== $user->id && $user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this talent profile'
            ], 403);
        }

        try {
            $results = $this->searchService->matchProjectsToTalent(
                talent: $talent,
                filters: $request->input('filters', []),
                limit: $request->input('limit', 20),
                minSimilarity: $request->input('min_similarity', 0.6)
            );

            return response()->json([
                'success' => true,
                'data' => $results
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Matching failed',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred'
            ], 500);
        }
    }

    /**
     * Find portfolios similar to a given portfolio
     * 
     * GET /api/v1/portfolios/{portfolio}/similar
     * 
     * @param Request $request
     * @param string $portfolioId
     * @return JsonResponse
     */
    public function similarPortfolios(Request $request, string $portfolioId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'nullable|integer|min:1|max:50',
            'min_similarity' => 'nullable|numeric|min:0|max:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Find portfolio
        $portfolio = Portfolio::find($portfolioId);

        if (!$portfolio) {
            return response()->json([
                'success' => false,
                'message' => 'Portfolio not found'
            ], 404);
        }

        try {
            $results = $this->searchService->findSimilarPortfolios(
                sourcePortfolio: $portfolio,
                limit: $request->input('limit', 10),
                minSimilarity: $request->input('min_similarity', 0.6)
            );

            return response()->json([
                'success' => true,
                'data' => $results
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred'
            ], 500);
        }
    }

    /**
     * Find skills related to a given skill
     * 
     * GET /api/v1/skills/{skill}/related
     * 
     * @param Request $request
     * @param string $skillId
     * @return JsonResponse
     */
    public function relatedSkills(Request $request, string $skillId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'nullable|integer|min:1|max:50',
            'min_similarity' => 'nullable|numeric|min:0|max:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Find skill
        $skill = Skill::find($skillId);

        if (!$skill) {
            return response()->json([
                'success' => false,
                'message' => 'Skill not found'
            ], 404);
        }

        try {
            $results = $this->searchService->findRelatedSkills(
                sourceSkill: $skill,
                limit: $request->input('limit', 15),
                minSimilarity: $request->input('min_similarity', 0.7)
            );

            return response()->json([
                'success' => true,
                'data' => $results
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred'
            ], 500);
        }
    }

    /**
     * Generate personalized project recommendations for a talent
     * 
     * GET /api/v1/talents/{talent}/recommendations
     * 
     * @param Request $request
     * @param string $talentId
     * @return JsonResponse
     */
    public function recommendations(Request $request, string $talentId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Find talent profile
        $talent = TalentProfile::find($talentId);

        if (!$talent) {
            return response()->json([
                'success' => false,
                'message' => 'Talent profile not found'
            ], 404);
        }

        // Check authorization (only talent owner or admin)
        $user = $request->user();
        if ($talent->user_id !== $user->id && $user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this talent profile'
            ], 403);
        }

        try {
            $results = $this->searchService->generateRecommendations(
                talent: $talent,
                limit: $request->input('limit', 10)
            );

            return response()->json([
                'success' => true,
                'data' => $results
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Recommendations failed',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred'
            ], 500);
        }
    }
}