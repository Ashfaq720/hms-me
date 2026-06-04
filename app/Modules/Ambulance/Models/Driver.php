<?php

namespace App\Modules\Ambulance\Models;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    protected $table = 'amb_drivers';
    protected $guarded = [];

    protected $casts = [
        'license_expiry' => 'date',
    ];

    public function trips()
    {
        return $this->hasMany(AmbulanceTrip::class, 'driver_id');
    }

    public function isAvailable(): bool
    {
        return $this->status === 'ACTIVE' && $this->availability === 'AVAILABLE';
    }

    public function isLicenseExpired(): bool
    {
        return $this->license_expiry && $this->license_expiry->isPast();
    }

    public function isLicenseExpiringSoon(int $days = 30): bool
    {
        return $this->license_expiry
            && !$this->license_expiry->isPast()
            && $this->license_expiry->diffInDays(now()) <= $days;
    }
}
