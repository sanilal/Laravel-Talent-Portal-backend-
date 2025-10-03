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
            'company' => 'required|string|max:255',
            'employment_type' => 'required|in:full_time,part_time,contract,freelance,internship',
            'location' => 'nullable|string|max:255',
            'location_type' => 'nullable|in:onsite,remote,hybrid',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'is_current' => 'boolean',
            'description' => 'nullable|string|max:2000',
            'skills_used' => 'nullable|array',
            'achievements' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // If is_current is true, end_date should be null
        if ($request->is_current) {
            $request->merge(['end_date' => null]);
        }

        $experience = $request->user()->experiences()->create($request->only([
    'title', 'company', 'employment_type', 'location', 'location_type',
    'start_date', 'end_date', 'is_current', 'description', 
    'skills_used', 'achievements'
]));

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
            'employment_type' => 'sometimes|in:full_time,part_time,contract,freelance,internship',
            'location' => 'nullable|string|max:255',
            'location_type' => 'nullable|in:onsite,remote,hybrid',
            'start_date' => 'sometimes|date',
            'end_date' => 'nullable|date|after:start_date',
            'is_current' => 'boolean',
            'description' => 'nullable|string|max:2000',
            'skills_used' => 'nullable|array',
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

        // If is_current is true, end_date should be null
        if ($request->has('is_current') && $request->is_current) {
            $request->merge(['end_date' => null]);
        }

        $experience->update($request->only([
            'title', 'company', 'employment_type', 'location', 'location_type',
            'start_date', 'end_date', 'is_current', 'description', 
            'skills_used', 'achievements'
        ]));

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