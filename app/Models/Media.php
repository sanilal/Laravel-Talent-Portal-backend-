<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Media extends Model
{
    use HasFactory, HasUuids;
    // REMOVED: SoftDeletes

    protected $fillable = [
        'model_type',        
        'model_id',          
        'uuid',
        'collection_name',
        'name',
        'file_name',
        'mime_type',
        'disk',
        'conversions_disk',
        'size',
        'manipulations',
        'custom_properties',
        'generated_conversions',
        'responsive_images',
        'order_column',
        'alt_text',
        'caption',
        'metadata',
        'is_public',
    ];

    protected $casts = [
        'manipulations' => 'array',
        'custom_properties' => 'array',
        'generated_conversions' => 'array',
        'responsive_images' => 'array',
        'metadata' => 'array',
        'is_public' => 'boolean',
        'size' => 'integer',
        'order_column' => 'integer',
    ];

    const TYPE_IMAGE = 'image';
    const TYPE_VIDEO = 'video';
    const TYPE_AUDIO = 'audio';
    const TYPE_DOCUMENT = 'document';

    public function model()
    {
        return $this->morphTo();
    }

    // Keep for backwards compatibility if needed
    public function mediable()
    {
        return $this->model();
    }

    public function scopeImages($query)
    {
        return $query->where('mime_type', 'like', 'image/%');
    }

    public function scopeVideos($query)
    {
        return $query->where('mime_type', 'like', 'video/%');
    }

    public function getFullUrlAttribute()
    {
        if ($this->disk === 'public') {
            return url('storage/' . $this->file_name);
        }
        return $this->file_name;
    }
}