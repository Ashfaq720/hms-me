<?php

namespace App\Models\Pharmacy;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ControlledDrug extends Model
{
    protected $fillable = [
        'entry_no',
        'entry_date',
        'doctor_name',
        'dea_number',
        'medicine_id',
        'generic_name',
        'lot_number',
        'schedule',
        'expiration_date',
        'ndc_code',
        'action_type',
        'quantity',
        'unit',
        'inventory_status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'entry_date'      => 'datetime',
        'expiration_date' => 'date',
        'quantity'        => 'decimal:2',
    ];

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
