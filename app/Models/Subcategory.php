<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\SubcategoryAttribute;
use App\Models\Skill;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subcategory extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'icon',
        'color',
        'is_active',
        'sort_order',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Get the category that owns the subcategory.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the talent profiles for the subcategory.
     */
    public function talentProfiles(): HasMany
    {
        return $this->hasMany(TalentProfile::class);
    }

    /**
     * Get the experiences for the subcategory.
     */
    public function experiences(): HasMany
    {
        return $this->hasMany(Experience::class);
    }

    /**
     * Get the portfolios for the subcategory.
     */
    public function portfolios(): HasMany
    {
        return $this->hasMany(Portfolio::class);
    }

    /**
     * Scope a query to only include active subcategories.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to order by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc');
    }

    /**
     * Get the skills for the subcategory.
     */
    public function skills(): HasMany
    {
        return $this->hasMany(Skill::class);
    }

    /**
     * Get the dynamic attributes for this subcategory.
     */
    public function attributes(): HasMany
    {
        return $this->hasMany(SubcategoryAttribute::class);
    }

    /**
     * Get active attributes for this subcategory.
     */
    public function activeAttributes(): HasMany
    {
        return $this->hasMany(SubcategoryAttribute::class)
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    /**
     * Get required attributes for this subcategory.
     */
    public function requiredAttributes(): HasMany
    {
        return $this->hasMany(SubcategoryAttribute::class)
            ->where('is_active', true)
            ->where('is_required', true)
            ->orderBy('sort_order');
    }
}