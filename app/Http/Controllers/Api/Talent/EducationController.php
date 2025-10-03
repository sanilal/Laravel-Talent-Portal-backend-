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
            'institution' => 'required|string|max:255',
            'degree' => 'required|string|max:255',
            'field_of_study' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'is_current' => 'boolean',
            'grade' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:2000',
            'activities' => 'nullable|string|max:1000',
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

        $education = $request->user()->education()->create($request->all());

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
            'institution' => 'sometimes|string|max:255',
            'degree' => 'sometimes|string|max:255',
            'field_of_study' => 'sometimes|string|max:255',
            'start_date' => 'sometimes|date',
            'end_date' => 'nullable|date|after:start_date',
            'is_current' => 'boolean',
            'grade' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:2000',
            'activities' => 'nullable|string|max:1000',
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
        if ($request->has('is_current') && $request->is_current) {
            $request->merge(['end_date' => null]);
        }

        $education->update($request->all());

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