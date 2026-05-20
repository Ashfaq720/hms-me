<?php

namespace App\Models\Ipd;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class LabInvestigationOrder extends Model
{
    protected $table = 'lab_investigation_order';

    protected $fillable = [
        'order_number',
        'ipd_id',
        'opd_id',
        'er_id',
        'appointment_id',
        'case_id',
        'patient_id',
        'doctor_id',
        'datetime',
        'generated_by',
        'remarks',
        'priority',
        'collected_by',
        'source',
        'lab_name',
        'type',
    ];

    protected $casts = [
        'datetime' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $last = static::orderBy('id', 'desc')->first();
                $next = $last ? ((int) substr($last->order_number, 4)) + 1 : 1;
                $order->order_number = 'LAB-' . str_pad($next, 3, '0', STR_PAD_LEFT);
            }
        });
    }

    public function requests()
    {
        return $this->hasMany(LabInvestigationOrderRequest::class, 'lab_inv_order_id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
