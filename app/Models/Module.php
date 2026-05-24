<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Models\Permission;

class Module extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get permissions for this module
     */
    public function permissions(): HasMany
    {
        return $this->hasMany(Permission::class, 'module_id');
    }

    /**
     * Scope for active modules
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordered modules
     */
    public function scopeOrdered($query, string $direction = 'asc')
    {
        return $query->orderBy('sort_order', $direction)->orderBy('name', 'asc');
    }
}
