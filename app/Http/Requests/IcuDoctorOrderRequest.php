<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IcuDoctorOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'doctor_id'           => ['required', 'integer', Rule::exists('doctors', 'id')],
            'order_type'          => ['required', Rule::in([
                'Medication', 'Lab', 'Radiology', 'Procedure', 'NursingCare', 'DietFluid', 'Monitoring',
            ])],
            'order_title'         => ['required', 'string', 'max:255'],
            'order_details'       => ['nullable', 'string', 'max:4000'],
            'priority'            => ['required', Rule::in(['Routine', 'Urgent', 'STAT'])],
            'start_time'          => ['nullable', 'date'],
            'frequency'           => ['nullable', 'string', 'max:50'],
            'duration'            => ['nullable', 'string', 'max:50'],
            'requires_doctor_ack' => ['nullable', 'boolean'],
            'remarks'             => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'requires_doctor_ack' => filter_var($this->input('requires_doctor_ack'), FILTER_VALIDATE_BOOLEAN),
        ]);
    }
}
