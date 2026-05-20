<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    protected $table = 'roles';

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
        'is_system' => 'boolean',
        'priority' => 'integer',
    ];

    /**
     * Scope a query to only include active roles.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
