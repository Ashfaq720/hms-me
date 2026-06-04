<?php

namespace App\Models\Icu;

use App\Models\Bed;
use Illuminate\Database\Eloquent\Model;

class IcuTransfer extends Model
{
    protected $table = 'icu_transfers';

    protected $fillable = [
        'icu_admission_id',
        'icu_case_id',
        'patient_id',
        'transfer_type',
        'from_unit',
        'to_unit',
        'from_bed_id',
        'to_bed_id',
        'to_ipd_id',
        'transfer_reason',
        'transfer_time',
        'requested_by',
        'approved_by',
        'status',
    ];

    protected $casts = [
        'transfer_time' => 'datetime',
    ];

    public function admission()
    {
        return $this->belongsTo(IcuAdmission::class, 'icu_admission_id');
    }

    public function fromBed()
    {
        return $this->belongsTo(Bed::class, 'from_bed_id');
    }

    public function toBed()
    {
        return $this->belongsTo(Bed::class, 'to_bed_id');
    }
}
