<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Category extends Model
{
    use HasUuids;

    protected $fillable = ['name', 'slug', 'description'];

    public function talents()
    {
        return $this->hasMany(User::class)->where('role', 'talent');
    }
}