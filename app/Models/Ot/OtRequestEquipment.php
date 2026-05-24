<?php

namespace App\Models\Ot;

use Illuminate\Database\Eloquent\Model;

class OtRequestEquipment extends Model
{
    protected $table = 'ot_request_equipments';

    protected $fillable = [
        'surgery_request_id', 'ot_equipment_id', 'equipment_name',
        'quantity', 'is_mandatory', 'setup_instruction', 'notes',
    ];

    protected $casts = [
        'is_mandatory' => 'boolean',
        'quantity' => 'integer',
    ];

    public function surgeryRequest()
    {
        return $this->belongsTo(OtSurgeryRequest::class, 'surgery_request_id');
    }

    public function equipment()
    {
        return $this->belongsTo(OtEquipment::class, 'ot_equipment_id');
    }
}
