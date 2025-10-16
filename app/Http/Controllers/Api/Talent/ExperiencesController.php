<?php

namespace App\Http\Controllers\Api\Talent;

use App\Http\Controllers\Controller;
use App\Models\Experience;
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

        return response()->json($experiences);
    }

    /**
     * Create new experience
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'company' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'is_current' => 'boolean',
            'description' => 'nullable|string',
            'employment_type' => 'nullable|string|in:full_time,part_time,contract,freelance,internship',
            'skills' => 'nullable|array',
            'achievements' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Get talent_profile_id if exists
        $data = $request->only([
            'title', 'company', 'location', 'start_date', 'end_date',
            'is_current', 'description', 'employment_type', 'skills', 'achievements'
        ]);

        // If current position, clear end date
        if ($request->is_current) {
            $data['end_date'] = null;
        }

        // Add talent_profile_id if user has talent profile
        if ($request->user()->talentProfile) {
            $data['talent_profile_id'] = $request->user()->talentProfile->id;
        }

        // Add user_id for direct relationship
        $data['user_id'] = $request->user()->id;

        $experience = Experience::create($data);

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
            'company' => 'sometimes|string|max:255',
            'location' => 'nullable|string|max:255',
            'start_date' => 'sometimes|date',
            'end_date' => 'nullable|date|after:start_date',
            'is_current' => 'boolean',
            'description' => 'nullable|string',
            'employment_type' => 'nullable|string|in:full_time,part_time,contract,freelance,internship',
            'skills' => 'nullable|array',
            'achievements' => 'nullable|array',
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

        $data = $request->only([
            'title', 'company', 'location', 'start_date', 'end_date',
            'is_current', 'description', 'employment_type', 'skills', 'achievements'
        ]);

        // If current position, clear end date
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