<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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
    ];

    protected $casts = [
        'proficiency_level' => 'integer', // 1-5 scale based on your original
        'years_of_experience' => 'integer',
        'is_primary' => 'boolean',
        'is_verified' => 'boolean',
        'certifications' => 'array', // JSON field
    ];

    protected $appends = ['image_url', 'level_display', 'proficiency_badge'];

    // Constants for proficiency levels
    const LEVEL_BEGINNER = 1;
    const LEVEL_INTERMEDIATE = 2;
    const LEVEL_ADVANCED = 3;
    const LEVEL_EXPERT = 4;
    const LEVEL_MASTER = 5;

    const LEVELS = [
        self::LEVEL_BEGINNER => 'Beginner',
        self::LEVEL_INTERMEDIATE => 'Intermediate',
        self::LEVEL_ADVANCED => 'Advanced',
        self::LEVEL_EXPERT => 'Expert',
        self::LEVEL_MASTER => 'Master',
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

        // Otherwise, generate storage URL
        return Storage::url($this->image_path);
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
            self::LEVEL_MASTER => 'purple',
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
}