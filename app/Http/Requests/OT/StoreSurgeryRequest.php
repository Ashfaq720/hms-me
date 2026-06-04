<?php

namespace App\Http\Requests\OT;

use App\Models\Ot\OtSurgeryRequest;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class StoreSurgeryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'patient_id'                  => 'required|exists:patients,id',
            'encounter_type'              => 'required|in:IPD,OPD,ER',
            'encounter_id'                => 'nullable|integer',
            'ipd_admission_id'            => 'nullable|integer',
            'surgery_type_id'             => 'nullable|exists:ot_surgery_types,id',
            'surgery_category_id'         => 'nullable|exists:ot_surgery_categories,id',
            'requested_by_doctor_id'      => 'nullable|exists:doctors,id',
            'primary_surgeon_id'          => 'nullable|exists:doctors,id',
            'department_id'               => 'nullable|exists:departments,id',
            'requested_surgery_date'      => 'nullable|date',
            'requested_surgery_time'      => 'nullable',
            'estimated_duration_minutes'  => 'nullable|integer|min:5|max:1440',
            'date_flexibility'            => 'nullable|in:Fixed,Flexible',
            'flexibility_reason'          => 'nullable|string',
            'required_ot_type'            => 'nullable|string|max:50',
            'priority'                    => 'nullable|in:Low,Normal,High,Emergency',
            'is_emergency'                => 'nullable|boolean',
            'emergency_reason'            => 'nullable|string|required_if:is_emergency,1|required_if:priority,Emergency',
            'is_life_threatening'         => 'nullable|boolean',
            'is_immediate_ot'             => 'nullable|boolean',
            'diagnosis'                   => 'required|string',
            'secondary_diagnosis'         => 'nullable|string',
            'icd_code'                    => 'nullable|string|max:20',
            'procedure_notes'             => 'nullable|string',
            'clinical_indication'         => 'nullable|string',
            'asa_grade'                   => 'nullable|string|max:10',
            'special_requirements'        => 'nullable|string',
            'blood_required'              => 'nullable|boolean',
            'blood_units'                 => 'nullable|integer|min:0|max:20',
            'blood_group'                 => 'nullable|string|max:10',
            'blood_group_id'              => 'nullable|exists:blood_groups,id',
            'blood_components'            => 'nullable|array',
            'blood_components.*'          => 'nullable|string|in:' . implode(',', OtSurgeryRequest::BLOOD_COMPONENTS),
            'crossmatch_required'         => 'nullable|boolean',
            'blood_bank_instruction'      => 'nullable|string',
            'junior_approval_required'    => 'nullable|boolean',
            'consultant_approval_required'=> 'nullable|boolean',
        ];
    }

    /** Cross-field validation: preferred surgery date can't be in the past (unless emergency). */
    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            $data = $v->getData();
            if (! empty($data['requested_surgery_date']) && empty($data['is_emergency'])) {
                if (Carbon::parse($data['requested_surgery_date'])->isPast()) {
                    $v->errors()->add(
                        'requested_surgery_date',
                        'Preferred date cannot be earlier than today (unless emergency).'
                    );
                }
            }
        });
    }
}
