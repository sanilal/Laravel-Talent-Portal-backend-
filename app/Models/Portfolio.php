<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Portfolio extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'talent_profile_id',
        'title',
        'description',
        'project_type',
        'role',
        'client',
        'completion_date',
        'duration',
        'budget',
        'currency',
        'technologies_used',
        'challenges',
        'results',
        'media_urls',
        'external_links',
        'is_featured',
        'is_public',
        'sort_order',
        'metadata',
    ];

    protected $casts = [
        'completion_date' => 'date',
        'budget' => 'decimal:2',
        'is_featured' => 'boolean',
        'is_public' => 'boolean',
        'technologies_used' => 'array',
        'challenges' => 'array',
        'results' => 'array',
        'media_urls' => 'array',
        'external_links' => 'array',
        'metadata' => 'array',
    ];

    public function talentProfile()
    {
        return $this->belongsTo(TalentProfile::class);
    }

    public function media()
    {
        return $this->hasMany(Media::class, 'mediable_id')
                    ->where('mediable_type', self::class);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }
}

