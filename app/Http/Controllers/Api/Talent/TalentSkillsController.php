<?php

namespace App\Http\Controllers\Api\Talent;

use App\Http\Controllers\Controller;
use App\Models\Skill;
use App\Models\TalentSkill;
use App\Models\SubcategoryAttribute;
use App\Models\TalentSkillAttribute;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class TalentSkillsController extends Controller
{
    /**
     * Get all skills for the authenticated talent with attributes.
     */
    public function index(Request $request): JsonResponse
    {
        $talentProfile = $request->user()->talentProfile;

        if (!$talentProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Talent profile not found.',
            ], 404);
        }

        $skills = TalentSkill::where('talent_profile_id', $talentProfile->id)
            ->with([
                'skill.category',
                'skill.subcategory',
                'attributes.attribute' // Load dynamic attributes
            ])
            ->orderBy('display_order', 'asc')
            ->orderBy('is_primary', 'desc')
            ->get()
            ->map(function ($talentSkill) {
                return [
                    'id' => $talentSkill->id,
                    'skill_id' => $talentSkill->skill_id,
                    'skill_name' => $talentSkill->skill->name,
                    'category' => $talentSkill->skill->category ? [
                        'id' => $talentSkill->skill->category->id,
                        'name' => $talentSkill->skill->category->name,
                    ] : null,
                    'subcategory' => $talentSkill->skill->subcategory ? [
                        'id' => $talentSkill->skill->subcategory->id,
                        'name' => $talentSkill->skill->subcategory->name,
                    ] : null,
                    'proficiency_level' => $talentSkill->proficiency_level,
                    'level_display' => $talentSkill->level_display,
                    'years_of_experience' => $talentSkill->years_of_experience,
                    'is_primary' => $talentSkill->is_primary,
                    'is_verified' => $talentSkill->is_verified,
                    'description' => $talentSkill->description,
                    'image_url' => $talentSkill->image_url,
                    'video_url' => $talentSkill->video_url,
                    'certifications' => $talentSkill->certifications,
                    'display_order' => $talentSkill->display_order,
                    'show_on_profile' => $talentSkill->show_on_profile,
                    'attributes' => $talentSkill->formatted_attributes ?? [], // Dynamic fields
                    'created_at' => $talentSkill->created_at,
                    'updated_at' => $talentSkill->updated_at,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $skills,
        ]);
    }

    /**
     * Add a new skill with dynamic attributes.
     */
    public function store(Request $request): JsonResponse
    {
        $talentProfile = $request->user()->talentProfile;

        if (!$talentProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Talent profile not found. Please create a talent profile first.',
            ], 404);
        }

        // Validate basic fields
        $validator = Validator::make($request->all(), [
            'skill_id' => 'required|uuid|exists:skills,id',
            'description' => 'nullable|string|max:5000',
            'proficiency_level' => 'required|integer|between:1,4',
            'years_of_experience' => 'nullable|integer|min:0|max:100',
            'certifications' => 'nullable|array',
            'certifications.*' => 'string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'video_url' => 'nullable|url|max:500',
            'is_primary' => 'boolean',
            'is_verified' => 'boolean',
            'display_order' => 'nullable|integer',
            'show_on_profile' => 'boolean',
            'attributes' => 'nullable|array', // Dynamic attributes
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Check if skill already exists
        $existingSkill = TalentSkill::where('talent_profile_id', $talentProfile->id)
            ->where('skill_id', $request->skill_id)
            ->first();

        if ($existingSkill) {
            return response()->json([
                'success' => false,
                'message' => 'You have already added this skill to your profile.',
            ], 409);
        }

        // Get skill to check for subcategory
        $skill = Skill::with('subcategory')->findOrFail($request->skill_id);

        // Validate dynamic attributes if subcategory has them
        if ($skill->subcategory_id && $request->has('attributes')) {
            $attributeValidation = $this->validateAttributes(
                $skill->subcategory_id,
                $request->attributes
            );

            if (!$attributeValidation['valid']) {
                return response()->json([
                    'success' => false,
                    'errors' => $attributeValidation['errors'],
                ], 422);
            }
        }

        DB::beginTransaction();
        try {
            // If this is primary, unset others
            if ($request->is_primary) {
                TalentSkill::where('talent_profile_id', $talentProfile->id)
                    ->update(['is_primary' => false]);
            }

            // Handle image upload
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('skills', 'public');
            }

            // Get display order
            $displayOrder = $request->display_order;
            if ($displayOrder === null) {
                $maxOrder = TalentSkill::where('talent_profile_id', $talentProfile->id)
                    ->max('display_order');
                $displayOrder = ($maxOrder ?? -1) + 1;
            }

            // Create talent skill
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

            // Save dynamic attributes
            if ($skill->subcategory_id && $request->has('attributes')) {
                $this->saveAttributes($talentSkill->id, $request->attributes);
            }

            // Update skill count
            $skill->updateTalentsCount();

            // Load relationships
            $talentSkill->load([
                'skill.category',
                'skill.subcategory',
                'attributes.attribute'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Skill added successfully.',
                'data' => [
                    'id' => $talentSkill->id,
                    'skill_id' => $talentSkill->skill_id,
                    'skill_name' => $talentSkill->skill->name,
                    'proficiency_level' => $talentSkill->proficiency_level,
                    'level_display' => $talentSkill->level_display,
                    'attributes' => $talentSkill->formatted_attributes ?? [],
                    'image_url' => $talentSkill->image_url,
                ],
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to add skill: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show a specific talent skill with attributes.
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
            ->with([
                'skill.category',
                'skill.subcategory',
                'attributes.attribute'
            ])
            ->first();

        if (!$talentSkill) {
            return response()->json([
                'success' => false,
                'message' => 'Skill not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $talentSkill->id,
                'skill_id' => $talentSkill->skill_id,
                'skill_name' => $talentSkill->skill->name,
                'category' => $talentSkill->skill->category ? [
                    'id' => $talentSkill->skill->category->id,
                    'name' => $talentSkill->skill->category->name,
                ] : null,
                'subcategory' => $talentSkill->skill->subcategory ? [
                    'id' => $talentSkill->skill->subcategory->id,
                    'name' => $talentSkill->skill->subcategory->name,
                ] : null,
                'proficiency_level' => $talentSkill->proficiency_level,
                'level_display' => $talentSkill->level_display,
                'years_of_experience' => $talentSkill->years_of_experience,
                'is_primary' => $talentSkill->is_primary,
                'is_verified' => $talentSkill->is_verified,
                'description' => $talentSkill->description,
                'image_url' => $talentSkill->image_url,
                'video_url' => $talentSkill->video_url,
                'certifications' => $talentSkill->certifications,
                'display_order' => $talentSkill->display_order,
                'show_on_profile' => $talentSkill->show_on_profile,
                'attributes' => $talentSkill->formatted_attributes ?? [],
                'created_at' => $talentSkill->created_at,
                'updated_at' => $talentSkill->updated_at,
            ],
        ]);
    }

    /**
     * Get required attributes for a skill/subcategory.
     * This is called when user selects a skill to see what fields they need to fill.
     */
    public function getSkillAttributes(string $skillId): JsonResponse
    {
        $skill = Skill::with('subcategory.activeAttributes')->findOrFail($skillId);

        if (!$skill->subcategory_id) {
            return response()->json([
                'success' => true,
                'message' => 'This skill has no additional attributes.',
                'data' => [],
            ]);
        }

        $attributes = $skill->subcategory->activeAttributes
            ->map(function ($attr) {
                return [
                    'id' => $attr->id,
                    'field_name' => $attr->field_name,
                    'field_label' => $attr->field_label,
                    'field_type' => $attr->field_type,
                    'field_options' => $attr->field_options,
                    'field_description' => $attr->field_description,
                    'field_placeholder' => $attr->field_placeholder,
                    'default_value' => $attr->default_value,
                    'is_required' => $attr->is_required,
                    'validation_rules' => $attr->getValidationRulesArray(),
                    'min_value' => $attr->min_value,
                    'max_value' => $attr->max_value,
                    'min_length' => $attr->min_length,
                    'max_length' => $attr->max_length,
                    'unit' => $attr->unit,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'skill' => [
                    'id' => $skill->id,
                    'name' => $skill->name,
                    'subcategory' => [
                        'id' => $skill->subcategory->id,
                        'name' => $skill->subcategory->name,
                    ],
                ],
                'attributes' => $attributes,
            ],
        ]);
    }

    /**
     * Update a talent skill with attributes.
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
            ->with('skill.subcategory')
            ->first();

        if (!$talentSkill) {
            return response()->json([
                'success' => false,
                'message' => 'Skill not found.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'description' => 'nullable|string|max:5000',
            'proficiency_level' => 'sometimes|integer|between:1,4',
            'years_of_experience' => 'nullable|integer|min:0|max:100',
            'certifications' => 'nullable|array',
            'certifications.*' => 'string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'video_url' => 'nullable|url|max:500',
            'is_primary' => 'boolean',
            'is_verified' => 'boolean',
            'display_order' => 'nullable|integer',
            'show_on_profile' => 'boolean',
            'attributes' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Validate dynamic attributes
        if ($talentSkill->skill->subcategory_id && $request->has('attributes')) {
            $attributeValidation = $this->validateAttributes(
                $talentSkill->skill->subcategory_id,
                $request->attributes
            );

            if (!$attributeValidation['valid']) {
                return response()->json([
                    'success' => false,
                    'errors' => $attributeValidation['errors'],
                ], 422);
            }
        }

        DB::beginTransaction();
        try {
            // Handle primary
            if ($request->has('is_primary') && $request->is_primary) {
                TalentSkill::where('talent_profile_id', $talentProfile->id)
                    ->where('id', '!=', $talentSkill->id)
                    ->update(['is_primary' => false]);
            }

            // Handle image
            if ($request->hasFile('image')) {
                if ($talentSkill->image_path) {
                    Storage::disk('public')->delete($talentSkill->image_path);
                }
                $talentSkill->image_path = $request->file('image')->store('skills', 'public');
            }

            // Update skill
            $updateData = $request->except(['image', 'skill_id', 'talent_profile_id', 'certifications', 'attributes']);
            if ($request->has('certifications')) {
                $updateData['certifications'] = json_encode($request->certifications);
            }
            $talentSkill->update($updateData);

            // Update dynamic attributes
            if ($talentSkill->skill->subcategory_id && $request->has('attributes')) {
                $this->saveAttributes($talentSkill->id, $request->attributes);
            }

            $talentSkill->load([
                'skill.category',
                'skill.subcategory',
                'attributes.attribute'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Skill updated successfully.',
                'data' => [
                    'id' => $talentSkill->id,
                    'skill_name' => $talentSkill->skill->name,
                    'proficiency_level' => $talentSkill->proficiency_level,
                    'attributes' => $talentSkill->formatted_attributes ?? [],
                    'image_url' => $talentSkill->image_url,
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update skill: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a talent skill and its attributes.
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

        // Delete image
        if ($talentSkill->image_path) {
            Storage::disk('public')->delete($talentSkill->image_path);
        }

        // Delete talent skill (attributes will be deleted via cascade)
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

        try {
            foreach ($request->skills as $skillData) {
                TalentSkill::where('id', $skillData['id'])
                    ->where('talent_profile_id', $talentProfile->id)
                    ->update(['display_order' => $skillData['display_order']]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Skills reordered successfully.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reorder skills: ' . $e->getMessage(),
            ], 500);
        }
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

        DB::beginTransaction();
        try {
            // Unset all primary skills
            TalentSkill::where('talent_profile_id', $talentProfile->id)
                ->update(['is_primary' => false]);

            // Set this skill as primary
            $talentSkill->update(['is_primary' => true]);

            // Load relationships
            $talentSkill->load([
                'skill.category',
                'skill.subcategory',
                'attributes.attribute'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Primary skill updated successfully.',
                'data' => [
                    'id' => $talentSkill->id,
                    'skill_name' => $talentSkill->skill->name,
                    'is_primary' => $talentSkill->is_primary,
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to set primary skill: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Validate dynamic attributes.
     * 
     * @param string $subcategoryId
     * @param array $attributes
     * @return array
     */
    private function validateAttributes(string $subcategoryId, array $attributes): array
    {
        $subcategoryAttributes = SubcategoryAttribute::where('subcategory_id', $subcategoryId)
            ->where('is_active', true)
            ->get();

        $errors = [];

        foreach ($subcategoryAttributes as $attr) {
            $value = $attributes[$attr->field_name] ?? null;

            // Check required
            if ($attr->is_required && empty($value)) {
                $errors[$attr->field_name] = ["{$attr->field_label} is required"];
                continue;
            }

            // Skip if not provided and not required
            if (empty($value)) {
                continue;
            }

            // Validate based on rules
            $rules = $attr->getValidationRulesArray();
            $validator = Validator::make(
                [$attr->field_name => $value],
                [$attr->field_name => $rules]
            );

            if ($validator->fails()) {
                $errors[$attr->field_name] = $validator->errors()->get($attr->field_name);
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Save dynamic attributes.
     * 
     * @param string $talentSkillId
     * @param array $attributes
     * @return void
     */
    private function saveAttributes(string $talentSkillId, array $attributes): void
    {
        foreach ($attributes as $fieldName => $value) {
            $attribute = SubcategoryAttribute::where('field_name', $fieldName)
                ->whereHas('subcategory.skills.talentSkills', function ($query) use ($talentSkillId) {
                    $query->where('id', $talentSkillId);
                })
                ->first();

            if ($attribute) {
                TalentSkillAttribute::updateOrCreate(
                    [
                        'talent_skill_id' => $talentSkillId,
                        'attribute_id' => $attribute->id,
                    ],
                    [
                        'value' => is_array($value) ? json_encode($value) : $value,
                    ]
                );
            }
        }
    }
}