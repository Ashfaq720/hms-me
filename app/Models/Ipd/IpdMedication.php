<?php
namespace App\Models\Ipd;

use App\Models\IpdPatient;
use App\Models\Pharmacy\Medicine;
use Illuminate\Database\Eloquent\Model;

class IpdMedication extends Model
{
    protected $fillable = [
        'ipd_patient_id',
        'medicine_id',
        'datetime',
        'dosage',
        'medicated_by',
        'remarks',
        'notes',
    ];

    protected $casts = [
        'datetime' => 'datetime',
    ];

    public function ipdPatient()
    {
        return $this->belongsTo(IpdPatient::class, 'ipd_patient_id');
    }

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

}
