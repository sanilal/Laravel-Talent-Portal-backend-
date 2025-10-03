<?php

namespace App\Http\Controllers\Api\Talent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PortfolioController extends Controller
{
    /**
     * Get all portfolios
     */
    public function index(Request $request)
    {
        $portfolios = $request->user()
            ->portfolios()
            ->orderByDesc('is_featured')
            ->orderByDesc('completion_date')
            ->get();

        return response()->json([
            'portfolios' => $portfolios,
        ]);
    }

    /**
     * Create new portfolio
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'nullable|uuid|exists:categories,id',
            'project_type' => 'nullable|string|max:255',
            'skills_demonstrated' => 'nullable|array',
            'project_url' => 'nullable|url|max:500',
            'external_url' => 'nullable|url|max:500',
            'completion_date' => 'nullable|date',
            'client_name' => 'nullable|string|max:255',
            'director_name' => 'nullable|string|max:255',
            'role_description' => 'nullable|string',
            'challenges_faced' => 'nullable|string',
            'collaborators' => 'nullable|array',
            'awards' => 'nullable|array',
            'is_featured' => 'boolean',
            'is_public' => 'boolean',
            'is_demo_reel' => 'boolean',
            'metadata' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Get the user's talent profile ID
        $talentProfileId = $request->user()->talentProfile->id;

        $portfolioData = $request->only([
            'title', 'description', 'category_id', 'project_type', 'skills_demonstrated',
            'project_url', 'external_url', 'completion_date', 'client_name', 'director_name',
            'role_description', 'challenges_faced', 'collaborators', 'awards',
            'is_featured', 'is_public', 'is_demo_reel', 'metadata'
        ]);

        // Add talent_profile_id and generate slug
        $portfolioData['talent_profile_id'] = $talentProfileId;
        $portfolioData['slug'] = Str::slug($request->title) . '-' . Str::random(6);

        $portfolio = $request->user()->portfolios()->create($portfolioData);

        return response()->json([
            'message' => 'Portfolio added successfully',
            'portfolio' => $portfolio,
        ], 201);
    }

    /**
     * Get single portfolio
     */
    public function show(Request $request, $id)
    {
        $portfolio = $request->user()
            ->portfolios()
            ->where('id', $id)
            ->first();

        if (!$portfolio) {
            return response()->json([
                'message' => 'Portfolio not found',
            ], 404);
        }

        // Increment views count
        $portfolio->increment('views_count');

        return response()->json([
            'portfolio' => $portfolio,
        ]);
    }

    /**
     * Update portfolio
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'category_id' => 'nullable|uuid|exists:categories,id',
            'project_type' => 'nullable|string|max:255',
            'skills_demonstrated' => 'nullable|array',
            'project_url' => 'nullable|url|max:500',
            'external_url' => 'nullable|url|max:500',
            'completion_date' => 'nullable|date',
            'client_name' => 'nullable|string|max:255',
            'director_name' => 'nullable|string|max:255',
            'role_description' => 'nullable|string',
            'challenges_faced' => 'nullable|string',
            'collaborators' => 'nullable|array',
            'awards' => 'nullable|array',
            'is_featured' => 'boolean',
            'is_public' => 'boolean',
            'is_demo_reel' => 'boolean',
            'metadata' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $portfolio = $request->user()
            ->portfolios()
            ->where('id', $id)
            ->first();

        if (!$portfolio) {
            return response()->json([
                'message' => 'Portfolio not found',
            ], 404);
        }

        $updateData = $request->only([
            'title', 'description', 'category_id', 'project_type', 'skills_demonstrated',
            'project_url', 'external_url', 'completion_date', 'client_name', 'director_name',
            'role_description', 'challenges_faced', 'collaborators', 'awards',
            'is_featured', 'is_public', 'is_demo_reel', 'metadata'
        ]);

        // Update slug if title changed
        if ($request->has('title')) {
            $updateData['slug'] = Str::slug($request->title) . '-' . Str::random(6);
        }

        $portfolio->update($updateData);

        return response()->json([
            'message' => 'Portfolio updated successfully',
            'portfolio' => $portfolio->fresh(),
        ]);
    }

    /**
     * Delete portfolio
     */
    public function destroy(Request $request, $id)
    {
        $portfolio = $request->user()
            ->portfolios()
            ->where('id', $id)
            ->first();

        if (!$portfolio) {
            return response()->json([
                'message' => 'Portfolio not found',
            ], 404);
        }

        $portfolio->delete();

        return response()->json([
            'message' => 'Portfolio deleted successfully',
        ]);
    }
}