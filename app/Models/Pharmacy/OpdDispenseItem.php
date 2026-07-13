<?php

namespace App\Models\Pharmacy;

use Illuminate\Database\Eloquent\Model;

class OpdDispenseItem extends Model
{
    protected $fillable = [
        'opd_dispense_id',
        'medicine_id',
        'dosage',
        'qty_required',
        'available_qty',
        'unit_price',
        'store',
    ];

    public function dispense()
    {
        return $this->belongsTo(OpdDispense::class, 'opd_dispense_id');
    }

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }
}
