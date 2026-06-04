<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IPDPatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            // existing patient
            'patient_id'              => 'nullable|integer|exists:patients,id',

            // new patient
            'patient_name'            => 'required_without:patient_id|string|max:255',
            'mobileno'                => 'required_without:patient_id|string|max:20|unique:patients,mobileno',
            'dob'                     => 'nullable|date',
            'gender'                  => 'required_without:patient_id|in:Male,Female,Other',
            'blood_group'             => 'nullable|string|max:10',
            'discount_type'           => 'nullable|in:CORPORATE,INSURANCE,STUFF,SELF',
            'organization_name'       => 'nullable|string|max:100',
            'organization_id'         => 'nullable|string|max:100',
            'organization_api_link'   => 'nullable|string|max:255',

            // Ipd Admission
            // 'patient_id'              => ['required', 'integer', Rule::exists('patients', 'id')],
            'doctor_id'               => ['required', 'integer', Rule::exists('doctors', 'id')],
            'department_id'           => ['required', 'integer', Rule::exists('departments', 'id')],

            // Use "date" (Laravel doesn't have a default "date" rule)
            'admission_date'          => ['required', 'date'],
            'possible_discharge_date' => ['nullable', 'date', 'after_or_equal:admission_date'],

            'ipd_status'              => ['required'],

            'patient_history'         => ['nullable', 'string', 'max:2000'],
            'remarks'                 => ['nullable', 'string', 'max:2000'],

            // Bed / ICU Allocation
            'allocation_choice'       => ['nullable', 'in:bed,icu'],
            'bed_id'                  => ['required_if:allocation_choice,bed', 'nullable', 'integer', Rule::exists('beds', 'id')],
            'icu_bed_id'              => ['required_if:allocation_choice,icu', 'nullable', 'integer', Rule::exists('beds', 'id')],
            'from'                    => ['required', 'date'],
            'to'                      => ['nullable', 'date', 'after_or_equal:from'],
            'bed_remarks'             => ['nullable', 'string', 'max:2000'],

            // Advance Payment (optional block)
            'amount'                  => ['nullable', 'numeric', 'min:0'],
            'vat'                     => ['nullable', 'numeric', 'min:0'],
            'tax'                     => ['nullable', 'numeric', 'min:0'],

            'payment_via'             => ['nullable'],
            'payment_date'            => ['nullable', 'date'],
            'received_by'             => ['nullable', 'string', 'max:100'],
            'notes'                   => ['nullable', 'string'],

            // You used "successed" in your UI; match it here
            'payment_status'          => ['nullable'],

            // Files
            'files'                   => ['nullable', 'array'],
            'files.*'                 => ['nullable', 'file', 'max:5120'], // 5MB each

            // Patient Documents (multiple)
            'documents'               => ['nullable', 'array'],
            'documents.*.title'       => ['nullable', 'string', 'max:255'],
            'documents.*.file'        => ['nullable', 'file', 'max:10240'], // 10MB each
            'documents.*.remarks'     => ['nullable', 'string', 'max:1000'],
        ];

        // Conditional rules for payment methods
        $via = $this->input('payment_via');

        if ($via === 'card') {
            $rules['card_no']   = ['required', 'string', 'max:30'];
            $rules['card_type'] = ['required'];
        } elseif ($via === 'cheque') {
            $rules['cheque_name'] = ['required', 'string', 'max:30'];
            $rules['cheque_no']   = ['required', 'string', 'max:30'];
            $rules['cheque_date'] = ['required', 'date'];
        } elseif ($via === 'mfs') {
            $rules['mfs_type']           = ['required'];
            $rules['mfs_no']             = ['required', 'string', 'max:30'];
            $rules['mfs_transaction_id'] = ['required', 'string', 'max:100'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'to.after_or_equal'                      => 'Bed "To" must be same or after "From".',
            'possible_discharge_date.after_or_equal' => 'Possible discharge date must be same or after admission date.',

            'card_no.required'                       => 'Card no is required when payment via card.',
            'cheque_no.required'                     => 'Cheque no is required when payment via cheque.',
            'mfs_transaction_id.required'            => 'MFS transaction ID is required when payment via MFS.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Normalize empty strings to null for cleaner validation/store
        $this->merge([
            'received_by'        => $this->nullIfEmpty($this->input('received_by')),
            'notes'              => $this->nullIfEmpty($this->input('notes')),

            'card_no'            => $this->nullIfEmpty($this->input('card_no')),
            'card_type'          => $this->nullIfEmpty($this->input('card_type')),

            'cheque_name'        => $this->nullIfEmpty($this->input('cheque_name')),
            'cheque_no'          => $this->nullIfEmpty($this->input('cheque_no')),

            'mfs_type'           => $this->nullIfEmpty($this->input('mfs_type')),
            'mfs_no'             => $this->nullIfEmpty($this->input('mfs_no')),
            'mfs_transaction_id' => $this->nullIfEmpty($this->input('mfs_transaction_id')),
        ]);
    }

    private function nullIfEmpty($value)
    {
        if ($value === null) {
            return null;
        }

        $v = is_string($value) ? trim($value) : $value;
        return ($v === '') ? null : $v;
    }
}
