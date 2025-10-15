<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasUuids, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'user_type',
        'phone',
        'date_of_birth',
        'gender',
        'bio',
        'location',
        'city',
        'state',
        'country',
        'timezone',
        'website',
        'social_links',
        'avatar',
        'cover_image',
        'professional_title',
        'hourly_rate',
        'currency',
        'experience_level',
        'availability_status',
        'account_status',
        'is_verified',
        'is_email_verified',
        'email_verified_at',
        'last_login_at',
        'last_login_ip',
        'last_activity_at',
        'preferences',
        'privacy_settings',
        'profile_views',
        'profile_completion',
        'languages',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'date_of_birth' => 'date',
        'last_login_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'social_links' => 'array',
        'preferences' => 'array',
        'privacy_settings' => 'array',
        'languages' => 'array',
        'password' => 'hashed',
        'is_verified' => 'boolean',
        'is_email_verified' => 'boolean',
        'hourly_rate' => 'decimal:2',
        'profile_views' => 'integer',
        'profile_completion' => 'integer',
    ];

    /**
     * User types constants
     */
    const TYPE_TALENT = 'talent';
    const TYPE_RECRUITER = 'recruiter';
    const TYPE_ADMIN = 'admin';

    const TYPES = [
        self::TYPE_TALENT,
        self::TYPE_RECRUITER,
        self::TYPE_ADMIN,
    ];

    /**
     * User status constants
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_BANNED = 'banned';

    /**
     * Availability status constants
     */
    const AVAILABILITY_AVAILABLE = 'available';
    const AVAILABILITY_BUSY = 'busy';
    const AVAILABILITY_NOT_AVAILABLE = 'not_available';

    /**
     * Experience level constants
     */
    const EXPERIENCE_ENTRY = 'entry';
    const EXPERIENCE_INTERMEDIATE = 'intermediate';
    const EXPERIENCE_EXPERT = 'expert';

    /**
     * Get the user's full name.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get the user's full location.
     */
    public function getFullLocationAttribute(): ?string
    {
        $parts = array_filter([$this->city, $this->state, $this->country]);
        return !empty($parts) ? implode(', ', $parts) : null;
    }

    /**
     * Check if user is a talent.
     */
    public function isTalent(): bool
    {
        return $this->user_type === self::TYPE_TALENT;
    }

    /**
     * Check if user is a recruiter.
     */
    public function isRecruiter(): bool
    {
        return $this->user_type === self::TYPE_RECRUITER;
    }

    /**
     * Check if user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->user_type === self::TYPE_ADMIN;
    }

    /**
     * Check if user account is active.
     */
    public function isActive(): bool
    {
        return $this->account_status === self::STATUS_ACTIVE;
    }

    /**
     * Check if user is available for work.
     */
    public function isAvailable(): bool
    {
        return $this->availability_status === self::AVAILABILITY_AVAILABLE;
    }

    // ==================== SKILLS RELATIONSHIPS ====================

    /**
     * Get all skills with their detailed information.
     * This includes the pivot data (description, images, videos, etc.)
     */
    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'talent_skills', 'talent_id', 'skill_id')
                    ->using(TalentSkill::class)
                    ->withPivot([
                        'id',
                        'description',
                        'proficiency_level',
                        'years_of_experience',
                        'image_path',
                        'video_url',
                        'is_primary',
                        'display_order',
                        'show_on_profile',
                    ])
                    ->withTimestamps()
                    ->orderByPivot('display_order', 'asc')
                    ->orderByPivot('is_primary', 'desc');
    }

    /**
     * Get all talent skill records (direct access to pivot table).
     */
    public function talentSkills(): HasMany
    {
        return $this->hasMany(TalentSkill::class, 'talent_id');
    }

    /**
     * Get only visible skills for public display.
     */
    public function visibleSkills(): BelongsToMany
    {
        return $this->skills()->wherePivot('show_on_profile', true);
    }

    /**
     * Get the primary skill.
     */
    public function primarySkill()
    {
        return $this->talentSkills()
                    ->where('is_primary', true)
                    ->with('skill')
                    ->first();
    }

    /**
     * Get skills by proficiency level.
     */
    public function skillsByProficiency(string $level): BelongsToMany
    {
        return $this->skills()->wherePivot('proficiency_level', $level);
    }

    // ==================== OTHER RELATIONSHIPS ====================

    /**
     * Get the talent profile for this user.
     */
    public function talentProfile()
    {
        return $this->hasOne(TalentProfile::class);
    }

    /**
     * Get the recruiter profile for this user.
     */
    public function recruiterProfile()
    {
        return $this->hasOne(RecruiterProfile::class);
    }

    /**
     * Get all experiences.
     */
    public function experiences()
    {
        return $this->hasMany(Experience::class, 'user_id');
    }

    /**
     * Get all education records.
     */
    public function education()
    {
        return $this->hasMany(Education::class, 'user_id');
    }

    /**
     * Get all portfolios.
     */
    public function portfolios()
    {
        return $this->hasMany(Portfolio::class, 'user_id');
    }

    /**
     * Get all job applications by this user (as talent).
     */
    public function applications()
    {
        return $this->hasMany(Application::class, 'talent_id');
    }

    /**
     * Get all media uploaded by this user.
     */
    public function media()
    {
        return $this->hasMany(Media::class);
    }

    /**
     * Get all messages sent by this user.
     */
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Get all messages received by this user.
     */
    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'recipient_id');
    }

    /**
     * Get all notifications for this user.
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get all reviews written by this user.
     */
    public function givenReviews()
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    /**
     * Get all reviews received by this user.
     */
    public function receivedReviews()
    {
        return $this->hasMany(Review::class, 'reviewee_id');
    }

    // ==================== QUERY SCOPES ====================

    /**
     * Scope for active users.
     */
    public function scopeActive($query)
    {
        return $query->where('account_status', self::STATUS_ACTIVE);
    }

    /**
     * Scope for users by type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('user_type', $type);
    }

    /**
     * Scope for verified users.
     */
    public function scopeVerified($query)
    {
        return $query->where('is_email_verified', true);
    }

    /**
     * Scope for available talents.
     */
    public function scopeAvailable($query)
    {
        return $query->where('availability_status', self::AVAILABILITY_AVAILABLE);
    }

    /**
     * Scope for talents by experience level.
     */
    public function scopeByExperience($query, string $level)
    {
        return $query->where('experience_level', $level);
    }

    /**
     * Increment profile views.
     */
    public function incrementProfileViews(): void
    {
        $this->increment('profile_views');
    }
}