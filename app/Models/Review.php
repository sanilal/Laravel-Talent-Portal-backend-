<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'reviewer_id',
        'reviewee_id',
        'project_id',
        'application_id',
        'rating',
        'title',
        'comment',
        'pros',
        'cons',
        'would_recommend',
        'work_quality',
        'communication',
        'deadline_adherence',
        'professionalism',
        'is_public',
        'is_featured',
        'status',
        'metadata',
    ];

    protected $casts = [
        'rating' => 'decimal:1',
        'work_quality' => 'integer',
        'communication' => 'integer',
        'deadline_adherence' => 'integer',
        'professionalism' => 'integer',
        'would_recommend' => 'boolean',
        'is_public' => 'boolean',
        'is_featured' => 'boolean',
        'pros' => 'array',
        'cons' => 'array',
        'metadata' => 'array',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_PUBLISHED = 'published';
    const STATUS_HIDDEN = 'hidden';

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function reviewee()
    {
        return $this->belongsTo(User::class, 'reviewee_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopePositive($query)
    {
        return $query->where('rating', '>=', 4);
    }
}
