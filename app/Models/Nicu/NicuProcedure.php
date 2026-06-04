<?php

namespace App\Models\Nicu;

use Illuminate\Database\Eloquent\Model;

class NicuProcedure extends Model
{
    protected $table = 'nicu_procedures';

    protected $fillable = [
        'nicu_admission_id', 'procedure_code', 'procedure_name',
        'start_time', 'end_time', 'device_id', 'status',
        'clinical_indication', 'outcome', 'performed_by',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function admission() { return $this->belongsTo(NicuAdmission::class, 'nicu_admission_id'); }
}
