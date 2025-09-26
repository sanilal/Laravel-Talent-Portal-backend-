<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailVerificationAttempt extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'email',
        'token',
        'ip_address',
        'user_agent',
        'successful',
        'attempted_at',
        'expires_at',
    ];

    protected $casts = [
        'successful' => 'boolean',
        'attempted_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'email', 'email');
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function scopeValid($query)
    {
        return $query->where('expires_at', '>', now())
                     ->where('successful', false);
    }
}
