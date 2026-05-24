<?php

namespace App\Models\Ot;

use Illuminate\Database\Eloquent\Model;

class OtConsumableUsage extends Model
{
    protected $table = 'ot_consumable_usages';

    protected $fillable = [
        'surgery_schedule_id', 'ot_consumable_id', 'item_name', 'item_code',
        'type', 'quantity', 'unit', 'rate', 'amount', 'is_billed',
        'patient_charge_id', 'inventory_deducted', 'notes', 'recorded_by', 'used_at',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'rate' => 'decimal:2',
        'amount' => 'decimal:2',
        'is_billed' => 'boolean',
        'inventory_deducted' => 'boolean',
        'used_at' => 'datetime',
    ];

    public function schedule()
    {
        return $this->belongsTo(OtSurgerySchedule::class, 'surgery_schedule_id');
    }

    public function consumable()
    {
        return $this->belongsTo(OtConsumable::class, 'ot_consumable_id');
    }

    public function patientCharge()
    {
        return $this->belongsTo(\App\Models\PatientCharge::class, 'patient_charge_id');
    }

    public function recordedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'recorded_by');
    }

    /**
     * Auto-deduct from inventory the first time a usage row is persisted.
     * The flag `inventory_deducted` prevents double-deduction on update/replay.
     */
    protected static function booted(): void
    {
        static::created(function (self $usage) {
            $usage->deductFromInventory();
        });
    }

    public function deductFromInventory(): void
    {
        if ($this->inventory_deducted) return;
        $consumable = $this->consumable;
        if (! $consumable || ! $consumable->inventory_item_id || ! $this->quantity) return;

        $warehouseId = \DB::table('inventory_warehouses')->where('is_active', 1)->value('id');
        if (! $warehouseId) return;

        \DB::table('stock_movements')->insert([
            'inventory_item_id' => $consumable->inventory_item_id,
            'warehouse_id'      => $warehouseId,
            'direction'         => 'out',
            'quantity'          => $this->quantity,
            'unit_cost'         => $this->rate,
            'reason'            => 'OT consumable usage',
            'source_type'       => self::class,
            'source_id'         => $this->id,
            'reference_no'      => 'OT-USE-' . $this->id,
            'remarks'           => 'Auto-deduct from OT consumable usage',
            'performed_by'      => $this->recorded_by,
            'performed_at'      => $this->used_at ?? now(),
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        $this->forceFill(['inventory_deducted' => true])->saveQuietly();
    }
}
