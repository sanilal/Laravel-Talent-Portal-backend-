<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CastingCallRequirement extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'casting_call_id',
        'gender',
        'age_group',
        'skin_tone',
        'height',
        'subcategory_id',
        'role_name',
        'role_description',
        'display_order',
    ];

    protected $casts = [
        'display_order' => 'integer',
    ];

    /**
     * Get the casting call this requirement belongs to
     */
    public function castingCall(): BelongsTo
    {
        return $this->belongsTo(CastingCall::class);
    }

    /**
     * Get the subcategory (Actor, Actress, Model, etc.)
     */
    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Subcategory::class);
    }

    /**
     * Scope to order by display order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order');
    }
}