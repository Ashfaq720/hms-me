<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'case_reference_id',
        'visit_details_id',
        'date',
        'time',
        'priority',
        'specialist',
        'doctor',
        'amount',
        'message',
        'appointment_status',
        'visit_status',
        'cancellation_reason',
        'source',
        'is_opd',
        'is_ipd',
        'global_shift_id',
        'shift_id',
        'slot_time_from',
        'slot_time_to',
        'is_queue',
        'live_consult',
    ];

    public const VISIT_STATUSES = [
        'booked'          => 'Booked',
        'checked_in'      => 'Checked In',
        'waiting'         => 'Waiting',
        'in_consultation' => 'In Consultation',
        'completed'       => 'Completed',
        'closed'          => 'Closed',
        'cancelled'       => 'Cancelled',
        'no_show'         => 'No Show',
        'referred'        => 'Referred',
        'converted_to_er' => 'Converted to ER',
    ];

    public function getVisitStatusLabelAttribute(): string
    {
        return self::VISIT_STATUSES[$this->visit_status] ?? ucfirst($this->visit_status ?? '');
    }

    public function getVisitStatusBadgeAttribute(): string
    {
        return match ($this->visit_status) {
            'booked'          => 'bg-primary',
            'checked_in'      => 'bg-info text-dark',
            'waiting'         => 'bg-warning text-dark',
            'in_consultation' => 'bg-success',
            'completed'       => 'bg-success',
            'closed'          => 'bg-secondary',
            'cancelled'       => 'bg-danger',
            'no_show'         => 'bg-dark',
            'referred'        => 'bg-warning text-dark',
            'converted_to_er' => 'bg-danger',
            default           => 'bg-secondary',
        };
    }

    protected function casts(): array
    {
        return [
            'date' => 'datetime',
        ];
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctorRelation()
    {
        return $this->belongsTo(Doctor::class, 'doctor');
    }
}
