<?php

namespace App\Models\Er;

use App\Models\FrontDesk\ErPatient;
use Illuminate\Database\Eloquent\Model;

class ErClinicalNote extends Model
{
    protected $table = 'er_clinical_notes';

    protected $fillable = [
        'er_patient_id', 'note_type', 'subjective', 'objective',
        'assessment', 'plan', 'doctor_id', 'recorded_at', 'signed', 'signed_by',
    ];
    protected $casts = ['recorded_at' => 'datetime', 'signed' => 'boolean'];

    public function erPatient() { return $this->belongsTo(ErPatient::class); }
    public function doctor()    { return $this->belongsTo(\App\Models\Doctor::class); }
}
