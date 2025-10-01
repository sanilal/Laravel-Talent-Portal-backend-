<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;



class TalentSkill extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'talent_profile_id',
        'skill_id',
        'level',
        'years_experience',
        'is_primary',
        'endorsements_count',
        'last_used_at',
        'certification_url',
    ];

    protected $casts = [
        'years_experience' => 'integer',
        'is_primary' => 'boolean',
        'endorsements_count' => 'integer',
        'last_used_at' => 'date',
    ];

    const LEVEL_BEGINNER = 'beginner';
    const LEVEL_INTERMEDIATE = 'intermediate';
    const LEVEL_ADVANCED = 'advanced';
    const LEVEL_EXPERT = 'expert';

    const LEVELS = [
        self::LEVEL_BEGINNER,
        self::LEVEL_INTERMEDIATE,
        self::LEVEL_ADVANCED,
        self::LEVEL_EXPERT,
    ];

    public function talentProfile()
    {
        return $this->belongsTo(TalentProfile::class);
    }

    public function skill()
    {
        return $this->belongsTo(Skill::class);
    }

    public function getLevelDisplayAttribute(): string
    {
        return match($this->level) {
            self::LEVEL_BEGINNER => 'Beginner',
            self::LEVEL_INTERMEDIATE => 'Intermediate',
            self::LEVEL_ADVANCED => 'Advanced',
            self::LEVEL_EXPERT => 'Expert',
            default => 'Not specified'
        };
    }
}