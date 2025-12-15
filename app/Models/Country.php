<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'country_name',
        'country_code',
        'country_code_alpha3',
        'dialing_code',
        'emoji',
        'currency',
        'currency_symbol',
        'flag',
        'numeric_code',
        'is_active',
        'sort_order',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'numeric_code' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Get the states for the country.
     */
    public function states(): HasMany
    {
        return $this->hasMany(State::class);
    }

    /**
     * Get the users from this country.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Scope a query to only include active countries.
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

    /**
     * Scope a query to order by country name.
     */
    public function scopeOrderByName($query)
    {
        return $query->orderBy('country_name', 'asc');
    }
}