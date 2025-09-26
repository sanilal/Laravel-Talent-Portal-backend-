<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Education extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'talent_profile_id',
        'institution',
        'degree',
        'field_of_study',
        'start_date',
        'end_date',
        'is_current',
        'grade',
        'description',
        'activities',
        'location',
        'is_featured',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
        'is_featured' => 'boolean',
        'activities' => 'array',
    ];

    public function talentProfile()
    {
        return $this->belongsTo(TalentProfile::class);
    }

    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }
}
