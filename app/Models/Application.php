<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Application extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'talent_id',
        'project_id',
        'casting_call_id',
        'recruiter_id',
        'cover_letter',
        'message',
        'pitch',
        'audition_video_url',
        'resume_url',
        'attachments',
        'portfolio_links',
        'available_from',
        'available_until',
        'proposed_rate',
        'rate_type',
        'currency',
        'status',
        'audition_status',
        'recruiter_notes',
        'feedback_to_talent',
        'rating',
        'interview_date',
        'interview_location',
        'interview_type',
        'interview_notes',
        'source',
        'referral_code',
        'metadata',
        'is_read',
        'read_at',
        'viewed_at',
        'reviewed_at',
        'shortlisted_at',
        'interview_scheduled_at',
        'accepted_at',
        'rejected_at',
        'responded_at',
        'withdrawn_at',
    ];

    protected $casts = [
        'attachments' => 'array',
        'portfolio_links' => 'array',
        'metadata' => 'array',
        'available_from' => 'date',
        'available_until' => 'date',
        'proposed_rate' => 'decimal:2',
        'rating' => 'integer',
        'is_read' => 'boolean',
        'viewed_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'shortlisted_at' => 'datetime',
        'interview_scheduled_at' => 'datetime',
        'responded_at' => 'datetime',
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
        'withdrawn_at' => 'datetime',
        'interview_date' => 'datetime',
        'read_at' => 'datetime',
    ];

    // Status constants
    public const STATUS_PENDING = 'pending';
    public const STATUS_UNDER_REVIEW = 'under_review';
    public const STATUS_SHORTLISTED = 'shortlisted';
    public const STATUS_INTERVIEW_SCHEDULED = 'interview_scheduled';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_WITHDRAWN = 'withdrawn';

    // Relationships
    public function talent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'talent_id');
    }

    public function recruiter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recruiter_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function castingCall(): BelongsTo
    {
        return $this->belongsTo(CastingCall::class);
    }

    public function notifications(): MorphMany
    {
        return $this->morphMany(Notification::class, 'notifiable');
    }

    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    // Query Scopes
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeShortlisted($query)
    {
        return $query->where('status', self::STATUS_SHORTLISTED);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeForRecruiter($query, $recruiterId)
    {
        return $query->where('recruiter_id', $recruiterId);
    }

    public function scopeByTalent($query, $talentId)
    {
        return $query->where('talent_id', $talentId);
    }

    // Helper Methods
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isShortlisted(): bool
    {
        return $this->status === self::STATUS_SHORTLISTED;
    }

    public function canBeWithdrawn(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_UNDER_REVIEW]);
    }

    public function canBeEdited(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function markAsRead(): void
    {
        if (!$this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }
    }

    public function updateStatus(string $status, ?string $notes = null): bool
    {
        $validStatuses = [
            self::STATUS_PENDING,
            self::STATUS_UNDER_REVIEW,
            self::STATUS_SHORTLISTED,
            self::STATUS_INTERVIEW_SCHEDULED,
            self::STATUS_ACCEPTED,
            self::STATUS_REJECTED,
            self::STATUS_WITHDRAWN,
        ];

        if (!in_array($status, $validStatuses)) {
            return false;
        }

        $updates = ['status' => $status];

        // Set timestamps based on status
        switch ($status) {
            case self::STATUS_UNDER_REVIEW:
                $updates['reviewed_at'] = now();
                break;
            case self::STATUS_SHORTLISTED:
                $updates['shortlisted_at'] = now();
                break;
            case self::STATUS_INTERVIEW_SCHEDULED:
                $updates['interview_scheduled_at'] = now();
                break;
            case self::STATUS_ACCEPTED:
                $updates['accepted_at'] = now();
                $updates['responded_at'] = now();
                break;
            case self::STATUS_REJECTED:
                $updates['rejected_at'] = now();
                $updates['responded_at'] = now();
                break;
            case self::STATUS_WITHDRAWN:
                $updates['withdrawn_at'] = now();
                break;
        }

        if ($notes) {
            $updates['recruiter_notes'] = $notes;
        }

        return $this->update($updates);
    }

    public function accept(?string $feedback = null): bool
    {
        $this->status = self::STATUS_ACCEPTED;
        $this->accepted_at = now();
        $this->responded_at = now();
        
        if ($feedback) {
            $this->feedback_to_talent = $feedback;
        }
        
        return $this->save();
    }

    public function reject(?string $feedback = null): bool
    {
        $this->status = self::STATUS_REJECTED;
        $this->rejected_at = now();
        $this->responded_at = now();
        
        if ($feedback) {
            $this->feedback_to_talent = $feedback;
        }
        
        return $this->save();
    }

    public function withdraw(): bool
    {
        $this->status = self::STATUS_WITHDRAWN;
        $this->withdrawn_at = now();
        
        return $this->save();
    }
}