<?php

namespace App\Models\Icu;

use App\Models\Bed;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class IcuAdmissionOverride extends Model
{
    protected $table = 'icu_admission_overrides';

    protected $fillable = [
        'icu_admission_id',
        'resource_issue',
        'override_reason',
        'approved_by',
        'temporary_bed_id',
        'override_time',
        'created_by',
    ];

    protected $casts = [
        'override_time' => 'datetime',
    ];

    public function admission()
    {
        return $this->belongsTo(IcuAdmission::class, 'icu_admission_id');
    }

    public function temporaryBed()
    {
        return $this->belongsTo(Bed::class, 'temporary_bed_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
