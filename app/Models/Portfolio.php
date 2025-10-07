<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Portfolio extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'talent_profile_id',
        'category_id',
        'title',
        'slug',
        'description',
        'project_type',
        'skills_demonstrated',
        'project_url',
        'external_url',
        'completion_date',
        'client_name',
        'director_name',
        'role_description',
        'challenges_faced',
        'collaborators',
        'awards',
        'is_featured',
        'is_public',
        'is_demo_reel',
        'views_count',
        'likes_count',
        'average_rating',
        'total_ratings',
        'order',
        'metadata',
    ];

    protected $casts = [
        'completion_date' => 'date',
        'is_featured' => 'boolean',
        'is_public' => 'boolean',
        'is_demo_reel' => 'boolean',
        'skills_demonstrated' => 'array',
        'collaborators' => 'array',
        'awards' => 'array',
        'metadata' => 'array',
        'views_count' => 'integer',
        'likes_count' => 'integer',
        'average_rating' => 'decimal:2',
        'total_ratings' => 'integer',
        'order' => 'integer',
        'requirements_embedding' => 'array',
        'required_skills_embedding' => 'array',
        'embeddings_generated_at' => 'datetime',
    ];

    public function talentProfile()
    {
        return $this->belongsTo(TalentProfile::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function media()
    {
        return $this->hasMany(Media::class, 'mediable_id')
                    ->where('mediable_type', self::class);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }
}