<?php

namespace App\Http\Requests\ServiceCharge;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServiceCatalogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('service_charge.manage') ?? false;
    }

    public function rules(): array
    {
        $branchId = $this->input('branch_id');
        $catalogId = $this->route('service_catalog');

        return [
            'code' => [
                'required', 'string', 'max:64',
                Rule::unique('service_catalogs')
                    ->where(fn ($q) => $q->where('branch_id', $branchId))
                    ->ignore($catalogId),
            ],
            'name' => ['required', 'string', 'max:191'],
            'organization_id' => ['nullable', 'integer', 'exists:organizations,id'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'department_code' => ['nullable', 'string', 'max:64'],
            'service_type' => ['required', Rule::in([
                'consultation', 'bed', 'icu_bed', 'nicu_bed', 'ot_room',
                'nursing', 'procedure', 'lab_test', 'radiology',
                'pharmacy', 'equipment', 'ambulance', 'package',
                'administrative', 'other',
            ])],
            'charge_unit' => ['required', Rule::in([
                'per_use', 'per_hour', 'per_day', 'per_session', 'per_unit',
                'per_km', 'per_test', 'per_dose', 'per_package',
            ])],
            'base_price' => ['required', 'numeric', 'min:0'],
            'tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'patient_type' => ['nullable', Rule::in(['all', 'self', 'corporate', 'insurance', 'staff'])],
            'valid_from' => ['nullable', 'date'],
            'valid_to' => ['nullable', 'date', 'after_or_equal:valid_from'],
            'discount_allowed' => ['nullable', 'boolean'],
            'insurance_covered' => ['nullable', 'boolean'],
            'package_eligible' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
