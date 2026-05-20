<?php

namespace App\Models\Icu;

use App\Models\Bed;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class IcuInfectionControlOverride extends Model
{
    protected $table = 'icu_infection_control_overrides';

    protected $fillable = [
        'icu_admission_id',
        'icu_case_id',
        'required_isolation_type',
        'assigned_bed_id',
        'override_reason',
        'approved_by',
        'override_time',
        'created_by',
    ];

    protected $casts = [
        'override_time' => 'datetime',
    ];

    public function bed()
    {
        return $this->belongsTo(Bed::class, 'assigned_bed_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
