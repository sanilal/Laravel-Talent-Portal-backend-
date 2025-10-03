<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Message extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'sender_id',
        'recipient_id', // Changed from receiver_id
        'application_id',
        'project_id',
        'parent_id',
        'subject',
        'body',
        'message_type',
        'is_read',
        'read_at',
        'is_important',
        'is_archived',
        'attachments',
        'metadata',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'attachments' => 'array',
        'metadata' => 'array',
        'is_read' => 'boolean',
        'is_important' => 'boolean',
        'is_archived' => 'boolean',
    ];

    const TYPE_DIRECT = 'direct';
    const TYPE_APPLICATION = 'application';
    const TYPE_SYSTEM = 'system';

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    // Alias for backward compatibility
    public function receiver()
    {
        return $this->recipient();
    }

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function parent()
    {
        return $this->belongsTo(Message::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(Message::class, 'parent_id');
    }

    public function markAsRead()
    {
        $this->update(['is_read' => true, 'read_at' => now()]);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeImportant($query)
    {
        return $query->where('is_important', true);
    }
}