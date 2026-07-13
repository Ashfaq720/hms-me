<?php

namespace App\Models\Nicu;

use App\Models\Bed;
use Illuminate\Database\Eloquent\Model;

class NicuResourceAllocation extends Model
{
    protected $table = 'nicu_resource_allocations';

    protected $fillable = [
        'nicu_admission_id', 'bed_id', 'resource_type',
        'device_serial', 'from', 'to', 'status', 'reason', 'assigned_by',
    ];

    protected $casts = [
        'from' => 'datetime',
        'to' => 'datetime',
    ];

    public function admission() { return $this->belongsTo(NicuAdmission::class, 'nicu_admission_id'); }
    public function bed() { return $this->belongsTo(Bed::class); }
}
