<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\TalentSkillAttribute;
use App\Models\SubcategoryAttribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Facades\Storage;

class TalentSkill extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'talent_profile_id',
        'skill_id',
        'proficiency_level',
        'years_of_experience',
        'is_primary',
        'description',
        'certifications',
        'is_verified',
        'image_path',
        'video_url',
        'display_order',
        'show_on_profile',
    ];

    protected $casts = [
        'proficiency_level' => 'integer', // 1-4 scale (Beginner, Intermediate, Advanced, Expert)
        'years_of_experience' => 'integer',
        'is_primary' => 'boolean',
        'is_verified' => 'boolean',
        'display_order' => 'integer',
        'show_on_profile' => 'boolean',
        'certifications' => 'array', // JSON field
    ];

    protected $appends = ['image_url', 'level_display', 'proficiency_badge'];

    // Constants for proficiency levels (Updated to match controller validation: 1-4)
    const LEVEL_BEGINNER = 1;
    const LEVEL_INTERMEDIATE = 2;
    const LEVEL_ADVANCED = 3;
    const LEVEL_EXPERT = 4;

    const LEVELS = [
        self::LEVEL_BEGINNER => 'Beginner',
        self::LEVEL_INTERMEDIATE => 'Intermediate',
        self::LEVEL_ADVANCED => 'Advanced',
        self::LEVEL_EXPERT => 'Expert',
    ];

    // Relationships
    public function talentProfile()
    {
        return $this->belongsTo(TalentProfile::class);
    }

    public function skill()
    {
        return $this->belongsTo(Skill::class);
    }

    public function attributes(): HasMany
    {
        return $this->hasMany(TalentSkillAttribute::class);
    }

    // Accessors
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image_path) {
            return null;
        }

        // If it's already a full URL, return as is
        if (filter_var($this->image_path, FILTER_VALIDATE_URL)) {
            return $this->image_path;
        }

        // FIXED: Specify 'public' disk explicitly
        return Storage::disk('public')->url($this->image_path);
    }

    public function getLevelDisplayAttribute(): string
    {
        return self::LEVELS[$this->proficiency_level] ?? 'Not specified';
    }

    public function getProficiencyBadgeAttribute(): string
    {
        return match($this->proficiency_level) {
            self::LEVEL_BEGINNER => 'blue',
            self::LEVEL_INTERMEDIATE => 'green',
            self::LEVEL_ADVANCED => 'yellow',
            self::LEVEL_EXPERT => 'orange',
            default => 'gray',
        };
    }

    // Scopes
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('is_primary', 'desc')
                     ->orderBy('proficiency_level', 'desc')
                     ->orderBy('years_of_experience', 'desc');
    }

    public function scopeVisible($query)
    {
        return $query->where('show_on_profile', true);
    }

    /**
     * Get formatted attributes for display.
     */
    public function getFormattedAttributesAttribute(): array
    {
        $formatted = [];
        
        $this->load(['attributes.attribute']);
        
        foreach ($this->attributes as $attr) {
            if (!$attr->attribute) continue;
            
            $formatted[$attr->attribute->field_name] = [
                'label' => $attr->attribute->field_label,
                'value' => $attr->parsed_value,
                'type' => $attr->attribute->field_type,
                'unit' => $attr->attribute->unit,
            ];
        }
        
        return $formatted;
    }

    /**
     * Get a specific skill attribute value by field name.
     * 
     * Note: Renamed from getAttributeValue to avoid conflict with Laravel's Model::getAttributeValue()
     */
    public function getSkillAttributeValue(string $fieldName)
    {
        $attribute = $this->attributes()
            ->whereHas('attribute', function ($query) use ($fieldName) {
                $query->where('field_name', $fieldName);
            })
            ->with('attribute')
            ->first();

        return $attribute ? $attribute->parsed_value : null;
    }

    /**
     * Set a specific skill attribute value by field name.
     * 
     * Note: Renamed from setAttributeValue to avoid potential conflict with Laravel methods
     */
    public function setSkillAttributeValue(string $fieldName, $value): void
    {
        $subcategoryAttribute = SubcategoryAttribute::where('field_name', $fieldName)
            ->whereHas('subcategory.skills', function ($query) {
                $query->where('id', $this->skill_id);
            })
            ->first();

        if (!$subcategoryAttribute) {
            throw new \Exception("Attribute '{$fieldName}' not found for this skill");
        }

        TalentSkillAttribute::updateOrCreate(
            [
                'talent_skill_id' => $this->id,
                'attribute_id' => $subcategoryAttribute->id,
            ],
            [
                'value' => $value,
            ]
        );
    }
}