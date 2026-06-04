<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientVisitor extends Model
{
    protected $fillable = [
        'visitor_name', 'contact_no', 'patient_type', 'visit_date', 'visit_time', 'visitor_qty',
        'patient_id', 'patient_name', 'department_id', 'remarks', 'visit_code', 'created_by', 'updated_by'
    ];

    protected static function boot()
    {
        parent::boot();

        static::created(function (PatientVisitor $patientVisitor) {
            if (! empty($patientVisitor->visit_code)) {
                return;
            }

            $patientVisitor->visit_code = 'VIS-' . str_pad((string) $patientVisitor->id, 5, '0', STR_PAD_LEFT);
            $patientVisitor->saveQuietly();
        });
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
