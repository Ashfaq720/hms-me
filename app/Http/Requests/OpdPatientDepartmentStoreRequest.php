<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OpdPatientDepartmentStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // later replace with policy
    }

    public function rules(): array
    {
        return [
            'patient_id'       => ['required','exists:patients,id'],
            'case'             => ['nullable','string','max:255'],
            'opd_number'       => ['required','string','max:50','unique:opd_patient_departments,opd_number'],

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
        // ensure unchecked checkbox becomes 0 if not present
        $this->merge([
            'is_old_patient' => $this->boolean('is_old_patient'),
        ]);
    }
}
