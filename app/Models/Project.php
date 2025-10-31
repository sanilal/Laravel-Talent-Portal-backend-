<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Project extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'recruiter_profile_id',
        'posted_by',
        'primary_category_id', // FIXED: Was 'category_id'
        'title',
        'slug',
        'description',
        'requirements',
        'responsibilities',
        'deliverables',
        'project_type',
        'work_type',
        'budget_type',
        'budget_min',
        'budget_max',
        'budget_currency',
        'duration',
        'start_date',
        'end_date',
        'deadline',
        'location',
        'experience_level',
        'skills_required',
        'positions_available',
        'positions_filled',
        'status',
        'priority',
        'tags',
        'application_deadline',
        'application_instructions',
        'external_url',
        'contact_info',
        'is_featured',
        'is_urgent',
        'views_count',
        'applications_count',
        'visibility',
        'approval_status',
        'approved_at',
        'approved_by',
        'rejection_reason',
        'metadata',
        'requirements_embedding',
        'required_skills_embedding',
        'embeddings_generated_at',
        'embedding_model',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'deadline' => 'datetime',
        'application_deadline' => 'datetime',
        'approved_at' => 'datetime',
        'is_featured' => 'boolean',
        'is_urgent' => 'boolean',
        'budget_min' => 'decimal:2',
        'budget_max' => 'decimal:2',
        'tags' => 'array',
        'requirements' => 'array',
        'responsibilities' => 'array',
        'deliverables' => 'array',
        'skills_required' => 'array',
        'location' => 'array',
        'contact_info' => 'array',
        'metadata' => 'array',
        'views_count' => 'integer',
        'applications_count' => 'integer',
        'positions_available' => 'integer',
        'positions_filled' => 'integer',
        'requirements_embedding' => 'array',
        'required_skills_embedding' => 'array',
        'embeddings_generated_at' => 'datetime',
    ];

    /**
     * Bootstrap the model.
     */
    protected static function boot()
    {
        parent::boot();
        
        // Auto-generate slug when creating a project
        static::creating(function ($project) {
            if (empty($project->slug)) {
                $project->slug = Str::slug($project->title);
                
                // Ensure slug is unique
                $originalSlug = $project->slug;
                $counter = 1;
                
                while (static::where('slug', $project->slug)->exists()) {
                    $project->slug = $originalSlug . '-' . $counter;
                    $counter++;
                }
            }
        });
        
        // Auto-update slug when title changes
        static::updating(function ($project) {
            if ($project->isDirty('title') && empty($project->slug)) {
                $project->slug = Str::slug($project->title);
                
                // Ensure slug is unique
                $originalSlug = $project->slug;
                $counter = 1;
                
                while (static::where('slug', $project->slug)
                            ->where('id', '!=', $project->id)
                            ->exists()) {
                    $project->slug = $originalSlug . '-' . $counter;
                    $counter++;
                }
            }
        });
    }

    /**
     * Project type constants
     */
    const TYPE_ONE_TIME = 'one_time';
    const TYPE_RECURRING = 'recurring';
    const TYPE_CONTRACT = 'contract';
    const TYPE_FULL_TIME = 'full_time';
    const TYPE_PART_TIME = 'part_time';

    const PROJECT_TYPES = [
        self::TYPE_ONE_TIME,
        self::TYPE_RECURRING,
        self::TYPE_CONTRACT,
        self::TYPE_FULL_TIME,
        self::TYPE_PART_TIME,
    ];

    /**
     * Budget type constants
     */
    const BUDGET_FIXED = 'fixed';
    const BUDGET_HOURLY = 'hourly';
    const BUDGET_DAILY = 'daily';
    const BUDGET_NEGOTIABLE = 'negotiable';

    const BUDGET_TYPES = [
        self::BUDGET_FIXED,
        self::BUDGET_HOURLY,
        self::BUDGET_DAILY,
        self::BUDGET_NEGOTIABLE,
    ];

    /**
     * Status constants
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_OPEN = 'published';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_PAUSED = 'expired';

    const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_OPEN,
        self::STATUS_IN_PROGRESS,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
        self::STATUS_PAUSED,
    ];

    /**
     * Experience level constants
     */
    const EXPERIENCE_ENTRY = 'entry';
    const EXPERIENCE_INTERMEDIATE = 'intermediate';
    const EXPERIENCE_ADVANCED = 'advanced';
    const EXPERIENCE_EXPERT = 'expert';

    const EXPERIENCE_LEVELS = [
        self::EXPERIENCE_ENTRY,
        self::EXPERIENCE_INTERMEDIATE,
        self::EXPERIENCE_ADVANCED,
        self::EXPERIENCE_EXPERT,
    ];

    /**
     * Priority constants
     */
    const PRIORITY_LOW = 'low';
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    const PRIORITIES = [
        self::PRIORITY_LOW,
        self::PRIORITY_NORMAL,
        self::PRIORITY_HIGH,
        self::PRIORITY_URGENT,
    ];

    /**
     * Visibility constants
     */
    const VISIBILITY_PUBLIC = 'public';
    const VISIBILITY_PRIVATE = 'private';
    const VISIBILITY_INVITED_ONLY = 'invited_only';

    const VISIBILITIES = [
        self::VISIBILITY_PUBLIC,
        self::VISIBILITY_PRIVATE,
        self::VISIBILITY_INVITED_ONLY,
    ];

    /**
     * Approval status constants
     */
    const APPROVAL_PENDING = 'pending';
    const APPROVAL_APPROVED = 'approved';
    const APPROVAL_REJECTED = 'rejected';

    const APPROVAL_STATUSES = [
        self::APPROVAL_PENDING,
        self::APPROVAL_APPROVED,
        self::APPROVAL_REJECTED,
    ];

    /**
     * Get the recruiter profile that owns this project.
     */
    public function recruiterProfile()
    {
        return $this->belongsTo(RecruiterProfile::class);
    }

    public function recruiter()
{
    return $this->belongsTo(User::class, 'recruiter_id');
}

    /**
     * Get the category this project belongs to.
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'primary_category_id'); // FIXED: Added foreign key
    }

    /**
     * Get all applications for this project.
     */
    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    /**
     * Get pending applications.
     */
    public function pendingApplications()
    {
        return $this->applications()->where('status', Application::STATUS_PENDING);
    }

    /**
     * Get accepted applications.
     */
    public function acceptedApplications()
    {
        return $this->applications()->where('status', Application::STATUS_ACCEPTED);
    }

    /**
     * Get the skills required for this project.
     */
    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'project_skills')
                    ->withPivot(['level_required', 'is_required'])
                    ->withTimestamps();
    }

    /**
     * Get all media files for this project.
     */
    public function media()
    {
        return $this->hasMany(Media::class, 'mediable_id')
                    ->where('mediable_type', self::class);
    }

    /**
     * Check if project is open for applications.
     */
    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN &&
               $this->approval_status === self::APPROVAL_APPROVED &&
               (!$this->application_deadline || $this->application_deadline->isFuture());
    }

    /**
     * Check if application deadline has passed.
     */
    public function isExpired(): bool
    {
        return $this->application_deadline && $this->application_deadline->isPast();
    }

    /**
     * Check if project is fully staffed.
     */
    public function isFullyStaffed(): bool
    {
        return $this->positions_filled >= $this->positions_available;
    }

    /**
     * Get available positions count.
     */
    public function getAvailablePositionsAttribute(): int
    {
        return max(0, $this->positions_available - $this->positions_filled);
    }

    /**
     * Get budget range display.
     */
    public function getBudgetDisplayAttribute(): string
    {
        if ($this->budget_type === self::BUDGET_NEGOTIABLE) {
            return 'Negotiable';
        }

        $currency = $this->budget_currency ?? 'USD';
        $symbol = $this->getCurrencySymbol($currency);

        if ($this->budget_min && $this->budget_max) {
            return "{$symbol}{$this->budget_min} - {$symbol}{$this->budget_max}";
        } elseif ($this->budget_min) {
            return "From {$symbol}{$this->budget_min}";
        } elseif ($this->budget_max) {
            return "Up to {$symbol}{$this->budget_max}";
        }

        return 'Not specified';
    }

    /**
     * Get currency symbol.
     */
    private function getCurrencySymbol(string $currency): string
    {
        return match($currency) {
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'CAD' => 'C$',
            'AUD' => 'A$',
            'AED' => 'د.إ',
            'SAR' => 'ر.س',
            'QAR' => 'ر.ق',
            'KWD' => 'د.ك',
            'BHD' => 'د.ب',
            'OMR' => 'ر.ع',
            'JPY' => '¥',
            'CNY' => '¥',
            'INR' => '₹',
            'SGD' => 'S$',
            'CHF' => 'CHF',
            'SEK' => 'kr',
            'NOK' => 'kr',
            'DKK' => 'kr',
            'PLN' => 'zł',
            'CZK' => 'Kč',
            'HUF' => 'Ft',
            'RUB' => '₽',
            'BRL' => 'R$',
            'MXN' => '$',
            'ZAR' => 'R',
            'TRY' => '₺',
            'KRW' => '₩',
            'THB' => '฿',
            'MYR' => 'RM',
            'PHP' => '₱',
            'IDR' => 'Rp',
            'VND' => '₫',
            'EGP' => 'E£',
            'JOD' => 'د.ا',
            'LBP' => 'ل.ل',
            'IQD' => 'ع.د',
            'SYP' => 'ل.س',
            'YER' => 'ر.ي',
            'MAD' => 'د.م',
            'TND' => 'د.ت',
            'DZD' => 'د.ج',
            'LYD' => 'د.ل',
            'SDG' => 'ج.س',
            default => $currency . ' '
        };
    }

    /**
     * Increment views count.
     */
    public function incrementViews()
    {
        $this->increment('views_count');
    }

    /**
     * Increment applications count.
     */
    public function incrementApplications()
    {
        $this->increment('applications_count');
    }

    /**
     * Decrement applications count.
     */
    public function decrementApplications()
    {
        $this->decrement('applications_count');
    }

    /**
     * Scope for open projects.
     */
    public function scopeOpen($query)
    {
        return $query->where('status', self::STATUS_OPEN)
                     ->where('approval_status', self::APPROVAL_APPROVED);
    }

    /**
     * Scope for active projects (open and accepting applications).
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_OPEN)
                     ->where('approval_status', self::APPROVAL_APPROVED)
                     ->where(function ($q) {
                         $q->whereNull('application_deadline')
                           ->orWhere('application_deadline', '>', now());
                     });
    }

    /**
     * Scope for featured projects.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope for urgent projects.
     */
    public function scopeUrgent($query)
    {
        return $query->where('is_urgent', true);
    }

    /**
     * Scope for projects by category.
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('primary_category_id', $categoryId); // FIXED: Was 'category_id'
    }

    /**
     * Scope for projects by budget range.
     */
    public function scopeBudgetRange($query, float $min, float $max)
    {
        return $query->where(function ($q) use ($min, $max) {
            $q->whereBetween('budget_min', [$min, $max])
              ->orWhereBetween('budget_max', [$min, $max])
              ->orWhere(function ($qq) use ($min, $max) {
                  $qq->where('budget_min', '<=', $min)
                     ->where('budget_max', '>=', $max);
              });
        });
    }

    /**
     * Scope for recent projects.
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}