<?php
namespace App\Models;

use App\Traits\LogPreference;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Patient extends Authenticatable
{
    use LogPreference, Notifiable;

    protected string $logName = 'patient';

    /**
     * Maps Patient::getAuthIdentifierName() → 'id'
     * Used by the 'patient' guard for self-service portal login.
     */
    public function getAuthPassword(): string
    {
        return $this->portal_password ?: '';
    }

    protected $hidden = ['portal_password', 'remember_token'];

    protected $fillable = [
        'lang_id',
        'mrn',
        'health_card_no',
        'patient_name',
        'dob',
        'image',
        'mobileno',
        'email',
        'portal_password',
        'portal_last_login_at',
        'gender',
        'marital_status',
        'discount_type',
        'organization_name',
        'organization_id',
        'organization_api_link',
        'blood_group',
        'address',
        'guardian_name',
        'emergency_contact',
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
        'org_id',
        'branch_id',
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

    public function encounters()
    {
        return $this->hasMany(\App\Models\Encounter\Encounter::class)->latest('started_at');
    }

    public function insurancePolicies()
    {
        return $this->hasMany(\App\Models\Insurance\InsurancePolicy::class);
    }

    public function packageEnrollments()
    {
        return $this->hasMany(\App\Models\Package\PackageEnrollment::class);
    }

    public function branch()
    {
        return $this->belongsTo(\App\Models\Organization\Branch::class, 'branch_id');
    }

    public function portalUsers()
    {
        return $this->hasMany(\App\Models\PatientPortal\PatientPortalUser::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function (Patient $patient) {
            $dirty = [];

            // Robust MRN: walk well-formed MRN-NNNNNN rows and pick the next
            // numeric suffix. Plain `id`-based padding collides whenever the
            // patients table has historical rows with MRNs out of sync with
            // their ids (seed data, test data, etc).
            if (empty($patient->mrn)) {
                $maxN = (int) static::query()
                    ->where('mrn', 'REGEXP', '^MRN-[0-9]{6}$')
                    ->selectRaw('MAX(CAST(SUBSTRING(mrn, 5) AS UNSIGNED)) as n')
                    ->value('n');
                $next = max($maxN + 1, $patient->id);
                $dirty['mrn'] = 'MRN-' . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
            }

            if (empty($patient->health_card_no)) {
                $year = date('Y');
                $maxN = (int) static::query()
                    ->where('health_card_no', 'REGEXP', '^HC-' . $year . '-[0-9]{5}$')
                    ->selectRaw('MAX(CAST(SUBSTRING(health_card_no, 9) AS UNSIGNED)) as n')
                    ->value('n');
                $next = max($maxN + 1, $patient->id);
                $dirty['health_card_no'] = 'HC-' . $year . '-' . str_pad((string) $next, 5, '0', STR_PAD_LEFT);
            }

            if ($dirty) {
                $patient->forceFill($dirty)->saveQuietly();
            }
        });
    }

}
