<?php

namespace App\Models\Organization;

use App\Traits\LogPreference;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use SoftDeletes, LogPreference;

    protected string $logName = 'organization';

    protected $fillable = [
        'code', 'name', 'legal_name', 'logo_path',
        'contact_email', 'contact_phone', 'tax_number',
        'country', 'timezone', 'default_currency',
        'is_active', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }
}
