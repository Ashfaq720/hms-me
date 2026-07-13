<?php

namespace App\Models\Insurance;

use App\Traits\LogPreference;
use Illuminate\Database\Eloquent\Model;

class Payer extends Model
{
    use LogPreference;

    protected string $logName = 'payer';

    protected $fillable = [
        'organization_id', 'code', 'name', 'type',
        'contact_person', 'phone', 'email', 'address',
        'default_discount_percent', 'pre_auth_required', 'is_active',
    ];

    protected $casts = [
        'default_discount_percent' => 'decimal:2',
        'pre_auth_required' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function policies()
    {
        return $this->hasMany(InsurancePolicy::class);
    }

    public function claims()
    {
        return $this->hasMany(Claim::class);
    }
}
