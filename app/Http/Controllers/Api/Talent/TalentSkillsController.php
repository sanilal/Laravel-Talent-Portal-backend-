<?php

namespace App\Http\Controllers\Api\Talent;

use App\Http\Controllers\Controller;
use App\Models\Skill;
use App\Models\TalentSkill;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TalentSkillsController extends Controller
{
    /**
     * Get all skills for the authenticated talent.
     */
    public function index(Request $request): JsonResponse
    {
        // Get the talent profile first
        $talentProfile = $request->user()->talentProfile;

        if (!$talentProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Talent profile not found.',
            ], 404);
        }

        $skills = TalentSkill::where('talent_profile_id', $talentProfile->id)
            ->with('skill.category')
            ->orderBy('display_order', 'asc')
            ->orderBy('is_primary', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $skills,
        ]);
    }

    /**
     * Add a new skill to the talent's profile.
     */
    public function store(Request $request): JsonResponse
    {
        // Get the talent profile
        $talentProfile = $request->user()->talentProfile;

        if (!$talentProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Talent profile not found. Please create a talent profile first.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'skill_id' => 'required|uuid|exists:skills,id',
            'description' => 'nullable|string|max:5000',
            'proficiency_level' => 'required|in:beginner,intermediate,advanced,expert',
            'years_of_experience' => 'nullable|integer|min:0|max:100',
            'certifications' => 'nullable|array',
            'certifications.*' => 'string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB
            'video_url' => 'nullable|url|max:500',
            'is_primary' => 'boolean',
            'is_verified' => 'boolean',
            'display_order' => 'nullable|integer',
            'show_on_profile' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Check if skill already exists for this talent
        $existingSkill = TalentSkill::where('talent_profile_id', $talentProfile->id)
            ->where('skill_id', $request->skill_id)
            ->first();

        if ($existingSkill) {
            return response()->json([
                'success' => false,
                'message' => 'You have already added this skill to your profile.',
            ], 409);
        }

        // If this is set as primary, unset other primary skills
        if ($request->is_primary) {
            TalentSkill::where('talent_profile_id', $talentProfile->id)
                ->update(['is_primary' => false]);
        }

        // Handle image upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('skills', 'public');
        }

        // Get the next display order if not provided
        $displayOrder = $request->display_order;
        if ($displayOrder === null) {
            $maxOrder = TalentSkill::where('talent_profile_id', $talentProfile->id)
                ->max('display_order');
            $displayOrder = ($maxOrder ?? -1) + 1;
        }

        // Create the talent skill
        $talentSkill = TalentSkill::create([
            'talent_profile_id' => $talentProfile->id,
            'skill_id' => $request->skill_id,
            'description' => $request->description,
            'proficiency_level' => $request->proficiency_level,
            'years_of_experience' => $request->years_of_experience,
            'certifications' => $request->certifications ? json_encode($request->certifications) : null,
            'image_path' => $imagePath,
            'video_url' => $request->video_url,
            'is_primary' => $request->is_primary ?? false,
            'is_verified' => $request->is_verified ?? false,
            'display_order' => $displayOrder,
            'show_on_profile' => $request->show_on_profile ?? true,
        ]);

        // Load the skill relationship
        $talentSkill->load('skill.category');

        // Update skill count
        $skill = Skill::find($request->skill_id);
        if ($skill) {
            $skill->updateTalentsCount();
        }

        return response()->json([
            'success' => true,
            'message' => 'Skill added successfully.',
            'data' => $talentSkill,
        ], 201);
    }

    /**
     * Show a specific talent skill.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $talentProfile = $request->user()->talentProfile;

        if (!$talentProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Talent profile not found.',
            ], 404);
        }

        $talentSkill = TalentSkill::where('talent_profile_id', $talentProfile->id)
            ->where('id', $id)
            ->with('skill.category')
            ->first();

        if (!$talentSkill) {
            return response()->json([
                'success' => false,
                'message' => 'Skill not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $talentSkill,
        ]);
    }

    /**
     * Update an existing talent skill.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $talentProfile = $request->user()->talentProfile;

        if (!$talentProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Talent profile not found.',
            ], 404);
        }

        $talentSkill = TalentSkill::where('talent_profile_id', $talentProfile->id)
            ->where('id', $id)
            ->first();

        if (!$talentSkill) {
            return response()->json([
                'success' => false,
                'message' => 'Skill not found.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'description' => 'nullable|string|max:5000',
            'proficiency_level' => 'sometimes|in:beginner,intermediate,advanced,expert',
            'years_of_experience' => 'nullable|integer|min:0|max:100',
            'certifications' => 'nullable|array',
            'certifications.*' => 'string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'video_url' => 'nullable|url|max:500',
            'is_primary' => 'boolean',
            'is_verified' => 'boolean',
            'display_order' => 'nullable|integer',
            'show_on_profile' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // If this is set as primary, unset other primary skills
        if ($request->has('is_primary') && $request->is_primary) {
            TalentSkill::where('talent_profile_id', $talentProfile->id)
                ->where('id', '!=', $talentSkill->id)
                ->update(['is_primary' => false]);
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($talentSkill->image_path) {
                Storage::disk('public')->delete($talentSkill->image_path);
            }
            
            $imagePath = $request->file('image')->store('skills', 'public');
            $talentSkill->image_path = $imagePath;
        }

        // Handle certifications
        if ($request->has('certifications')) {
            $talentSkill->certifications = $request->certifications ? json_encode($request->certifications) : null;
        }

        // Update other fields
        $updateData = $request->except(['image', 'skill_id', 'talent_profile_id', 'certifications']);
        $talentSkill->update($updateData);
        
        // Reload the relationship
        $talentSkill->load('skill.category');

        return response()->json([
            'success' => true,
            'message' => 'Skill updated successfully.',
            'data' => $talentSkill,
        ]);
    }

    /**
     * Delete a talent skill.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $talentProfile = $request->user()->talentProfile;

        if (!$talentProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Talent profile not found.',
            ], 404);
        }

        $talentSkill = TalentSkill::where('talent_profile_id', $talentProfile->id)
            ->where('id', $id)
            ->first();

        if (!$talentSkill) {
            return response()->json([
                'success' => false,
                'message' => 'Skill not found.',
            ], 404);
        }

        $skillId = $talentSkill->skill_id;

        // Delete associated image
        if ($talentSkill->image_path) {
            Storage::disk('public')->delete($talentSkill->image_path);
        }

        $talentSkill->delete();

        // Update skill count
        $skill = Skill::find($skillId);
        if ($skill) {
            $skill->updateTalentsCount();
        }

        return response()->json([
            'success' => true,
            'message' => 'Skill removed successfully.',
        ]);
    }

    /**
     * Reorder talent skills.
     */
    public function reorder(Request $request): JsonResponse
    {
        $talentProfile = $request->user()->talentProfile;

        if (!$talentProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Talent profile not found.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'skills' => 'required|array',
            'skills.*.id' => 'required|uuid|exists:talent_skills,id',
            'skills.*.display_order' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        foreach ($request->skills as $skillData) {
            TalentSkill::where('id', $skillData['id'])
                ->where('talent_profile_id', $talentProfile->id)
                ->update(['display_order' => $skillData['display_order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Skills reordered successfully.',
        ]);
    }

    /**
     * Set a skill as primary.
     */
    public function setPrimary(Request $request, string $id): JsonResponse
    {
        $talentProfile = $request->user()->talentProfile;

        if (!$talentProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Talent profile not found.',
            ], 404);
        }

        $talentSkill = TalentSkill::where('talent_profile_id', $talentProfile->id)
            ->where('id', $id)
            ->first();

        if (!$talentSkill) {
            return response()->json([
                'success' => false,
                'message' => 'Skill not found.',
            ], 404);
        }

        // Unset all primary skills
        TalentSkill::where('talent_profile_id', $talentProfile->id)
            ->update(['is_primary' => false]);

        // Set this skill as primary
        $talentSkill->update(['is_primary' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Primary skill updated successfully.',
            'data' => $talentSkill->load('skill.category'),
        ]);
    }
}