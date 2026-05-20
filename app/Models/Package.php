<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $fillable = [
        'name',
        'description',
        'discount',
        'total_amount',
        'tenant_id',
    ];

    protected $casts = [
        'discount' => 'float',
        'total_amount' => 'float',
    ];

    public function services()
    {
        return $this->hasMany(PackageService::class, 'package_id');
    }
}
