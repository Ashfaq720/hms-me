<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bed extends Model
{
    protected $fillable = [
        'code',
        'name',
        'bed_no',
        'rent',
        'amenity_charge',
        'nursing_charge',
        'bed_type_id',
        'bed_group_id',
        'room_id',
        'bed_gender',
        'is_reserved',
        'is_active',
        'status',
        'billing_type',
        'has_oxygen_support',
        'notes',
        'default_package_id',
    ];

    protected $casts = [
        'rent'               => 'decimal:2',
        'amenity_charge'     => 'decimal:2',
        'nursing_charge'     => 'decimal:2',
        'is_reserved'        => 'boolean',
        'is_active'          => 'boolean',
        'has_oxygen_support' => 'boolean',
    ];

    /* ───────── Status enum constants — single source of truth for
       seeders, controllers and views. ───────── */
    public const STATUS_AVAILABLE          = 'Available';
    public const STATUS_RESERVED           = 'Reserved';
    public const STATUS_OCCUPIED           = 'Occupied';
    public const STATUS_CLEANING_REQUIRED  = 'Cleaning Required';
    public const STATUS_CLEANING_PROGRESS  = 'Cleaning in Progress';
    public const STATUS_READY              = 'Ready';
    public const STATUS_UNDER_MAINTENANCE  = 'Under Maintenance';
    public const STATUS_BLOCKED            = 'Blocked';
    public const STATUS_ISOLATION_HOLD     = 'Isolation Hold';
    public const STATUS_TRANSFER_PENDING   = 'Transfer Pending';
    public const STATUS_DISCHARGE_PENDING  = 'Discharge Pending';

    public const STATUSES = [
        self::STATUS_AVAILABLE, self::STATUS_RESERVED, self::STATUS_OCCUPIED,
        self::STATUS_CLEANING_REQUIRED, self::STATUS_CLEANING_PROGRESS, self::STATUS_READY,
        self::STATUS_UNDER_MAINTENANCE, self::STATUS_BLOCKED, self::STATUS_ISOLATION_HOLD,
        self::STATUS_TRANSFER_PENDING, self::STATUS_DISCHARGE_PENDING,
    ];

    public const STATUSES_RESERVED_LIKE = [
        self::STATUS_RESERVED, self::STATUS_OCCUPIED,
        self::STATUS_CLEANING_REQUIRED, self::STATUS_CLEANING_PROGRESS,
        self::STATUS_UNDER_MAINTENANCE, self::STATUS_BLOCKED,
        self::STATUS_ISOLATION_HOLD, self::STATUS_TRANSFER_PENDING,
        self::STATUS_DISCHARGE_PENDING,
    ];

    public const GENDERS = ['Male', 'Female', 'Child', 'Neonatal', 'Any'];

    public const BILLING_TYPES = [
        'Hourly', 'Daily', 'Package', 'Free', 'Insurance', 'Upgrade', 'Downgrade',
    ];

    protected static function boot()
    {
        parent::boot();
        // Keep legacy is_reserved boolean in sync with the richer status enum
        static::saving(function (self $bed) {
            if ($bed->isDirty('status')) {
                $bed->is_reserved = in_array($bed->status, self::STATUSES_RESERVED_LIKE, true);
            } elseif ($bed->isDirty('is_reserved')) {
                if ($bed->is_reserved && in_array($bed->status, [self::STATUS_AVAILABLE, self::STATUS_READY, null, ''], true)) {
                    $bed->status = self::STATUS_OCCUPIED;
                } elseif (! $bed->is_reserved && in_array($bed->status, self::STATUSES_RESERVED_LIKE, true)) {
                    $bed->status = self::STATUS_AVAILABLE;
                }
            }
        });
    }

    public function bedType()
    {
        return $this->belongsTo(BedType::class);
    }

    public function bedGroup()
    {
        return $this->belongsTo(BedGroup::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function defaultPackage()
    {
        return $this->belongsTo(Package::class, 'default_package_id');
    }

    public function packageLinks()
    {
        return $this->hasMany(PackageBedLink::class);
    }

    public function totalDailyRate(): float
    {
        return (float) ($this->rent ?? 0)
            + (float) ($this->amenity_charge ?? 0)
            + (float) ($this->nursing_charge ?? 0)
            + (float) optional($this->room)->room_rent;
    }
}
