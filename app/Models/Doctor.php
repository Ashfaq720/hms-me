<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    protected $fillable = [
        'user_id',
        'doctor_code',
        'name',
        'email',
        'phone',
        'emergency_phone',
        'address',
        'identification_number',
        'department_id',
        'specialist_id',
        'designation_id',
        'qualification',
        'registration_no',
        'license_no',
        'license_expiry_date',
        'doctor_type',
        'joining_date',
        'leaving_date',
        'gender',
        'marital_status',
        'blood_group',
        'image',
        'notes',
        'work_history',
        'is_active',
    ];

    protected $casts = [
        'license_expiry_date' => 'date',
        'joining_date'       => 'date',
        'leaving_date'       => 'date',
    ];

    protected static function booted()
    {
        static::creating(function ($doctor) {

            // If doctor_code already set (manual entry), skip auto generation
            if (! empty($doctor->doctor_code)) {
                return;
            }

            $lastCode = self::where('doctor_code', 'like', 'DOC-%')
                ->orderBy('id', 'desc')
                ->value('doctor_code');

            if ($lastCode) {
                // Extract numeric part
                $number     = (int) str_replace('DOC-', '', $lastCode);
                $nextNumber = $number + 1;
            } else {
                $nextNumber = 1;
            }

            // Pad with leading zeros (DOC-001)
            $doctor->doctor_code = 'DOC-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        });
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function designation()
    {
        return $this->belongsTo(Designation::class);
    }

    public function specialist()
    {
        return $this->belongsTo(Specialist::class);
    }

    public function shifts()
    {
        return $this->belongsToMany(Shift::class, 'doctor_shift')->withTimestamps();
    }
}
