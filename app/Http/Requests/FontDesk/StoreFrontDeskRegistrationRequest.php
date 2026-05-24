<?php
namespace App\Http\Requests\FontDesk;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFrontDeskRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $regType     = $this->input('registration_type');
        $isNewOrUnknown = \in_array($regType, ['NEW_PATIENT', 'UNKNOWN']);

        $contactRules = ['required', 'string', 'min:7', 'max:20'];
        if ($isNewOrUnknown) {
            $contactRules[] = Rule::unique('patients', 'mobileno');
        }

        return [
            // patient identity
            'patient_id'            => ['nullable', 'integer', 'exists:patients,id'],
            'registration_type'     => ['required', Rule::in(['NEW_PATIENT', 'EXISTING_PATIENT', 'UNKNOWN'])],
            'name'                  => ['nullable', 'required_if:registration_type,NEW_PATIENT', 'string', 'max:255'],
            'contact_no'            => $contactRules,
            'gender'                => ['nullable', Rule::in(['Male', 'Female', 'Other'])],
            'dob'                   => ['nullable', 'date'],
            'blood_group'           => ['nullable', Rule::in(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])],
            'nid_passport'          => ['nullable', 'string', 'max:50'],
            'address'               => ['nullable', 'string', 'max:500'],
            'guardian_name'         => ['nullable', 'string', 'max:150'],
            'emergency_contact'     => ['nullable', 'string', 'max:20'],

            // org / discount
            'discount_type'         => ['nullable', Rule::in(['CORPORATE', 'INSURANCE', 'STUFF', 'SELF'])],
            'organization_name'     => ['nullable', 'string', 'max:150'],
            'organization_id'       => ['nullable', 'string', 'max:50'],
            'organization_api_link' => ['nullable', 'url', 'max:255'],

            // visit
            'patient_type'          => ['required', Rule::in(['OPD', 'Ipd', 'ER', 'LAB'])],
            'appointment_date'      => ['nullable', 'date'],
            'ipd_admission_date'    => ['nullable', 'date'],
            'er_arrival_datetime'   => ['nullable', 'date_format:Y-m-d\TH:i'],
            'booking_status'        => ['nullable', Rule::in(['PRE_BOOK', 'WALK_IN', 'REFERRAL'])],
            'visit_type'            => ['nullable', Rule::in(['new', 'follow_up', 'recheckup', 'referred', 'emergency'])],
            'referral_source'       => ['nullable', 'string', 'max:150'],
            'priority'              => ['nullable', Rule::in(['Normal', 'Senior Citizen', 'VIP', 'Emergency'])],
            'doctor_id'             => ['nullable', 'integer', 'exists:doctors,id'],
            'department_id'         => ['nullable', 'integer', 'exists:departments,id'],
            'shift_id'              => ['nullable', 'integer', 'exists:shifts,id'],
            'slot_time'             => ['nullable', 'string', 'max:30'],

            // er
            'er_priority'           => ['nullable', Rule::in(['CRITICAL', 'HIGH', 'NORMAL'])],

            // ipd bed
            'bed_id'                => ['nullable', 'integer', 'exists:beds,id'],
            'from'                  => ['nullable'],
            'to'                    => ['nullable'],
            'bed_remarks'           => ['nullable', 'string', 'max:500'],

            // file + note
            'supporting_doc'        => ['nullable', 'file', 'mimes:pdf,doc,docx,png,jpg,jpeg', 'max:5120'],
            'description'           => ['nullable', 'string', 'max:2000'],

            // billing (OPD only)
            'standard_charge'       => ['nullable', 'numeric', 'min:0'],
            'applied_charge'        => ['nullable', 'numeric', 'min:0'],
            'discount'              => ['nullable', 'numeric', 'min:0'],
            'tax'                   => ['nullable', 'numeric', 'min:0'],
            'amount'                => ['nullable', 'numeric', 'min:0'],

            'case_id'               => ['nullable'],
            'ipd_patient_id'        => ['nullable'],
        ];
    }

    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->after(function ($v) {
            $data = $this->all();
            $type    = $data['registration_type'] ?? '';
            $patType = $data['patient_type'] ?? '';

            if ($type === 'EXISTING_PATIENT' && empty($data['patient_id'])) {
                $v->errors()->add('patient_id', 'Please search and select an existing patient.');
            }

            if (in_array($patType, ['OPD', 'Ipd', 'ER'])) {
                if (empty($data['doctor_id'])) {
                    $v->errors()->add('doctor_id', 'Please select a doctor.');
                }
                if (empty($data['department_id'])) {
                    $v->errors()->add('department_id', 'Please select a department.');
                }
                if (empty($data['booking_status'])) {
                    $v->errors()->add('booking_status', 'Please select a booking status.');
                }
            }

            if ($patType === 'OPD' && empty($data['appointment_date'])) {
                $v->errors()->add('appointment_date', 'Appointment date is required for OPD.');
            }

            if ($patType === 'OPD' && ($data['booking_status'] ?? '') === 'PRE_BOOK') {
                if (empty($data['shift_id'])) {
                    $v->errors()->add('shift_id', 'Please select a shift for the appointment.');
                }
                if (empty($data['slot_time'])) {
                    $v->errors()->add('slot_time', 'Please select a time slot for the appointment.');
                }
            }

            if ($patType === 'Ipd' && empty($data['bed_id'])) {
                $v->errors()->add('bed_id', 'Please assign a bed for IPD admission.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'name.required_if'          => 'Patient name is required for new registrations.',
            'contact_no.required'       => 'Contact number is required.',
            'contact_no.min'            => 'Contact number must be at least 7 digits.',
            'contact_no.max'            => 'Contact number must not exceed 20 characters.',
            'contact_no.unique'         => 'This phone number is already registered. Search for the existing patient or use a different number.',
            'organization_api_link.url' => 'Organization link must be a valid URL (include https://).',
            'supporting_doc.mimes'      => 'Document must be PDF, DOC, DOCX, PNG, JPG, or JPEG.',
            'supporting_doc.max'        => 'Document must not exceed 5 MB.',
        ];
    }
}
