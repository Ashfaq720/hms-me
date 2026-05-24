<?php

namespace App\Models\Nicu;

use App\Models\NicuAdmission;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class NicuInfection extends Model
{
    protected $table = 'nicu_infections';

    public const TYPES      = ['Sepsis', 'NEC', 'Pneumonia', 'UTI', 'Meningitis', 'Other'];
    public const SOURCES    = ['CLABSI', 'VAP', 'Surgical Site', 'Community', 'Unknown'];
    public const ISOLATIONS = ['None', 'Contact', 'Droplet', 'Airborne'];

    protected $fillable = [
        'nicu_admission_id',
        'infection_type', 'organism', 'source', 'isolation_status',
        'identified_on', 'resolved_on',
        'treatment_summary', 'reported_by',
    ];

    protected $casts = [
        'identified_on' => 'date',
        'resolved_on'   => 'date',
    ];

    public function admission()
    {
        return $this->belongsTo(NicuAdmission::class, 'nicu_admission_id');
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }
}
