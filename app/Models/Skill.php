<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Subcategory;
use App\Models\SubcategoryAttribute;
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
        'subcategory_id', 
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
     * FIXED: Now uses talent_profile_id instead of talent_id
     */
    public function talents(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'talent_skills', 'skill_id', 'talent_profile_id')
                    ->using(TalentSkill::class)
                    ->withPivot([
                        'description',
                        'proficiency_level',
                        'years_of_experience',
                        'certifications',
                        'image_path',
                        'video_url',
                        'is_primary',
                        'is_verified',
                        'display_order',
                        'show_on_profile',
                    ])
                    ->withTimestamps();
    }

         /**
     * Get the subcategory this skill belongs to.
     */
    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Subcategory::class);
    }

    /**
     * Get the required attributes for this skill's subcategory.
     */
    public function getRequiredAttributes()
    {
        if (!$this->subcategory_id) {
            return collect();
        }

        return SubcategoryAttribute::where('subcategory_id', $this->subcategory_id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Scope to filter by subcategory.
     */
    public function scopeForSubcategory($query, $subcategoryId)
    {
        return $query->where('subcategory_id', $subcategoryId);
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
     * FIXED: Now counts unique talent_profile_id
     */
    public function updateTalentsCount(): void
    {
        // Count unique talent profiles that have this skill
        $this->talents_count = $this->talentSkills()
            ->distinct('talent_profile_id')
            ->count('talent_profile_id');
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