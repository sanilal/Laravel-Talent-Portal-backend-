<?php

namespace App\Http\Controllers\Api\Talent;

use App\Http\Controllers\Controller;
use App\Models\TalentSkill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TalentSkillsController extends Controller
{
    /**
     * Get all talent skills
     */
    public function index(Request $request)
    {
        $skills = $request->user()
            ->skills()
            ->with('skill.category')
            ->orderBy('proficiency_level', 'desc')
            ->orderBy('years_of_experience', 'desc')
            ->get();

        return response()->json([
            'skills' => $skills,
        ]);
    }

    /**
     * Add a skill
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'skill_id' => 'required|uuid|exists:skills,id',
            'proficiency_level' => 'required|in:beginner,intermediate,advanced,expert',
            'years_of_experience' => 'nullable|integer|min:0|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        // Check if skill already exists for this user
        $existingSkill = $user->skills()->where('skill_id', $request->skill_id)->first();

        if ($existingSkill) {
            return response()->json([
                'message' => 'This skill is already added to your profile',
            ], 409);
        }

        // Create talent skill
        $talentSkill = $user->skills()->create([
            'skill_id' => $request->skill_id,
            'proficiency_level' => $request->proficiency_level,
            'years_of_experience' => $request->years_of_experience,
        ]);

        return response()->json([
            'message' => 'Skill added successfully',
            'skill' => $talentSkill->load('skill'),
        ], 201);
    }

    /**
     * Update a skill
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'proficiency_level' => 'sometimes|in:beginner,intermediate,advanced,expert',
            'years_of_experience' => 'sometimes|nullable|integer|min:0|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $talentSkill = $request->user()
            ->skills()
            ->where('id', $id)
            ->first();

        if (!$talentSkill) {
            return response()->json([
                'message' => 'Skill not found',
            ], 404);
        }

        $talentSkill->update($request->only([
            'proficiency_level',
            'years_of_experience',
        ]));

        return response()->json([
            'message' => 'Skill updated successfully',
            'skill' => $talentSkill->fresh()->load('skill'),
        ]);
    }

    /**
     * Delete a skill
     */
    public function destroy(Request $request, $id)
    {
        $talentSkill = $request->user()
            ->skills()
            ->where('id', $id)
            ->first();

        if (!$talentSkill) {
            return response()->json([
                'message' => 'Skill not found',
            ], 404);
        }

        $talentSkill->delete();

        return response()->json([
            'message' => 'Skill removed successfully',
        ]);
    }
}