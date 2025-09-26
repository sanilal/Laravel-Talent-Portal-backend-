<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'action_url',
        'read_at',
        'is_important',
        'expires_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_important' => 'boolean',
    ];

    const TYPE_APPLICATION_SUBMITTED = 'application_submitted';
    const TYPE_APPLICATION_ACCEPTED = 'application_accepted';
    const TYPE_APPLICATION_REJECTED = 'application_rejected';
    const TYPE_MESSAGE_RECEIVED = 'message_received';
    const TYPE_PROJECT_POSTED = 'project_posted';
    const TYPE_PROFILE_VIEWED = 'profile_viewed';
    const TYPE_SYSTEM_UPDATE = 'system_update';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function markAsRead()
    {
        $this->update(['read_at' => now()]);
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeImportant($query)
    {
        return $query->where('is_important', true);
    }
}

