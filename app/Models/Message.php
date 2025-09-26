<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'application_id',
        'project_id',
        'subject',
        'body',
        'message_type',
        'status',
        'read_at',
        'replied_at',
        'attachments',
        'metadata',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'replied_at' => 'datetime',
        'attachments' => 'array',
        'metadata' => 'array',
    ];

    const TYPE_DIRECT = 'direct';
    const TYPE_APPLICATION = 'application';
    const TYPE_SYSTEM = 'system';

    const STATUS_SENT = 'sent';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_READ = 'read';

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function markAsRead()
    {
        $this->update(['status' => self::STATUS_READ, 'read_at' => now()]);
    }

    public function scopeUnread($query)
    {
        return $query->where('status', '!=', self::STATUS_READ);
    }
}