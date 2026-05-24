<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OpdPatientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'patient_id'         => 'nullable|exists:patients,id',
            'patient_mode'       => 'required|in:existing,new',
            'appointment_date'   => 'required|date',
            'consultant_doctor'  => 'required|exists:doctors,id',
            'department_id'      => 'required|exists:departments,id',
            'charge_category_id' => 'nullable|exists:charge_categories,id',
            'charge_id'          => 'nullable|exists:charges,id',
            'standard_charge'    => 'nullable|numeric|min:0',
            'applied_charge'     => 'nullable|numeric|min:0',
            'discount'           => 'nullable|numeric|min:0',
            'tax'                => 'nullable|numeric|min:0',
            'amount'             => 'nullable|numeric|min:0',
            'payment_mode'       => 'required|in:cash,cheque,bank,upi,online,other',
            'paid_amount'        => 'nullable|numeric|min:0',
            'old_patient'        => 'nullable|in:yes,no',
            'reference'          => 'nullable|string|max:255',
            'note'               => 'nullable|string|max:1000',
            'known_allergies'    => 'nullable|string|max:1000',
            'supporting_doc'     => 'nullable|file|mimes:pdf,docx,png,jpeg,jpg|max:5120',

            // Payment-specific fields
            'cheque_no'             => 'nullable|string|max:50',
            'cheque_date'           => 'nullable|date',
            'bank_name'             => 'nullable|string|max:100',
            'account_no'            => 'nullable|string|max:50',
            'transaction_id'        => 'nullable|string|max:100',
            'upi_id'                => 'nullable|string|max:100',
            'other_payment_details' => 'nullable|string|max:255',

            // Hidden charge fields
            'charge_type_id'    => 'nullable|integer',
            'unite_type_id'     => 'nullable|integer',
            'tax_category_id'   => 'nullable|integer',

            // BRD fields
            'visit_type'        => 'required|in:new,follow_up,recheckup,referred,emergency',
            'chief_complaint'   => 'nullable|string|max:1000',
            'referral_source'   => 'nullable|string|max:255',

            // Shift & slot
            'shift_id'          => 'nullable|exists:shifts,id',
            'slot'              => ['nullable', 'regex:/^\d{2}:\d{2}\|\d{2}:\d{2}$/'],

            // Queue priority
            'priority'          => 'nullable|in:Normal,Senior Citizen,VIP,Emergency',
        ];

        if ($this->input('patient_mode') === 'new') {
            $rules['patient_name']           = 'required|string|max:255';
            $rules['mobileno']               = 'required|string|max:20';
            $rules['gender']                 = 'required|in:Male,Female,Other';
            $rules['dob']                    = 'nullable|date|before_or_equal:today';
            $rules['blood_group']            = 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-';
            $rules['discount_type']          = 'nullable|in:CORPORATE,INSURANCE,STAFF,SELF';
            $rules['organization_name']      = 'nullable|string|max:255';
            $rules['organization_id']        = 'nullable|string|max:100';
            $rules['organization_api_link']  = 'nullable|string|max:255';
        } else {
            $rules['patient_id'] = 'required|exists:patients,id';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'patient_mode.required' => 'Please select patient mode.',
            'patient_mode.in'       => 'Patient mode must be either existing or new.',

            'patient_id.required'   => 'Please select an existing patient.',
            'patient_id.exists'     => 'Selected patient does not exist.',

            'patient_name.required' => 'Patient name is required for new patient.',
            'mobileno.required'     => 'Mobile number is required for new patient.',
            'gender.required'       => 'Gender is required for new patient.',

            'consultant_doctor.required' => 'Consultant doctor is required.',
            'department_id.required'     => 'Department is required.',
            'charge_category_id.required'=> 'Charge category is required.',
            'charge_id.required'         => 'Charge is required.',
            'appointment_date.required'  => 'Appointment date is required.',
            'payment_mode.required'      => 'Payment mode is required.',
        ];
    }
}
