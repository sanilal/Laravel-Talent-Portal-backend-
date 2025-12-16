<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TalentSkillAttribute extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'talent_skill_id',      // For skill-level attributes
        'talent_profile_id',    // For profile-level attributes (physical attributes)
        'attribute_id',
        'value',
    ];

    protected $casts = [
        // Value is stored as text but can be JSON for arrays
    ];

    /**
     * Get the talent skill that owns the attribute value.
     * (Only for skill-level attributes)
     */
    public function talentSkill(): BelongsTo
    {
        return $this->belongsTo(TalentSkill::class);
    }

    /**
     * Get the talent profile that owns the attribute value.
     * (Only for profile-level attributes like physical attributes)
     */
    public function talentProfile(): BelongsTo
    {
        return $this->belongsTo(TalentProfile::class);
    }

    /**
     * Get the attribute definition.
     */
    public function attribute(): BelongsTo
    {
        return $this->belongsTo(SubcategoryAttribute::class, 'attribute_id');
    }

    /**
     * Get the parsed value based on field type.
     */
    public function getParsedValueAttribute()
    {
        if (!$this->attribute) {
            return $this->value;
        }

        // Parse based on field type
        switch ($this->attribute->field_type) {
            case SubcategoryAttribute::TYPE_MULTISELECT:
            case SubcategoryAttribute::TYPE_CHECKBOX:
                return json_decode($this->value, true) ?? [];

            case SubcategoryAttribute::TYPE_NUMBER:
            case SubcategoryAttribute::TYPE_RANGE:
                return is_numeric($this->value) ? (float) $this->value : null;

            case SubcategoryAttribute::TYPE_DATE:
                return $this->value ? \Carbon\Carbon::parse($this->value) : null;

            default:
                return $this->value;
        }
    }

    /**
     * Set the value, handling arrays for multiselect/checkbox fields.
     */
    public function setValueAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['value'] = json_encode($value);
        } else {
            $this->attributes['value'] = $value;
        }
    }
}