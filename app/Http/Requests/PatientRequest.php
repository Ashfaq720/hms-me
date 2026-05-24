<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // change if you use policy/permission
    }

    public function rules(): array
    {
        $patientId = $this->route('patient')?->id ?? $this->route('patient'); // supports model binding or id

        return [
            'patient_name'          => ['required', 'string', 'max:255'],

            'dob'                   => ['nullable', 'date', 'before_or_equal:today'],

            // if you upload file: use image; if you only store string path, keep string rule instead
            'image'                 => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            // 'image'                 => ['nullable', 'string', 'max:255'],

            'mobileno'              => [
                'required',
                'string',
                'max:100',
                Rule::unique('patients', 'mobileno')->ignore($patientId),
            ],

            'email'                 => ['nullable', 'email', 'max:255'],

            'gender'                => ['required', Rule::in(['Male', 'Female', 'Other'])],

            'marital_status'        => ['required', Rule::in(['Single', 'Married', 'Divorced', 'Widowed'])],

            'blood_group'           => ['nullable', Rule::in(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])],

            'address'               => ['nullable', 'string'],

            'guardian_name'         => ['nullable', 'string', 'max:255'],

            'patient_type'          => ['nullable', 'string', 'max:255'],

            'identification_number' => ['nullable', 'string', 'max:255'],

            'known_allergies'       => ['nullable', 'string', 'max:255'],

            'note'                  => ['nullable', 'string', 'max:255'],

            'is_ipd'                => ['nullable', 'in:0,1'],

            'insurance'             => ['nullable', 'string', 'max:255'],

            'insurance_validity'    => ['nullable', 'date', 'after_or_equal:today'],

            'is_dead'               => ['nullable', 'in:0,1'],
            'is_active'             => ['nullable', 'in:0,1'],

            'lang_id'               => ['nullable', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'mobileno.unique'                   => 'This mobile number is already used by another patient.',
            'dob.before_or_equal'               => 'DOB cannot be a future date.',
            'insurance_validity.after_or_equal' => 'Insurance validity must be today or a future date.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // normalize flags if they come as true/false or "on"
        $this->merge([
            'is_ipd'    => $this->normalizeBool($this->input('is_ipd')),
            'is_dead'   => $this->normalizeBool($this->input('is_dead')),
            'is_active' => $this->normalizeBool($this->input('is_active')),
        ]);
    }

    private function normalizeBool($value)
    {
        if ($value === null) {
            return null;
        }

        if (in_array($value, [1, '1', true, 'true', 'on', 'yes'], true)) {
            return 1;
        }

        if (in_array($value, [0, '0', false, 'false', 'off', 'no'], true)) {
            return 0;
        }

        return $value;
    }
}
