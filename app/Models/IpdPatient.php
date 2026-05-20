<?php
namespace App\Models;

use App\Models\FrontDesk\VitalCheck;
use App\Models\Ipd\IpdMedication;
use App\Models\Ipd\IpdNurseNote;
use App\Models\Ipd\IpdTreatmentHistory;
use App\Models\Ipd\LabInvestigationOrder;
use App\Models\Ipd\MedicineOrder;
use App\Models\Ipd\OperationHistory;
use Illuminate\Database\Eloquent\Model;

class IpdPatient extends Model
{
    protected $table = 'i_p_d_patients';
    protected $fillable = [
        'case_id',
        'patient_id',
        'doctor_id',
        'department_id',
        'admission_date',
        'possible_discharge_date',
        'admission_type',
        'status',
        'patient_history',
        'remarks',
        'discharge_date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $last          = static::latest('id')->first();
            $nextNumber    = $last ? ((int) substr($last->ipd_no, 3)) + 1 : 1;
            $model->ipd_no = 'Ipd' . str_pad($nextNumber, 2, '0', STR_PAD_LEFT);
        });
    }

    protected $casts = [
        'admission_date'          => 'datetime',
        'possible_discharge_date' => 'datetime',
        'discharge_date'          => 'datetime',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
    public function bedAllocations()
    {
        return $this->hasMany(IpdPatientBed::class, 'ipd_patient_id', 'id');
    }
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'ipd_patient_id', 'id');
    }
    public function nurseNotes()
    {
        return $this->hasMany(IpdNurseNote::class, 'ipd_patient_id', 'id');
    }

    public function roundDrs()
    {
        return $this->hasMany(\App\Models\Ipd\IpdRoundDr::class, 'ipd_patient_id', 'id');
    }

    public function caseDrs()
    {
        return $this->hasMany(\App\Models\Ipd\IpdCaseDr::class, 'ipd_patient_id', 'id');
    }

    public function charges()
    {
        return $this->hasMany(PatientCharge::class, 'ipd_id', 'id');
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class, 'ipd_patient_id', 'id');
    }

    public function vitalChecks()
    {
        return $this->hasMany(VitalCheck::class, 'ipd_patient_id', 'id');
    }

    public function operationHistories()
    {
        return $this->hasMany(OperationHistory::class, 'ipd_id', 'id');
    }

    public function medicineOrders()
    {
        return $this->hasMany(MedicineOrder::class, 'ipd_id', 'id');
    }

    public function medications()
    {
        return $this->hasMany(IpdMedication::class, 'ipd_patient_id', 'id');
    }

    public function pathologyOrders()
    {
        return $this->hasMany(LabInvestigationOrder::class, 'ipd_id', 'id')
            ->where('type', 'pathology');
    }

    public function treatmentHistories()
    {
        return $this->hasMany(IpdTreatmentHistory::class, 'ipd_id', 'id');
    }

    public function radiologyOrders()
    {
        return $this->hasMany(LabInvestigationOrder::class, 'ipd_id', 'id')
            ->where('type', 'radiology');
    }

    public function documents()
    {
        return $this->hasMany(IpdPatientDocument::class, 'ipd_patient_id', 'id');
    }
}
