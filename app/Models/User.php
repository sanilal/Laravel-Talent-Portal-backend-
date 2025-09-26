<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, HasUuids, SoftDeletes;

    /**
     * Indicates if the model's ID is auto-incrementing.
     */
    public $incrementing = false;

    /**
     * The data type of the auto-incrementing ID.
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'first_name',
        'last_name', 
        'email',
        'password',
        'phone',
        'date_of_birth',
        'gender',
        'bio',
        'location',
        'website',
        'social_links',
        'user_type',
        'account_status',
        'timezone',
        'privacy_settings',
        'preferences',
        'last_login_ip',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'date_of_birth' => 'date',
        'social_links' => 'array',
        'privacy_settings' => 'array',
        'preferences' => 'array',
        'password' => 'hashed',
        'is_verified' => 'boolean',
        'is_email_verified' => 'boolean',
    ];

    /**
     * Get full name attribute
     */
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * Check user types
     */
    public function isTalent()
    {
        return $this->user_type === 'talent';
    }

    public function isRecruiter()
    {
        return $this->user_type === 'recruiter';
    }

    public function isAdmin()
    {
        return $this->user_type === 'admin';
    }

    /**
     * Relationships
     */
    public function talentProfile()
    {
        return $this->hasOne(TalentProfile::class);
    }

    public function recruiterProfile()
    {
        return $this->hasOne(RecruiterProfile::class);
    }
}