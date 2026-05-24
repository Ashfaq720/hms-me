<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DoctorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // change if you use policy/permission
    }

    public function rules(): array
    {
        $doctorId = $this->route('doctor')?->id ?? $this->route('doctor'); // supports model binding or id

        return [

            'name'                  => ['required', 'string', 'max:255'],

            'email'                 => [
                'required',
                'email',
                'max:255',
                Rule::unique('doctors', 'email')->ignore($doctorId),
            ],

            'phone'                 => ['required', 'string', 'max:50'],
            'emergency_phone'       => ['nullable', 'string', 'max:50'],

            'address'               => ['nullable', 'string'],

            'identification_number' => ['nullable', 'string', 'max:255'],

            'department_id'         => ['required', 'integer', Rule::exists('departments', 'id')],
            'specialist_id'         => ['required', 'integer', Rule::exists('specialists', 'id')],
            'designation_id'        => ['required', 'integer', Rule::exists('designations', 'id')],

            'qualification'         => ['nullable', 'string', 'max:255'],

            'registration_no'       => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('doctors', 'registration_no')->ignore($doctorId),
            ],

            'license_no'            => ['nullable', 'string', 'max:255'],
            'license_expiry_date'   => ['nullable', 'date', 'after_or_equal:today'],

            'doctor_type'           => ['nullable', 'string', 'max:255'],

            'joining_date'          => ['nullable', 'date', 'before_or_equal:today'],
            'leaving_date'          => ['nullable', 'date', 'after_or_equal:joining_date'],

            'gender'                => ['required', Rule::in(['Male', 'Female', 'Other'])],

            'marital_status'        => ['required', Rule::in(['Single', 'Married', 'Divorced', 'Widowed'])],

            'blood_group'           => ['nullable', Rule::in(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])],

            // file upload
            'image'                 => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],

            'notes'                 => ['nullable', 'string'],

            'work_history'          => ['nullable', 'string'],

            'is_active'             => ['nullable', 'in:0,1'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique'                       => 'This email is already used by another doctor.',
            'registration_no.unique'             => 'This registration number is already used by another doctor.',
            'joining_date.before_or_equal'       => 'Joining date cannot be a future date.',
            'leaving_date.after_or_equal'        => 'Leaving date must be same or after joining date.',
            'license_expiry_date.after_or_equal' => 'License expiry date must be today or a future date.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
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
