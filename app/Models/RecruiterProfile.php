<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class RecruiterProfile extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'company_name',
        'company_slug',
        'company_description',
        'industry',
        'company_size',
        'company_website',
        'company_email',
        'company_phone',
        'company_address',
        'company_logo_url',
        'social_links',
        'founded_year',
        'company_type',
        'employee_count',
        'annual_revenue',
        'company_benefits',
        'culture_values',
        'hiring_preferences',
        'is_verified',
        'is_featured',
        'verification_status',
        'average_rating',
        'total_ratings',
        'active_projects_count',
    ];

    protected $casts = [
        'company_address' => 'array',
        'social_links' => 'array',
        'company_benefits' => 'array',
        'culture_values' => 'array',
        'hiring_preferences' => 'array',
        'is_verified' => 'boolean',
        'is_featured' => 'boolean',
        'founded_year' => 'integer',
        'employee_count' => 'integer',
        'annual_revenue' => 'decimal:2',
        'average_rating' => 'decimal:2',
        'total_ratings' => 'integer',
        'active_projects_count' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($recruiterProfile) {
            if (empty($recruiterProfile->company_slug)) {
                $recruiterProfile->company_slug = Str::slug($recruiterProfile->company_name);
                
                // Ensure uniqueness
                $originalSlug = $recruiterProfile->company_slug;
                $count = 1;
                while (static::where('company_slug', $recruiterProfile->company_slug)->exists()) {
                    $recruiterProfile->company_slug = $originalSlug . '-' . $count;
                    $count++;
                }
            }
        });
    }

    /**
     * Get the user that owns the recruiter profile
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the projects posted by this recruiter
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'recruiter_id');
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
     * Update active projects count
     */
    public function updateActiveProjectsCount(): void
    {
        $this->active_projects_count = $this->projects()
            ->whereIn('status', ['open', 'in_progress'])
            ->count();
        $this->save();
    }

    /**
     * Get verification status badge
     */
    public function getVerificationBadgeAttribute(): string
    {
        return match($this->verification_status) {
            'verified' => 'Verified Company',
            'pending' => 'Verification Pending',
            'rejected' => 'Not Verified',
            default => 'Unknown Status'
        };
    }

    /**
     * Get company size display name
     */
    public function getCompanySizeDisplayAttribute(): string
    {
        return match($this->company_size) {
            'startup' => 'Startup (1-10)',
            'small' => 'Small (11-50)',
            'medium' => 'Medium (51-200)',
            'large' => 'Large (201-1000)',
            'enterprise' => 'Enterprise (1000+)',
            default => 'Not specified'
        };
    }
}
