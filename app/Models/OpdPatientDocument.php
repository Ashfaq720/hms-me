<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpdPatientDocument extends Model
{
    protected $table = 'opd_patient_documents';

    protected $fillable = [
        'opd_patient_id',
        'title',
        'file',
        'remarks',
    ];

    public function opdPatient()
    {
        return $this->belongsTo(OpdPatient::class, 'opd_patient_id');
    }
}
