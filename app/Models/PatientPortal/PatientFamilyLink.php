<?php

namespace App\Models\PatientPortal;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Model;

class PatientFamilyLink extends Model
{
    protected $fillable = [
        'portal_user_id', 'linked_patient_id', 'relationship',
        'status', 'verification_token', 'verified_at',
    ];

    protected $casts = ['verified_at' => 'datetime'];

    public function portalUser()
    {
        return $this->belongsTo(PatientPortalUser::class, 'portal_user_id');
    }

    public function linkedPatient()
    {
        return $this->belongsTo(Patient::class, 'linked_patient_id');
    }
}
