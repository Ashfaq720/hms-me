<?php

namespace App\Modules\Ambulance\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Ambulance extends Model
{
    protected $table = 'amb_ambulances';
    protected $guarded = [];

    protected $casts = [
        'fitness_expiry'   => 'date',
        'insurance_expiry' => 'date',
        'license_validity' => 'date',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function trips()
    {
        return $this->hasMany(AmbulanceTrip::class, 'ambulance_id');
    }

    public function equipment()
    {
        return $this->hasMany(AmbulanceEquipment::class, 'ambulance_id');
    }

    public function isFitnessExpired(): bool
    {
        return $this->fitness_expiry && $this->fitness_expiry->isPast();
    }

    public function isInsuranceExpired(): bool
    {
        return $this->insurance_expiry && $this->insurance_expiry->isPast();
    }

    public function isExpiringSoon(string $field, int $days = 30): bool
    {
        return $this->{$field} && !$this->{$field}->isPast()
            && $this->{$field}->lte(Carbon::today()->addDays($days));
    }

    // AMB-BR-004: ICU/ALS must have required equipment mapped
    public function requiresEquipmentCheck(): bool
    {
        return in_array($this->type, ['ALS', 'ICU', 'NEONATAL'], true);
    }
}
