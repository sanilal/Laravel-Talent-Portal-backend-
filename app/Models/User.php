<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
        'location',
        'timezone',
        'profile_picture',
        'bio',
        'website',
        'social_links',
        'email_verified_at',
        'phone_verified_at',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'account_status',
        'last_login_at',
        'last_login_ip',
        'last_activity_at',
        'login_attempts',
        'locked_until',
        'preferences',
        'privacy_settings',
        'two_factor_enabled',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'two_factor_confirmed_at' => 'datetime',
        'date_of_birth' => 'date',
        'last_login_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'locked_until' => 'datetime',
        'social_links' => 'array',
        'preferences' => 'array',
        'privacy_settings' => 'array',
        'password' => 'hashed',
        'two_factor_enabled' => 'boolean',
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
     * Get the user's full name.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
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
     * Check if user account is locked.
     */
    public function isLocked(): bool
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }

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
     * Get all talent skills through talent profile.
     */
    public function skills()
    {
        return $this->hasManyThrough(
            TalentSkill::class,
            TalentProfile::class,
            'user_id',           // Foreign key on talent_profiles table
            'talent_profile_id', // Foreign key on talent_skills table
            'id',                // Local key on users table
            'id'                 // Local key on talent_profiles table
        );
    }

    /**
     * Alias for skills relationship
     */
    public function talentSkills()
    {
        return $this->skills();
    }

    /**
     * Get all experiences through talent profile.
     */
    public function experiences()
    {
        return $this->hasManyThrough(
            Experience::class,
            TalentProfile::class,
            'user_id',           // Foreign key on talent_profiles table
            'talent_profile_id', // Foreign key on experiences table
            'id',                // Local key on users table
            'id'                 // Local key on talent_profiles table
        );
    }

    /**
     * Get all education records through talent profile.
     */
    public function education()
    {
        return $this->hasManyThrough(
            Education::class,
            TalentProfile::class,
            'user_id',           // Foreign key on talent_profiles table
            'talent_profile_id', // Foreign key on education table
            'id',                // Local key on users table
            'id'                 // Local key on talent_profiles table
        );
    }

    /**
     * Get all portfolios through talent profile.
     */
    public function portfolios()
    {
        return $this->hasManyThrough(
            Portfolio::class,
            TalentProfile::class,
            'user_id',           // Foreign key on talent_profiles table
            'talent_profile_id', // Foreign key on portfolios table
            'id',                // Local key on users table
            'id'                 // Local key on talent_profiles table
        );
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

    /**
     * Get all login attempts for this user.
     */
    public function loginAttempts()
    {
        return $this->hasMany(LoginAttempt::class, 'email', 'email');
    }

    /**
     * Get all email verification attempts for this user.
     */
    public function emailVerificationAttempts()
    {
        return $this->hasMany(EmailVerificationAttempt::class, 'email', 'email');
    }

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
        return $query->whereNotNull('email_verified_at');
    }
}