<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorFee extends Model
{
    protected $fillable = [
        'doctor_id',
        'first_visit_fee',
        'follow_up_window',
        'follow_up_fee',
        'ipd_visit_fee',
        'opd_visit_fee',
        'status',
    ];

    // DoctorFee.php
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

}
