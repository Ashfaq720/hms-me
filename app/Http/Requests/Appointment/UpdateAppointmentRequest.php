<?php

namespace App\Http\Requests\Appointment;

class UpdateAppointmentRequest extends StoreAppointmentRequest
{
    /**
     * Update allows time to come in as H:i or H:i:s (existing rows have full seconds).
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'time' => 'nullable|date_format:H:i,H:i:s',
        ]);
    }
}
