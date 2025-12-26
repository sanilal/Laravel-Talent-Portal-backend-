<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CastingCall extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'recruiter_id',
        'genre_id',
        'project_type_id',
        'project_name',
        'title',
        'director',
        'production_company',
        'description',
        'synopsis',
        'additional_notes',
        'role_name',
        'role_type',
        'gender_required',
        'age_min',
        'age_max',
        'ethnicity_preferences',
        'required_skills',
        'audition_script',
        'audition_duration_seconds',
        'submission_requirements',
        'location',
        'city',
        'country_id',
        'state_id',
        'deadline',
        'audition_date',
        'audition_location',
        'is_remote_audition',
        'compensation_type',
        'rate_amount',
        'rate_currency',
        'rate_period',
        'status',
        'visibility',
        'is_featured',
        'is_urgent',
        'views_count',
        'applications_count',
    ];

    protected $casts = [
        'deadline' => 'datetime',
        'audition_date' => 'datetime',
        'is_remote_audition' => 'boolean',
        'is_featured' => 'boolean',
        'is_urgent' => 'boolean',
        'views_count' => 'integer',
        'applications_count' => 'integer',
        'rate_amount' => 'decimal:2',
        'audition_duration_seconds' => 'integer',
        'age_min' => 'integer',
        'age_max' => 'integer',
        'ethnicity_preferences' => 'array',
        'required_skills' => 'array',
        'submission_requirements' => 'array',
    ];

    protected $attributes = [
        'status' => 'draft',
        'visibility' => 'public',
        'rate_currency' => 'AED',
        'is_featured' => false,
        'is_urgent' => false,
        'views_count' => 0,
        'applications_count' => 0,
    ];

    /**
     * Get the recruiter that posted this casting call
     */
    public function recruiter(): BelongsTo
    {
        return $this->belongsTo(RecruiterProfile::class, 'recruiter_id');
    }

    /**
     * Get the genre
     */
    public function genre(): BelongsTo
    {
        return $this->belongsTo(Genre::class);
    }

    /**
     * Get the project type
     */
    public function projectType(): BelongsTo
    {
        return $this->belongsTo(ProjectType::class);
    }

    /**
     * Get the country
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get the state
     */
    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    /**
     * Get the requirements for this casting call
     */
    public function requirements(): HasMany
    {
        return $this->hasMany(CastingCallRequirement::class)->orderBy('display_order');
    }

    /**
     * Get all applications for this casting call
     */
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    /**
     * Get all media (documents) for this casting call
     */
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    /**
     * Scope for published casting calls
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                     ->where('visibility', 'public');
    }

    /**
     * Scope for active casting calls (not expired)
     */
    public function scopeActive($query)
    {
        return $query->where('deadline', '>', now())
                     ->orWhereNull('deadline');
    }

    /**
     * Scope for featured casting calls
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope for urgent casting calls
     */
    public function scopeUrgent($query)
    {
        return $query->where('is_urgent', true);
    }

    /**
     * Scope for recruiter's casting calls
     */
    public function scopeByRecruiter($query, $recruiterId)
    {
        return $query->where('recruiter_id', $recruiterId);
    }

    /**
     * Increment views count
     */
    public function incrementViews()
    {
        $this->increment('views_count');
    }

    /**
     * Increment applications count
     */
    public function incrementApplications()
    {
        $this->increment('applications_count');
    }

    /**
     * Check if casting call is expired
     */
    public function isExpired(): bool
    {
        return $this->deadline && $this->deadline->isPast();
    }

    /**
     * Check if casting call accepts applications
     */
    public function acceptsApplications(): bool
    {
        return $this->status === 'published' 
               && !$this->isExpired();
    }
}