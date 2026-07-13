<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'discount',
        'total_amount',
        'tenant_id',
        'branch_id',
        'package_type',
        'admission_type',
        'category',
        'department_id',
        'bed_type_id',
        'patient_type',
        'validity_days',
        'is_active',
        'status',
    ];

    public function bedType()
    {
        return $this->belongsTo(\App\Models\BedType::class);
    }

    public function priceRules()
    {
        return $this->hasMany(\App\Models\PackagePriceRule::class)->where('is_active', true);
    }

    protected $casts = [
        'discount' => 'float',
        'total_amount' => 'float',
        'is_active' => 'boolean',
    ];

    public function services()
    {
        return $this->hasMany(PackageService::class, 'package_id');
    }

    public function enrollments()
    {
        return $this->hasMany(\App\Models\Package\PackageEnrollment::class, 'package_id');
    }

    /**
     * Convenience: total revenue across all enrollments of this package.
     */
    public function totalRevenue(): float
    {
        return (float) $this->enrollments()->sum('paid_amount');
    }

    /**
     * Convenience: count of active enrollments.
     */
    public function activeEnrollmentCount(): int
    {
        return $this->enrollments()->where('status', 'active')->count();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('package_type', $type);
    }

    protected static function booted(): void
    {
        static::creating(function (self $row) {
            if (empty($row->code)) {
                $prefixes = ['OPD' => 'OPD', 'IPD' => 'IPD', 'OT' => 'OT', 'ICU' => 'ICU', 'CCU' => 'CCU',
                    'NICU' => 'NICU', 'MATERNITY' => 'MAT', 'PATHOLOGY' => 'PATH', 'RADIOLOGY' => 'RAD',
                    'PHARMACY' => 'PH', 'DIAGNOSTIC' => 'DX', 'WELLNESS' => 'WEL', 'CORPORATE' => 'CORP',
                    'PHYSIOTHERAPY' => 'PHY', 'DENTAL' => 'DENT'];
                $pfx = $prefixes[$row->package_type] ?? 'PKG';
                $next = (self::max('id') ?? 0) + 1;
                $row->code = 'PKG-' . $pfx . '-' . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
            }
        });
    }

    public function department()
    {
        return $this->belongsTo(\App\Models\Department::class);
    }

    public function branch()
    {
        return $this->belongsTo(\App\Models\Organization\Branch::class);
    }

    /**
     * Canonical list of supported package categories — drives the dropdown
     * on the unified Master Data → Packages screen.
     */
    public static function categories(): array
    {
        return [
            'OPD'          => 'OPD / Consultation',
            'IPD'          => 'IPD / Admission',
            'OT'           => 'Surgery / OT',
            'ICU'          => 'ICU',
            'CCU'          => 'CCU',
            'NICU'         => 'NICU',
            'MATERNITY'    => 'Maternity',
            'PATHOLOGY'    => 'Pathology / Lab',
            'RADIOLOGY'    => 'Radiology / Imaging',
            'DIAGNOSTIC'   => 'Diagnostic / Health Checkup',
            'PHARMACY'     => 'Pharmacy / Medicine',
            'PHYSIOTHERAPY' => 'Physiotherapy',
            'DENTAL'       => 'Dental',
            'CORPORATE'    => 'Corporate / Annual Plan',
            'WELLNESS'     => 'Wellness / Subscription',
        ];
    }
}
