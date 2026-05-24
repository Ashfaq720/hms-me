<?php

namespace App\Http\Controllers\FrontDesk;

use App\Http\Controllers\Controller;
use App\Models\CaseReference;
use App\Models\Doctor;
use App\Models\Ipd\LabInvestigationOrder;
use App\Models\Ipd\LabInvestigationOrderRequest;
use App\Models\LabInvestigation;
use App\Models\LabInvestigationCategory;
use App\Models\LabInvestigationType;
use App\Models\Patient;
use App\Models\PatientCharge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FrontDeskLabOrderController extends Controller
{
    public function create(Request $request)
    {
        $caseId    = $request->query('case_id');
        $patientId = $request->query('patient_id');

        $patient = Patient::findOrFail($patientId);
        $case    = CaseReference::findOrFail($caseId);
        $doctors = Doctor::where('is_active', 1)->orderBy('name')->get();

        $pathologyType  = LabInvestigationType::where('name', 'Pathology')->first();
        $radiologyType  = LabInvestigationType::where('name', 'Radiology')->first();

        $pathologyCategories   = $pathologyType
            ? LabInvestigationCategory::where('type_id', $pathologyType->id)->orderBy('name')->get()
            : collect();
        $pathologyInvestigations = $pathologyCategories->isNotEmpty()
            ? LabInvestigation::whereIn('category_id', $pathologyCategories->pluck('id'))->orderBy('name')->get()
            : collect();

        $radiologyCategories   = $radiologyType
            ? LabInvestigationCategory::where('type_id', $radiologyType->id)->orderBy('name')->get()
            : collect();
        $radiologyInvestigations = $radiologyCategories->isNotEmpty()
            ? LabInvestigation::whereIn('category_id', $radiologyCategories->pluck('id'))->orderBy('name')->get()
            : collect();

        return view('front-desk.lab-order.create', compact(
            'patient', 'case', 'doctors',
            'pathologyType', 'pathologyCategories', 'pathologyInvestigations',
            'radiologyType', 'radiologyCategories', 'radiologyInvestigations'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'case_id'    => 'required|exists:case_references,id',
            'patient_id' => 'required|exists:patients,id',
            'lab_type'   => 'required|in:pathology,radiology,both',

            // pathology section (optional)
            'pathology_enabled'                          => 'nullable',
            'pathology_doctor_id'                        => 'nullable|exists:doctors,id',
            'pathology_priority'                         => 'nullable|in:Regular,Urgent,STAT',
            'pathology_remarks'                          => 'nullable|string|max:500',
            'pathology_lab_inv_type_id'                  => 'nullable|exists:lab_investigation_types,id',
            'pathology_requests'                         => 'required_if:pathology_enabled,1|array|min:1',
            'pathology_requests.*.lab_inv'               => 'required_if:pathology_enabled,1|exists:lab_investigations,id',
            'pathology_requests.*.lab_inv_category_id'   => 'nullable|exists:lab_investigation_categories,id',

            // radiology section (optional)
            'radiology_enabled'                          => 'nullable',
            'radiology_doctor_id'                        => 'nullable|exists:doctors,id',
            'radiology_remarks'                          => 'nullable|string|max:500',
            'radiology_lab_inv_type_id'                  => 'nullable|exists:lab_investigation_types,id',
            'radiology_requests'                         => 'required_if:radiology_enabled,1|array|min:1',
            'radiology_requests.*.lab_inv'               => 'required_if:radiology_enabled,1|exists:lab_investigations,id',
            'radiology_requests.*.lab_inv_category_id'   => 'nullable|exists:lab_investigation_categories,id',
        ]);

        $labType          = $request->input('lab_type');
        $pathologyEnabled = in_array($labType, ['pathology', 'both']);
        $radiologyEnabled = in_array($labType, ['radiology', 'both']);

        if (!$pathologyEnabled && !$radiologyEnabled) {
            return back()->withInput()->with('error', 'Please select at least one lab type (Pathology or Radiology).');
        }

        try {
            DB::transaction(function () use ($request, $pathologyEnabled, $radiologyEnabled) {
                $caseId    = $request->input('case_id');
                $patientId = $request->input('patient_id');

                if ($pathologyEnabled) {
                    $this->createOrder($request, $caseId, $patientId, 'pathology');
                }

                if ($radiologyEnabled) {
                    $this->createOrder($request, $caseId, $patientId, 'radiology');
                }
            });

            $caseId = $request->input('case_id');
            return redirect()
                ->route('front_desk.lab_order.slip', $caseId)
                ->with('success', 'Lab orders created successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to create lab orders: ' . $e->getMessage());
        }
    }

    public function slip($caseId)
    {
        $orders = LabInvestigationOrder::with([
                'patient',
                'doctor',
                'generatedBy',
                'requests.labInvestigation',
                'requests.labInvestigationCategory',
            ])
            ->where('case_id', $caseId)
            ->whereIn('source', ['front_desk_pathology', 'front_desk_radiology'])
            ->latest('id')
            ->get();

        $case    = CaseReference::findOrFail($caseId);
        $patient = $orders->first()?->patient ?? Patient::where('id', function ($q) use ($caseId) {
            $q->select('patient_id')->from('lab_investigation_order')->where('case_id', $caseId)->limit(1);
        })->first();

        return view('front-desk.lab-order.slip', compact('orders', 'case', 'patient'));
    }

    private function createOrder(Request $request, int $caseId, int $patientId, string $type): void
    {
        $prefix      = $type; // 'pathology' or 'radiology'
        $typeValue   = $type === 'pathology' ? 'pathology' : 'radiology';
        $source      = $type === 'pathology' ? 'front_desk_pathology' : 'front_desk_radiology';
        $chargeModule = $type;

        $order = LabInvestigationOrder::create([
            'patient_id'   => $patientId,
            'case_id'      => $caseId,
            'doctor_id'    => $request->input("{$prefix}_doctor_id") ?: null,
            'datetime'     => now(),
            'remarks'      => $request->input("{$prefix}_remarks"),
            'priority'     => $type === 'pathology' ? ($request->input('pathology_priority') ?? 'Regular') : null,
            'source'       => $source,
            'type'         => $typeValue,
            'generated_by' => auth()->id(),
        ]);

        $typeId = $request->input("{$prefix}_lab_inv_type_id");

        foreach ($request->input("{$prefix}_requests", []) as $row) {
            if (empty($row['lab_inv'])) continue;

            LabInvestigationOrderRequest::create([
                'lab_inv_order_id'    => $order->id,
                'lab_inv_id'          => $row['lab_inv'],
                'lab_inv_type_id'     => $typeId,
                'lab_inv_category_id' => $row['lab_inv_category_id'] ?? null,
                'status'              => 'Pending',
            ]);

            $investigation = LabInvestigation::find($row['lab_inv']);
            if (!$investigation) continue;

            $unitPrice = (float) ($investigation->price ?? 0);
            $amount    = $unitPrice;

            PatientCharge::create([
                'case_id'        => $caseId,
                'charge_module'  => $chargeModule,
                "{$prefix}_id"   => $order->id,
                'doctor_id'      => $order->doctor_id,
                'charge_item'    => $investigation->name,
                'unit_price'     => $unitPrice,
                'quantity'       => 1,
                'amount'         => $amount,
                'vat'            => 0,
                'tax'            => 0,
                'net_amount'     => $amount,
                'date'           => now(),
                'notes'          => $order->remarks,
                'status'         => 'pending',
                'created_by'     => auth()->id(),
            ]);
        }
    }
}
