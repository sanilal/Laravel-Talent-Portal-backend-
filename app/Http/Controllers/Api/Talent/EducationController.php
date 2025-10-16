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

        // Map fields for frontend compatibility
        $education = $education->map(function($item) {
            $item->institution = $item->institution_name; // Add alias
            return $item;
        });

        return response()->json($education);
    }

    /**
     * Create new education record
     */
    public function store(Request $request)
    {
        // Accept both 'institution' and 'institution_name' for compatibility
        $institutionName = $request->institution ?? $request->institution_name;

        $validator = Validator::make(array_merge($request->all(), [
            'institution_name' => $institutionName
        ]), [
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

        $data = [
            'institution_name' => $institutionName,
            'degree' => $request->degree,
            'field_of_study' => $request->field_of_study,
            'start_date' => $request->start_date,
            'end_date' => $request->is_current ? null : $request->end_date,
            'is_current' => $request->is_current ?? false,
            'grade' => $request->grade,
            'description' => $request->description,
            'activities' => $request->activities,
            'certifications' => $request->certifications,
            'institution_website' => $request->institution_website,
            'attachments' => $request->attachments,
            'talent_profile_id' => $talentProfileId,
        ];

        $education = $request->user()->education()->create($data);

        // Add alias for frontend
        $education->institution = $education->institution_name;

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

        // Add alias
        $education->institution = $education->institution_name;

        return response()->json([
            'education' => $education,
        ]);
    }

    /**
     * Update education record
     */
    public function update(Request $request, $id)
    {
        // Accept both 'institution' and 'institution_name'
        $institutionName = $request->institution ?? $request->institution_name;

        $validator = Validator::make(array_merge($request->all(), [
            'institution_name' => $institutionName
        ]), [
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

        $data = [];
        if ($institutionName) $data['institution_name'] = $institutionName;
        if ($request->has('degree')) $data['degree'] = $request->degree;
        if ($request->has('field_of_study')) $data['field_of_study'] = $request->field_of_study;
        if ($request->has('start_date')) $data['start_date'] = $request->start_date;
        if ($request->has('end_date')) $data['end_date'] = $request->end_date;
        if ($request->has('is_current')) {
            $data['is_current'] = $request->is_current;
            if ($request->is_current) {
                $data['end_date'] = null;
            }
        }
        if ($request->has('grade')) $data['grade'] = $request->grade;
        if ($request->has('description')) $data['description'] = $request->description;
        if ($request->has('activities')) $data['activities'] = $request->activities;
        if ($request->has('certifications')) $data['certifications'] = $request->certifications;
        if ($request->has('institution_website')) $data['institution_website'] = $request->institution_website;
        if ($request->has('attachments')) $data['attachments'] = $request->attachments;

        $education->update($data);

        // Add alias
        $education = $education->fresh();
        $education->institution = $education->institution_name;

        return response()->json([
            'message' => 'Education updated successfully',
            'education' => $education,
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