<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Experience extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'talent_profile_id',
        'title',
        'company',
        'location',
        'employment_type',
        'start_date',
        'end_date',
        'is_current',
        'description',
        'achievements',
        'skills_used',
        'media_links',
        'is_featured',
        'sort_order',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
        'is_featured' => 'boolean',
        'achievements' => 'array',
        'skills_used' => 'array',
        'media_links' => 'array',
    ];

    const TYPE_FULL_TIME = 'full_time';
    const TYPE_PART_TIME = 'part_time';
    const TYPE_CONTRACT = 'contract';
    const TYPE_FREELANCE = 'freelance';

    public function talentProfile()
    {
        return $this->belongsTo(TalentProfile::class);
    }

    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }
}

