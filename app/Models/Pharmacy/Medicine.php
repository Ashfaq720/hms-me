<?php

namespace App\Models\Pharmacy;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medicine extends Model
{
    use HasFactory;

    protected $fillable = [
        'medicine_name',
        'medicine_category_id',
        'company_id',
        'medical_group_id',
        'medicine_unit_id',
        'medicine_composition',
        'min_level',
        'reorder_level',
        'tax',
        'box_packing',
        'vat_ac',
        'rack_number',
        'note',
        'photo',
        'available_qty',
        'status',
        // Phase 4: unified stock-ledger link
        'inventory_item_id',
    ];

    public function inventoryItem()
    {
        return $this->belongsTo(\App\Models\Inventory\InventoryItem::class, 'inventory_item_id');
    }

    public function category()
    {
        return $this->belongsTo(MedicineCategory::class, 'medicine_category_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function medicalGroup()
    {
        return $this->belongsTo(MedicalGroup::class, 'medical_group_id');
    }

    public function unit()
    {
        return $this->belongsTo(MedicineUnit::class, 'medicine_unit_id');
    }

    public function batches()
    {
        return $this->hasMany(MedicineBatch::class);
    }

    /**
     * Live stock from the unified inventory ledger.
     * Reads inventory_item_batches.current_qty summed across all warehouses
     * for the linked inventory_items row. Falls back to the legacy
     * medicines.available_qty when no inventory linkage exists.
     */
    public function getLiveStockAttribute(): float
    {
        if (! $this->inventory_item_id) {
            return (float) ($this->available_qty ?? 0);
        }
        return (float) \Illuminate\Support\Facades\DB::table('inventory_item_batches')
            ->where('inventory_item_id', $this->inventory_item_id)
            ->sum('current_qty');
    }
}
