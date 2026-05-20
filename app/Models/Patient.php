<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    protected $fillable = [
        'lang_id',
        'mrn',
        'health_card_no',
        'patient_name',
        'dob',
        'image',
        'mobileno',
        'email',
        'gender',
        'marital_status',
        'discount_type',
        'organization_name',
        'organization_id',
        'organization_api_link',
        'blood_group',
        'address',
        'guardian_name',
        'patient_type',
        'identification_number',
        'known_allergies',
        'note',
        'is_ipd',
        'insurance',
        'insurance_validity',
        'is_dead',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'dob'                => 'date',
        'insurance_validity' => 'date',
        'is_ipd'             => 'boolean',
        'is_dead'            => 'boolean',
        'is_active'          => 'boolean',
    ];

    public function opdPatients()
    {
        return $this->hasMany(OpdPatient::class)->latest('date');
    }

    public function ipdPatients()
    {
        return $this->hasMany(IpdPatient::class)->latest('admission_date');
    }

    public function erPatients()
    {
        return $this->hasMany(\App\Models\FrontDesk\ErPatient::class)->latest('arrival_time');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class)->latest('date');
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class)->latest('date');
    }

    public function pharmacyTransactions()
    {
        return $this->hasMany(\App\Models\Pharmacy\PharmacyTransaction::class)->latest();
    }

    public function labOrders()
    {
        return $this->hasMany(\App\Models\Ipd\LabInvestigationOrder::class)->latest('datetime');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class)->latest('payment_date');
    }

    public function histories()
    {
        return $this->hasMany(PatientHistory::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function (Patient $patient) {
            $dirty = [];

            if (empty($patient->mrn)) {
                $dirty['mrn'] = 'MRN-' . str_pad((string) $patient->id, 6, '0', STR_PAD_LEFT);
            }

            if (empty($patient->health_card_no)) {
                $dirty['health_card_no'] = 'HC-' . date('Y') . '-' . str_pad((string) $patient->id, 5, '0', STR_PAD_LEFT);
            }

            if ($dirty) {
                $patient->forceFill($dirty)->saveQuietly();
            }
        });
    }

}
