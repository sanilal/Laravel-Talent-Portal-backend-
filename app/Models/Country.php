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
     * Append camelCase attributes for API responses
     * This ensures both snake_case and camelCase are available
     */
    protected $appends = [
        'countryName',
        'countryCode',
    ];

    /**
     * Get country name in camelCase (for API compatibility)
     * Maps database column 'country_name' to 'countryName'
     */
    public function getCountryNameAttribute()
    {
        return $this->attributes['country_name'] ?? null;
    }

    /**
     * Get country code in camelCase (for API compatibility)
     * Maps database column 'country_code' to 'countryCode'
     */
    public function getCountryCodeAttribute()
    {
        return $this->attributes['country_code'] ?? null;
    }

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