<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsultationNote extends Model
{
    protected $fillable = [
        'opd_patient_id',
        'subjective',
        'objective',
        'assessment',
        'plan',
        'icd10_code',
        'icd10_description',
        'status',
        'closed_at',
        'closed_by',
        'created_by',
    ];

    protected $casts = [
        'closed_at' => 'datetime',
    ];

    public function opdPatient()
    {
        return $this->belongsTo(OpdPatient::class, 'opd_patient_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }
}
