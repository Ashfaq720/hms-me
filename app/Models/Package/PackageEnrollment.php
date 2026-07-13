<?php

namespace App\Models\Package;

use App\Models\Encounter\Encounter;
use App\Models\Package as PackageMaster;
use App\Models\Patient;
use App\Traits\LogPreference;
use Illuminate\Database\Eloquent\Model;

class PackageEnrollment extends Model
{
    use LogPreference;

    protected string $logName = 'package_enrollment';

    protected $fillable = [
        'package_id', 'patient_id', 'encounter_id',
        'enrollment_no', 'start_date', 'end_date',
        'agreed_price', 'paid_amount', 'status', 'notes', 'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'agreed_price' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    public function package()
    {
        return $this->belongsTo(\App\Models\Package::class, 'package_id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function encounter()
    {
        return $this->belongsTo(Encounter::class);
    }

    public function entries()
    {
        return $this->hasMany(PackageConsumptionEntry::class, 'package_enrollment_id');
    }

    protected static function booted(): void
    {
        static::creating(function (self $row) {
            if (empty($row->enrollment_no)) {
                $row->enrollment_no = 'PKG-' . date('Ym') . '-' . str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT);
            }
        });
    }
}
