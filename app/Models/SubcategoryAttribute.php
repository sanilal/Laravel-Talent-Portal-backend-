<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubcategoryAttribute extends Model
{
    use HasFactory, HasUuids;

    // Field type constants
    const TYPE_TEXT = 'text';
    const TYPE_TEXTAREA = 'textarea';
    const TYPE_NUMBER = 'number';
    const TYPE_SELECT = 'select';
    const TYPE_MULTISELECT = 'multiselect';
    const TYPE_RADIO = 'radio';
    const TYPE_CHECKBOX = 'checkbox';
    const TYPE_DATE = 'date';
    const TYPE_FILE = 'file';
    const TYPE_URL = 'url';
    const TYPE_EMAIL = 'email';
    const TYPE_PHONE = 'phone';
    const TYPE_COLOR = 'color';
    const TYPE_RANGE = 'range';

    protected $fillable = [
        'subcategory_id',
        'field_name',
        'field_label',
        'field_type',
        'field_options',
        'field_description',
        'field_placeholder',
        'default_value',
        'is_required',
        'is_searchable',
        'is_public',
        'validation_rules',
        'min_value',
        'max_value',
        'min_length',
        'max_length',
        'unit',
        'sort_order',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'field_options' => 'array',
        'is_required' => 'boolean',
        'is_searchable' => 'boolean',
        'is_public' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'min_value' => 'integer',
        'max_value' => 'integer',
        'min_length' => 'integer',
        'max_length' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Get the subcategory that owns the attribute.
     */
    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Subcategory::class);
    }

    /**
     * Get all talent skill attribute values.
     */
    public function talentSkillAttributes(): HasMany
    {
        return $this->hasMany(TalentSkillAttribute::class, 'attribute_id');
    }

    /**
     * Scope a query to only include active attributes.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include required attributes.
     */
    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    /**
     * Scope a query to only include searchable attributes.
     */
    public function scopeSearchable($query)
    {
        return $query->where('is_searchable', true);
    }

    /**
     * Scope a query to only include public attributes.
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope a query to order by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc');
    }

    /**
     * Get the validation rules for this attribute.
     */
    public function getValidationRulesArray(): array
    {
        $rules = [];

        if ($this->is_required) {
            $rules[] = 'required';
        } else {
            $rules[] = 'nullable';
        }

        // Add type-specific validation
        switch ($this->field_type) {
            case self::TYPE_NUMBER:
            case self::TYPE_RANGE:
                $rules[] = 'numeric';
                if ($this->min_value !== null) {
                    $rules[] = "min:{$this->min_value}";
                }
                if ($this->max_value !== null) {
                    $rules[] = "max:{$this->max_value}";
                }
                break;

            case self::TYPE_TEXT:
                $rules[] = 'string';
                if ($this->min_length !== null) {
                    $rules[] = "min:{$this->min_length}";
                }
                if ($this->max_length !== null) {
                    $rules[] = "max:{$this->max_length}";
                }
                break;

            case self::TYPE_TEXTAREA:
                $rules[] = 'string';
                if ($this->max_length !== null) {
                    $rules[] = "max:{$this->max_length}";
                }
                break;

            case self::TYPE_EMAIL:
                $rules[] = 'email';
                break;

            case self::TYPE_URL:
                $rules[] = 'url';
                break;

            case self::TYPE_DATE:
                $rules[] = 'date';
                break;

            case self::TYPE_SELECT:
            case self::TYPE_RADIO:
                if ($this->field_options) {
                    $options = array_column($this->field_options, 'value');
                    $rules[] = 'in:' . implode(',', $options);
                }
                break;

            case self::TYPE_MULTISELECT:
            case self::TYPE_CHECKBOX:
                $rules[] = 'array';
                if ($this->field_options) {
                    $options = array_column($this->field_options, 'value');
                    $rules[] = 'in:' . implode(',', $options);
                }
                break;

            case self::TYPE_FILE:
                $rules[] = 'file';
                break;
        }

        // Add custom validation rules if provided
        if ($this->validation_rules) {
            $customRules = explode('|', $this->validation_rules);
            $rules = array_merge($rules, $customRules);
        }

        return $rules;
    }
}