<?php

namespace App\Http\Controllers\Api\Talent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ExperiencesController extends Controller
{
    /**
     * Get all experiences
     */
    public function index(Request $request)
    {
        $experiences = $request->user()
            ->experiences()
            ->orderByDesc('is_current')
            ->orderByDesc('start_date')
            ->get();

        return response()->json([
            'experiences' => $experiences,
        ]);
    }

    /**
     * Create new experience
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'project_name' => 'nullable|string|max:255',
            'category_id' => 'nullable|uuid|exists:categories,id',
            'employment_type' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'is_current' => 'boolean',
            'description' => 'nullable|string',
            'skills_used' => 'nullable|array',
            'achievements' => 'nullable|array',
            'company_website' => 'nullable|url|max:255',
            'compensation' => 'nullable|numeric|min:0',
            'compensation_type' => 'nullable|string|max:255',
            'media_attachments' => 'nullable|array',
            'is_featured' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Get the user's talent profile ID
        $talentProfileId = $request->user()->talentProfile->id;

        // If is_current is true, end_date should be null
        $data = $request->only([
            'title', 'company_name', 'project_name', 'category_id', 'employment_type',
            'location', 'start_date', 'end_date', 'is_current', 'description',
            'skills_used', 'achievements', 'company_website', 'compensation',
            'compensation_type', 'media_attachments', 'is_featured'
        ]);

        if ($request->is_current) {
            $data['end_date'] = null;
        }

        // Add talent_profile_id
        $data['talent_profile_id'] = $talentProfileId;

        $experience = $request->user()->experiences()->create($data);

        return response()->json([
            'message' => 'Experience added successfully',
            'experience' => $experience,
        ], 201);
    }

    /**
     * Get single experience
     */
    public function show(Request $request, $id)
    {
        $experience = $request->user()
            ->experiences()
            ->where('id', $id)
            ->first();

        if (!$experience) {
            return response()->json([
                'message' => 'Experience not found',
            ], 404);
        }

        return response()->json([
            'experience' => $experience,
        ]);
    }

    /**
     * Update experience
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'project_name' => 'nullable|string|max:255',
            'category_id' => 'nullable|uuid|exists:categories,id',
            'employment_type' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'start_date' => 'sometimes|date',
            'end_date' => 'nullable|date|after:start_date',
            'is_current' => 'boolean',
            'description' => 'nullable|string',
            'skills_used' => 'nullable|array',
            'achievements' => 'nullable|array',
            'company_website' => 'nullable|url|max:255',
            'compensation' => 'nullable|numeric|min:0',
            'compensation_type' => 'nullable|string|max:255',
            'media_attachments' => 'nullable|array',
            'is_featured' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $experience = $request->user()
            ->experiences()
            ->where('id', $id)
            ->first();

        if (!$experience) {
            return response()->json([
                'message' => 'Experience not found',
            ], 404);
        }

        // If is_current is true, end_date should be null
        $data = $request->only([
            'title', 'company_name', 'project_name', 'category_id', 'employment_type',
            'location', 'start_date', 'end_date', 'is_current', 'description',
            'skills_used', 'achievements', 'company_website', 'compensation',
            'compensation_type', 'media_attachments', 'is_featured'
        ]);

        if ($request->has('is_current') && $request->is_current) {
            $data['end_date'] = null;
        }

        $experience->update($data);

        return response()->json([
            'message' => 'Experience updated successfully',
            'experience' => $experience->fresh(),
        ]);
    }

    /**
     * Delete experience
     */
    public function destroy(Request $request, $id)
    {
        $experience = $request->user()
            ->experiences()
            ->where('id', $id)
            ->first();

        if (!$experience) {
            return response()->json([
                'message' => 'Experience not found',
            ], 404);
        }

        $experience->delete();

        return response()->json([
            'message' => 'Experience deleted successfully',
        ]);
    }
}