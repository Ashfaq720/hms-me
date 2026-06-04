<?php

namespace App\Modules\Ambulance\Models;

use Illuminate\Database\Eloquent\Model;

class Paramedic extends Model
{
    protected $table = 'amb_paramedics';  // Specifies the table name
    protected $guarded = [];  // Mass assignment protection

    protected $casts = [
        'cert_expiry' => 'date',
    ];

    public function trips()
    {
        return $this->hasMany(AmbulanceTrip::class, 'paramedic_id');
    }

    public function isAvailable(): bool
    {
        return $this->status === 'ACTIVE' && $this->availability === 'AVAILABLE';
    }

    public function isCertExpired(): bool
    {
        return $this->cert_expiry && $this->cert_expiry->isPast();
    }

    public function isCertExpiringSoon(int $days = 30): bool
    {
        return $this->cert_expiry
            && !$this->cert_expiry->isPast()
            && $this->cert_expiry->diffInDays(now()) <= $days;
    }
}
