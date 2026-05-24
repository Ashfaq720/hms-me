<?php

namespace App\Models\Er;

use App\Models\FrontDesk\ErPatient;
use Illuminate\Database\Eloquent\Model;

class ErClinicalNote extends Model
{
    protected $table = 'er_clinical_notes';
    protected $guarded = [];
    protected $casts = ['recorded_at' => 'datetime', 'signed' => 'boolean'];

    public function erPatient() { return $this->belongsTo(ErPatient::class); }
    public function doctor()    { return $this->belongsTo(\App\Models\Doctor::class); }
}
