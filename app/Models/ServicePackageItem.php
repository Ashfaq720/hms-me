<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServicePackageItem extends Model
{
    protected $table = 'service_package_items';

    public const CATEGORIES = [
        'Bed', 'Doctor Visit', 'Nursing', 'OT',
        'Investigation', 'Medicine', 'Consumable',
        'Equipment', 'Procedure', 'Other',
    ];

    /**
     * Allowed master_type slugs. When set, master_id points to that
     * master table's primary key. resolveMaster() returns the actual
     * model record.
     */
    public const MASTER_TYPES = [
        'surgery_type', 'surgery_category', 'consumable', 'equipment',
        'anesthesia_type', 'charge', 'medicine', 'bed_type', 'service',
        'lab_investigation',
    ];

    protected $fillable = [
        'service_package_id', 'item_category', 'master_type', 'master_id',
        'item_name', 'included_qty', 'unit', 'sort_order', 'notes',
    ];

    protected $casts = [
        'included_qty' => 'decimal:2',
        'sort_order'   => 'integer',
        'master_id'    => 'integer',
    ];

    public function package()
    {
        return $this->belongsTo(ServicePackage::class, 'service_package_id');
    }

    /**
     * Resolve the linked master record by its slug type. Mirrors the
     * resolveEncounter() pattern used in OtSurgeryRequest — keeps the
     * `master_type` column a short readable slug instead of a class name.
     */
    public function resolveMaster()
    {
        if (! $this->master_type || ! $this->master_id) {
            return null;
        }

        $map = [
            'surgery_type'     => \App\Models\Ot\OtSurgeryType::class,
            'surgery_category' => \App\Models\Ot\OtSurgeryCategory::class,
            'consumable'       => \App\Models\Ot\OtConsumable::class,
            'equipment'        => \App\Models\Ot\OtEquipment::class,
            'anesthesia_type'  => \App\Models\Ot\OtAnesthesiaType::class,
            'charge'           => \App\Models\Charges\Charge::class,
            'medicine'         => class_exists(\App\Models\Pharmacy\Medicine::class)
                                    ? \App\Models\Pharmacy\Medicine::class : null,
            'bed_type'          => \App\Models\BedType::class,
            'service'           => class_exists(\App\Models\Service::class)
                                    ? \App\Models\Service::class : null,
            'lab_investigation' => \App\Models\LabInvestigation::class,
        ];

        $class = $map[$this->master_type] ?? null;
        if (! $class || ! class_exists($class)) return null;
        return $class::find($this->master_id);
    }
}
