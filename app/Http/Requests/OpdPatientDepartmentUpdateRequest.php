<?php

// app/Http/Requests/OpdPatientDepartmentUpdateRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OpdPatientDepartmentUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('opdPatientDepartment')?->id;

        return [
            'patient_id'       => ['required','exists:patients,id'],
            'case'             => ['nullable','string','max:255'],
            'opd_number'       => ['required','string','max:50', Rule::unique('opd_patient_departments','opd_number')->ignore($id)],

            'height'           => ['nullable','numeric','min:0','max:300'],
            'weight'           => ['nullable','numeric','min:0','max:500'],
            'bp'               => ['nullable','string','max:20'],

            'appointment_date' => ['required','date'],

            'doctor_id'        => ['required','exists:doctors,id'],
            'standard_charge'  => ['required','numeric','min:0'],

            'payment_mode'     => ['required', 'string','max:30',
                Rule::in(['Cash','Card','MFS','Bank','Insurance','Due'])
            ],

            'symptoms'         => ['nullable','string'],
            'notes'            => ['nullable','string'],

            'is_old_patient'   => ['nullable','boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_old_patient' => $this->boolean('is_old_patient'),
        ]);
    }
}
