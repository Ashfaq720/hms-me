<?php

namespace App\Models\Ot;

use Illuminate\Database\Eloquent\Model;

class OtStockMovement extends Model
{
    protected $table = 'ot_stock_movements';

    protected $fillable = [
        'ot_consumable_id', 'surgery_schedule_id', 'consumable_usage_id',
        'movement_type', 'quantity', 'balance_after', 'notes', 'performed_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function consumable()
    {
        return $this->belongsTo(OtConsumable::class, 'ot_consumable_id');
    }

    public function schedule()
    {
        return $this->belongsTo(OtSurgerySchedule::class, 'surgery_schedule_id');
    }

    public function consumableUsage()
    {
        return $this->belongsTo(OtConsumableUsage::class, 'consumable_usage_id');
    }

    public function performedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'performed_by');
    }
}
