<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IcuAdmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_id'                => ['required', 'integer', Rule::exists('patients', 'id')],

            'source_type'               => ['required', Rule::in(['ER', 'OPD', 'Ipd', 'DIRECT'])],
            'source_id'                 => ['nullable', 'integer'],

            'icu_type'                  => ['required', Rule::in(['ICU', 'CCU', 'NICU', 'PICU'])],

            'admission_type'            => ['nullable', 'string', 'max:30'],
            'admission_diagnosis'       => ['required', 'string', 'max:1000'],
            'referring_doctor_id'       => ['required', 'integer', Rule::exists('doctors', 'id')],

            'isolation_type'            => ['required', Rule::in(['Airborne', 'Contact', 'Droplet', 'Standard', 'None'])],
            'ventilator_required'       => ['nullable', 'boolean'],
            'monitor_required'          => ['nullable', 'boolean'],

            'bed_id'                    => ['required_without:override', 'nullable', 'integer', Rule::exists('beds', 'id')],
            'admission_time'            => ['required', 'date'],

            'remarks'                   => ['nullable', 'string', 'max:2000'],

            // Optional emergency-override block
            'override'                  => ['nullable', 'boolean'],
            'override_resource_issue'   => ['nullable', 'required_if:override,1', Rule::in(['NoBed', 'NoVentilator', 'NoMonitor', 'NoIsolationBed', 'Other'])],
            'override_reason'           => ['nullable', 'required_if:override,1', 'string', 'max:1000'],
            'override_approved_by'      => ['nullable', 'required_if:override,1', 'integer', Rule::exists('users', 'id')],
            'override_temporary_bed_id' => ['nullable', 'integer', Rule::exists('beds', 'id')],
        ];
    }

    public function messages(): array
    {
        return [
            'admission_diagnosis.required'     => 'Admission diagnosis is required.',
            'referring_doctor_id.required'     => 'Referring doctor must be selected.',
            'bed_id.required_without'          => 'Either select an ICU bed or use Emergency Override.',
            'override_reason.required_if'      => 'Override reason is required when emergency override is used.',
            'override_approved_by.required_if' => 'An approver must be selected for emergency override.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'override'            => filter_var($this->input('override'), FILTER_VALIDATE_BOOLEAN),
            'ventilator_required' => filter_var($this->input('ventilator_required'), FILTER_VALIDATE_BOOLEAN),
            'monitor_required'    => filter_var($this->input('monitor_required', true), FILTER_VALIDATE_BOOLEAN),
        ]);
    }
}
