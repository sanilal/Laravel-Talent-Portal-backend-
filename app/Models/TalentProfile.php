<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TalentProfile extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'primary_category_id',
        'professional_title',
        'summary',
        'experience_level',
        'hourly_rate_min',
        'hourly_rate_max',
        'currency',
        'avatar_url',
        'availability_types',
        'is_available',
        'work_preferences',
        'preferred_locations',
        'notice_period',
        'languages',
        'profile_completion_percentage',
        'is_featured',
        'is_public',
        'profile_views',
        'average_rating',
        'total_ratings',
        'portfolio_highlights',
        'availability_updated_at',
    ];

    protected $casts = [
        'availability_types' => 'array',
        'work_preferences' => 'array',
        'preferred_locations' => 'array',
        'languages' => 'array',
        'portfolio_highlights' => 'array',
        'is_available' => 'boolean',
        'is_featured' => 'boolean',
        'is_public' => 'boolean',
        'hourly_rate_min' => 'decimal:2',
        'hourly_rate_max' => 'decimal:2',
        'average_rating' => 'decimal:2',
        'profile_views' => 'integer',
        'total_ratings' => 'integer',
        'profile_completion_percentage' => 'integer',
        'availability_updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the talent profile
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the primary category
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'primary_category_id');
    }

    /**
     * Alias for category relationship (for backward compatibility)
     */
    public function primaryCategory(): BelongsTo
    {
        return $this->category();
    }

    /**
     * Get all skills for this talent profile
     */
    public function skills()
    {
        return $this->hasMany(TalentSkill::class, 'talent_profile_id');
    }

    /**
     * Increment profile views
     */
    public function incrementViews(): void
    {
        $this->increment('profile_views');
    }

    /**
     * Update average rating
     */
    public function updateRating(float $newRating): void
    {
        $this->total_ratings++;
        $this->average_rating = (($this->average_rating * ($this->total_ratings - 1)) + $newRating) / $this->total_ratings;
        $this->save();
    }

    /**
     * Calculate profile completion percentage
     */
    public function calculateCompletion(): int
    {
        $fields = [
            'professional_title',
            'summary',
            'experience_level',
            'hourly_rate_min',
            'availability_types',
            'work_preferences',
            'languages',
        ];

        $completed = 0;
        foreach ($fields as $field) {
            if (!empty($this->$field)) {
                $completed++;
            }
        }

        // Check for related data
        if ($this->user->talentSkills()->count() > 0) $completed++;
        if ($this->user->experiences()->count() > 0) $completed++;
        if ($this->user->education()->count() > 0) $completed++;
        if ($this->user->portfolios()->count() > 0) $completed++;

        $total = count($fields) + 4; // 4 additional relation checks
        $percentage = round(($completed / $total) * 100);

        $this->update(['profile_completion_percentage' => $percentage]);

        return $percentage;
    }
}