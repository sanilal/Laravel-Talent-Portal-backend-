<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasUuids;

    protected $fillable = ['name', 'slug', 'description'];

    /**
     * Get talent profiles in this category
     */
    public function talentProfiles(): HasMany
    {
        return $this->hasMany(TalentProfile::class, 'primary_category_id');
    }

    /**
     * Get talents (users) in this category
     * Going through talent_profiles table
     */
    public function talents()
    {
        return $this->hasManyThrough(
            User::class,
            TalentProfile::class,
            'primary_category_id', // Foreign key on talent_profiles table
            'id',                   // Foreign key on users table
            'id',                   // Local key on categories table
            'user_id'               // Local key on talent_profiles table
        )->where('users.user_type', 'talent');
    }

    /**
     * Get projects in this category
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function subcategories(): HasMany
    {
        return $this->hasMany(Subcategory::class);
    }
}