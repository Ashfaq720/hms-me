<?php
namespace App\Models\Ipd;

use App\Models\CaseReference;
use App\Models\Doctor;
use App\Models\IpdPatient;
use App\Models\Patient;
use App\Models\Pharmacy\Medicine;
use Illuminate\Database\Eloquent\Model;

class MedicineOrder extends Model
{
    protected $fillable = [
        'medicine_id',
        'qty',
        'prescribed_by',
        'patient_id',
        'ipd_id',
        'er_id',
        'case_id',
        'source',
        'status',
        'order_by',
    ];

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

    public function prescribedBy()
    {
        return $this->belongsTo(Doctor::class, 'prescribed_by');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function ipdPatient()
    {
        return $this->belongsTo(IpdPatient::class, 'ipd_id');
    }

    public function caseReference()
    {
        return $this->belongsTo(CaseReference::class, 'case_id');
    }
}
