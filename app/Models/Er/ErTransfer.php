<?php

namespace App\Models\Er;

use App\Models\Bed;
use App\Models\Doctor;
use App\Models\FrontDesk\ErPatient;
use Illuminate\Database\Eloquent\Model;

class ErTransfer extends Model
{
    protected $table = 'er_transfers';
    protected $guarded = [];

    protected $casts = [
        'requested_at' => 'datetime',
        'accepted_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function erPatient()    { return $this->belongsTo(ErPatient::class); }
    public function targetBed()    { return $this->belongsTo(Bed::class, 'target_bed_id'); }
    public function targetDoctor() { return $this->belongsTo(Doctor::class, 'target_doctor_id'); }
}
