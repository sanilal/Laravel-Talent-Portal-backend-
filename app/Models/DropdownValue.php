<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DropdownValue extends Model
{
    use HasFactory, HasUuids;

    // Dropdown type constants
    const TYPE_HEIGHT = 1;
    const TYPE_SKIN_TONE = 2;
    const TYPE_WEIGHT = 3;
    const TYPE_AGE_RANGE = 4;
    const TYPE_VEHICLE = 5;
    const TYPE_SERVICE = 6;
    const TYPE_EVENT = 7;
    const TYPE_BUDGET = 8;
    const TYPE_EYE_COLOR = 9;
    const TYPE_HAIR_COLOR = 10;
    const TYPE_BODY_TYPE = 11;
    const TYPE_VOCAL_RANGE = 12;
    const TYPE_EXPERIENCE_LEVEL = 13;
    const TYPE_LANGUAGE_PROFICIENCY = 14;
    const TYPE_GENDER = 15;

    protected $fillable = [
        'type',
        'value',
        'value_secondary',
        'code',
        'description',
        'sort_order',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'type' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Get the type name for a given type code.
     */
    public static function getTypeName(int $type): string
    {
        return match ($type) {
            self::TYPE_HEIGHT => 'Height',
            self::TYPE_SKIN_TONE => 'Skin Tone',
            self::TYPE_WEIGHT => 'Weight',
            self::TYPE_AGE_RANGE => 'Age Range',
            self::TYPE_VEHICLE => 'Vehicle Type',
            self::TYPE_SERVICE => 'Service Type',
            self::TYPE_EVENT => 'Event Type',
            self::TYPE_BUDGET => 'Budget Range',
            self::TYPE_EYE_COLOR => 'Eye Color',
            self::TYPE_HAIR_COLOR => 'Hair Color',
            self::TYPE_BODY_TYPE => 'Body Type',
            self::TYPE_VOCAL_RANGE => 'Vocal Range',
            self::TYPE_EXPERIENCE_LEVEL => 'Experience Level',
            self::TYPE_LANGUAGE_PROFICIENCY => 'Language Proficiency',
            self::TYPE_GENDER => 'Gender',
            default => 'Unknown',
        };
    }

    /**
     * Scope a query to only include values of a specific type.
     */
    public function scopeOfType($query, int $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to only include active values.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to order by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc');
    }
}