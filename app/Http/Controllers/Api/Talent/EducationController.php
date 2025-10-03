<?php

namespace App\Http\Controllers\Api\Talent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EducationController extends Controller
{
    /**
     * Get all education records
     */
    public function index(Request $request)
    {
        $education = $request->user()
            ->education()
            ->orderByDesc('is_current')
            ->orderByDesc('start_date')
            ->get();

        return response()->json([
            'education' => $education,
        ]);
    }

    /**
     * Create new education record
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'institution_name' => 'required|string|max:255',
            'degree' => 'required|string|max:255',
            'field_of_study' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'is_current' => 'boolean',
            'grade' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'activities' => 'nullable|array',
            'certifications' => 'nullable|array',
            'institution_website' => 'nullable|url|max:255',
            'attachments' => 'nullable|array',
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
            'institution_name', 'degree', 'field_of_study', 'start_date', 'end_date',
            'is_current', 'grade', 'description', 'activities', 'certifications',
            'institution_website', 'attachments'
        ]);

        if ($request->is_current) {
            $data['end_date'] = null;
        }

        // Add talent_profile_id
        $data['talent_profile_id'] = $talentProfileId;

        $education = $request->user()->education()->create($data);

        return response()->json([
            'message' => 'Education added successfully',
            'education' => $education,
        ], 201);
    }

    /**
     * Get single education record
     */
    public function show(Request $request, $id)
    {
        $education = $request->user()
            ->education()
            ->where('id', $id)
            ->first();

        if (!$education) {
            return response()->json([
                'message' => 'Education record not found',
            ], 404);
        }

        return response()->json([
            'education' => $education,
        ]);
    }

    /**
     * Update education record
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'institution_name' => 'sometimes|string|max:255',
            'degree' => 'sometimes|string|max:255',
            'field_of_study' => 'nullable|string|max:255',
            'start_date' => 'sometimes|date',
            'end_date' => 'nullable|date|after:start_date',
            'is_current' => 'boolean',
            'grade' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'activities' => 'nullable|array',
            'certifications' => 'nullable|array',
            'institution_website' => 'nullable|url|max:255',
            'attachments' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $education = $request->user()
            ->education()
            ->where('id', $id)
            ->first();

        if (!$education) {
            return response()->json([
                'message' => 'Education record not found',
            ], 404);
        }

        // If is_current is true, end_date should be null
        $data = $request->only([
            'institution_name', 'degree', 'field_of_study', 'start_date', 'end_date',
            'is_current', 'grade', 'description', 'activities', 'certifications',
            'institution_website', 'attachments'
        ]);

        if ($request->has('is_current') && $request->is_current) {
            $data['end_date'] = null;
        }

        $education->update($data);

        return response()->json([
            'message' => 'Education updated successfully',
            'education' => $education->fresh(),
        ]);
    }

    /**
     * Delete education record
     */
    public function destroy(Request $request, $id)
    {
        $education = $request->user()
            ->education()
            ->where('id', $id)
            ->first();

        if (!$education) {
            return response()->json([
                'message' => 'Education record not found',
            ], 404);
        }

        $education->delete();

        return response()->json([
            'message' => 'Education deleted successfully',
        ]);
    }
}