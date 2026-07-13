<?php

namespace App\Http\Requests\Appointment;

use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Default: any authenticated user can book an appointment.
        // Tighten via Policy / @can if you need role-specific gating.
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'patient_id'         => 'required|exists:patients,id',
            'doctor'             => 'required|exists:doctors,id',
            'case_reference_id'  => 'nullable|integer',
            'visit_details_id'   => 'nullable|integer',
            'date'               => 'required|date',
            'time'               => 'nullable|date_format:H:i',
            'priority'           => 'required|string|max:100',
            'specialist'         => 'required|string|max:100',
            'amount'             => 'required|numeric|min:0',
            'message'            => 'nullable|string|max:1000',
            'appointment_status' => 'nullable|in:Pending,Approved,Rejected,Cancelled',
            'visit_status'       => 'nullable|in:booked,checked_in,waiting,in_consultation,completed,closed,cancelled,no_show,referred,converted_to_er',
            'source'             => 'required|in:Walk-in,Phone,Web',
            'is_opd'             => 'required|in:Yes,No',
            'is_ipd'             => 'required|in:Yes,No',
            'is_queue'           => 'nullable|integer',
            'global_shift_id'    => 'nullable|integer',
            'shift_id'           => 'nullable|exists:shifts,id',
            'slot'               => ['nullable', 'regex:/^\d{2}:\d{2}\|\d{2}:\d{2}$/'],
            'live_consult'       => 'required|in:Zoom,Meet,None',
        ];
    }

    public function messages(): array
    {
        return [
            'patient_id.exists' => 'The selected patient no longer exists.',
            'doctor.exists'     => 'The selected doctor no longer exists.',
            'slot.regex'        => 'Slot must be in HH:MM|HH:MM format (e.g. 10:00|10:30).',
        ];
    }
}
