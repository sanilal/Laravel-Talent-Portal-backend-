<?php

namespace App\Services;

use App\Models\TalentProfile;
use App\Models\TalentSkill;
use App\Models\SubcategoryAttribute;
use App\Models\TalentSkillAttribute;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * TalentAttributesService
 * 
 * Manages both profile-level and skill-level dynamic attributes
 * 
 * Usage:
 * - Profile-level: Physical attributes (height, weight, hair_color, etc.)
 * - Skill-level: Skill-specific attributes (vocal_range, dance_styles, etc.)
 */
class TalentAttributesService
{
    /**
     * Physical attributes that belong at profile level
     */
    private const PROFILE_LEVEL_ATTRIBUTES = [
        'height',
        'weight',
        'bust_chest',
        'chest',
        'waist',
        'hips',
        'shoe_size',
        'hair_color',
        'eye_color',
        'skin_tone',
        'body_type',
    ];

    /**
     * Get all attributes for a talent profile (both profile-level and skill-level)
     */
    public function getTalentAttributes(string $talentProfileId): array
    {
        $profile = TalentProfile::findOrFail($talentProfileId);

        return [
            'profile_attributes' => $this->getProfileLevelAttributes($profile),
            'skill_attributes' => $this->getSkillLevelAttributes($profile),
        ];
    }

    /**
     * Get profile-level attributes (physical attributes)
     */
    public function getProfileLevelAttributes(TalentProfile $profile): array
    {
        $attributes = TalentSkillAttribute::where('talent_profile_id', $profile->id)
            ->whereNull('talent_skill_id')
            ->with('attribute')
            ->get();

        return $attributes->mapWithKeys(function ($item) {
            return [$item->attribute->field_name => $item->value];
        })->toArray();
    }

    /**
     * Get skill-level attributes for a specific skill
     */
    public function getSkillAttributes(string $talentSkillId): array
    {
        $skill = TalentSkill::findOrFail($talentSkillId);

        $attributes = TalentSkillAttribute::where('talent_skill_id', $skill->id)
            ->whereNotNull('talent_skill_id')
            ->with('attribute')
            ->get();

        return $attributes->mapWithKeys(function ($item) {
            return [$item->attribute->field_name => $item->value];
        })->toArray();
    }

    /**
     * Get all skill-level attributes for a profile
     */
    public function getSkillLevelAttributes(TalentProfile $profile): array
    {
        $skills = $profile->skills()->with(['attributes.attribute'])->get();

        return $skills->map(function ($skill) {
            return [
                'skill_id' => $skill->id,
                'skill_name' => $skill->skill->name ?? 'Unknown',
                'attributes' => $skill->attributes->mapWithKeys(function ($item) {
                    return [$item->attribute->field_name => $item->value];
                })->toArray(),
            ];
        })->toArray();
    }

    /**
     * Set profile-level attributes
     */
    public function setProfileAttributes(string $talentProfileId, array $attributes): void
    {
        $profile = TalentProfile::findOrFail($talentProfileId);

        DB::transaction(function () use ($profile, $attributes) {
            foreach ($attributes as $fieldName => $value) {
                // Find the attribute definition
                $attributeDef = SubcategoryAttribute::where('field_name', $fieldName)->first();

                if (!$attributeDef) {
                    continue; // Skip unknown attributes
                }

                // Check if profile-level attribute
                if (!in_array($fieldName, self::PROFILE_LEVEL_ATTRIBUTES)) {
                    throw new \InvalidArgumentException("Attribute '{$fieldName}' is not a profile-level attribute");
                }

                // Update or create
                TalentSkillAttribute::updateOrCreate(
                    [
                        'talent_profile_id' => $profile->id,
                        'talent_skill_id' => null,
                        'attribute_id' => $attributeDef->id,
                    ],
                    [
                        'value' => $value,
                    ]
                );
            }
        });
    }

    /**
     * Set skill-level attributes
     */
    public function setSkillAttributes(string $talentSkillId, array $attributes): void
    {
        $talentSkill = TalentSkill::findOrFail($talentSkillId);
        $skill = $talentSkill->skill;

        if (!$skill || !$skill->subcategory_id) {
            throw new \Exception("Skill must have a subcategory to set attributes");
        }

        DB::transaction(function () use ($talentSkill, $skill, $attributes) {
            foreach ($attributes as $fieldName => $value) {
                // Find the attribute definition for this subcategory
                $attributeDef = SubcategoryAttribute::where('subcategory_id', $skill->subcategory_id)
                    ->where('field_name', $fieldName)
                    ->first();

                if (!$attributeDef) {
                    continue; // Skip attributes not defined for this subcategory
                }

                // Don't allow profile-level attributes at skill level
                if (in_array($fieldName, self::PROFILE_LEVEL_ATTRIBUTES)) {
                    throw new \InvalidArgumentException("Attribute '{$fieldName}' should be set at profile level, not skill level");
                }

                // Update or create
                TalentSkillAttribute::updateOrCreate(
                    [
                        'talent_skill_id' => $talentSkill->id,
                        'attribute_id' => $attributeDef->id,
                    ],
                    [
                        'talent_profile_id' => $talentSkill->talent_profile_id,
                        'value' => $value,
                    ]
                );
            }
        });
    }

    /**
     * Get available attributes for a subcategory
     */
    public function getAvailableAttributes(string $subcategoryId): array
    {
        $attributes = SubcategoryAttribute::where('subcategory_id', $subcategoryId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return $attributes->map(function ($attr) {
            return [
                'id' => $attr->id,
                'field_name' => $attr->field_name,
                'field_label' => $attr->field_label,
                'field_type' => $attr->field_type,
                'field_options' => $attr->field_options,
                'is_required' => $attr->is_required,
                'field_description' => $attr->field_description,
                'field_placeholder' => $attr->field_placeholder,
                'validation_rules' => $attr->validation_rules,
                'min_value' => $attr->min_value,
                'max_value' => $attr->max_value,
                'unit' => $attr->unit,
                'is_profile_level' => in_array($attr->field_name, self::PROFILE_LEVEL_ATTRIBUTES),
            ];
        })->toArray();
    }

    /**
     * Get profile-level attribute definitions
     */
    public function getProfileLevelAttributeDefinitions(): array
    {
        $attributes = SubcategoryAttribute::whereIn('field_name', self::PROFILE_LEVEL_ATTRIBUTES)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->unique('field_name'); // Remove duplicates across subcategories

        return $attributes->map(function ($attr) {
            return [
                'id' => $attr->id,
                'field_name' => $attr->field_name,
                'field_label' => $attr->field_label,
                'field_type' => $attr->field_type,
                'field_options' => $attr->field_options,
                'is_required' => $attr->is_required,
                'field_description' => $attr->field_description,
                'field_placeholder' => $attr->field_placeholder,
                'validation_rules' => $attr->validation_rules,
                'min_value' => $attr->min_value,
                'max_value' => $attr->max_value,
                'unit' => $attr->unit,
            ];
        })->toArray();
    }

    /**
     * Delete profile-level attribute
     */
    public function deleteProfileAttribute(string $talentProfileId, string $fieldName): bool
    {
        $attributeDef = SubcategoryAttribute::where('field_name', $fieldName)->first();

        if (!$attributeDef) {
            return false;
        }

        return TalentSkillAttribute::where('talent_profile_id', $talentProfileId)
            ->whereNull('talent_skill_id')
            ->where('attribute_id', $attributeDef->id)
            ->delete() > 0;
    }

    /**
     * Delete skill-level attribute
     */
    public function deleteSkillAttribute(string $talentSkillId, string $fieldName): bool
    {
        $talentSkill = TalentSkill::find($talentSkillId);
        if (!$talentSkill || !$talentSkill->skill) {
            return false;
        }

        $attributeDef = SubcategoryAttribute::where('subcategory_id', $talentSkill->skill->subcategory_id)
            ->where('field_name', $fieldName)
            ->first();

        if (!$attributeDef) {
            return false;
        }

        return TalentSkillAttribute::where('talent_skill_id', $talentSkill->id)
            ->where('attribute_id', $attributeDef->id)
            ->delete() > 0;
    }
}