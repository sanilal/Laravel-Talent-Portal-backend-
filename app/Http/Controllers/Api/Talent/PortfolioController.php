<?php

namespace App\Http\Controllers\Api\Talent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

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
            ->orderByDesc('completed_at')
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
            'description' => 'required|string|max:2000',
            'project_url' => 'nullable|url|max:500',
            'repository_url' => 'nullable|url|max:500',
            'technologies' => 'nullable|array',
            'technologies.*' => 'string|max:100',
            'completed_at' => 'nullable|date',
            'is_featured' => 'boolean',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $portfolioData = $request->only([
            'title', 'description', 'project_url', 'repository_url', 
            'technologies', 'completed_at', 'is_featured'
        ]);
        
        // Handle image uploads
        if ($request->hasFile('images')) {
            $imageUrls = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('portfolios', 'public');
                $imageUrls[] = $path;
            }
            $portfolioData['images'] = $imageUrls;
        }

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
            'description' => 'sometimes|string|max:2000',
            'project_url' => 'nullable|url|max:500',
            'repository_url' => 'nullable|url|max:500',
            'technologies' => 'nullable|array',
            'technologies.*' => 'string|max:100',
            'completed_at' => 'nullable|date',
            'is_featured' => 'boolean',
            'new_images' => 'nullable|array',
            'new_images.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
            'remove_images' => 'nullable|array',
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
            'title', 'description', 'project_url', 'repository_url', 
            'technologies', 'completed_at', 'is_featured'
        ]);
        
        // Handle image removals
        if ($request->has('remove_images')) {
            $currentImages = $portfolio->images ?? [];
            foreach ($request->remove_images as $imageToRemove) {
                if (in_array($imageToRemove, $currentImages)) {
                    Storage::disk('public')->delete($imageToRemove);
                    $currentImages = array_values(array_diff($currentImages, [$imageToRemove]));
                }
            }
            $updateData['images'] = $currentImages;
        }

        // Handle new image uploads
        if ($request->hasFile('new_images')) {
            $currentImages = $portfolio->images ?? [];
            foreach ($request->file('new_images') as $image) {
                $path = $image->store('portfolios', 'public');
                $currentImages[] = $path;
            }
            $updateData['images'] = $currentImages;
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

        // Delete associated images
        if ($portfolio->images) {
            foreach ($portfolio->images as $image) {
                Storage::disk('public')->delete($image);
            }
        }

        $portfolio->delete();

        return response()->json([
            'message' => 'Portfolio deleted successfully',
        ]);
    }
}