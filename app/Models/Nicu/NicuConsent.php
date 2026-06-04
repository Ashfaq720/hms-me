<?php

namespace App\Models\Nicu;

use Illuminate\Database\Eloquent\Model;

class NicuConsent extends Model
{
    protected $table = 'nicu_consents';

    protected $fillable = [
        'nicu_admission_id', 'consent_type', 'guardian_name',
        'guardian_relation', 'guardian_phone', 'signed_at',
        'document_path', 'status', 'notes', 'witnessed_by',
    ];

    protected $casts = ['signed_at' => 'datetime'];

    public function admission() { return $this->belongsTo(NicuAdmission::class, 'nicu_admission_id'); }
}
