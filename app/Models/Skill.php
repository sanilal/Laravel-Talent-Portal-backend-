<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Skill extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'category_id',
        'description',
        'icon',
        'is_featured',
        'is_active',
        'usage_count',
        'talents_count',
        'metadata',
        'embedding_model',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'usage_count' => 'integer',
        'talents_count' => 'integer',
        'metadata' => 'array',
        'skill_embedding' => 'array',
        'embeddings_generated_at' => 'datetime',
    ];

    /**
     * Get the category this skill belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get all talents that have this skill.
     */
    public function talents(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'talent_skills', 'skill_id', 'talent_profile_id')
                    ->using(TalentSkill::class)
                    ->withPivot([
                        'description',
                        'proficiency_level',
                        'years_of_experience',
                        'image_path',
                        'video_url',
                        'is_primary',
                        'display_order',
                        'show_on_profile',
                    ])
                    ->withTimestamps();
    }

    /**
     * Get all talent skill pivot records.
     */
    public function talentSkills(): HasMany
    {
        return $this->hasMany(TalentSkill::class);
    }

    /**
     * Scope to get only active skills.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get only featured skills.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope to search skills by name.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', "%{$search}%");
    }

    /**
     * Update the cached talents count.
     */
    public function updateTalentsCount(): void
    {
        $this->talents_count = $this->talents()->count();
        $this->save();
    }

    /**
     * Increment the usage count.
     */
    public function incrementUsageCount(): void
    {
        $this->increment('usage_count');
    }
}