<?php
namespace App\Http\Controllers\ICU;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Icu\IcuAdmission;
use App\Models\Ipd\LabInvestigationOrder;
use App\Models\Ipd\LabInvestigationOrderRequest;
use App\Models\LabInvestigation;
use App\Models\LabInvestigationCategory;
use App\Models\LabInvestigationType;
use App\Models\PatientCharge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IcuRadiologyOrderController extends Controller
{
    protected string $orderType = 'radiology';

    public function index($admissionId)
    {
        $admission = IcuAdmission::with('patient')->findOrFail($admissionId);

        $orders = LabInvestigationOrder::with(['requests.labInvestigation', 'requests.labInvestigationCategory', 'doctor'])
            ->where('case_id', $admission->case_id)
            ->where('source', $admission->unitKey())
            ->where('type', $this->orderType)
            ->orderByDesc('datetime')
            ->get();

        return view('icu.radiology-order.index', compact('admission', 'orders'));
    }

    public function create($admissionId)
    {
        $admission          = IcuAdmission::with('patient')->findOrFail($admissionId);
        $doctors            = Doctor::select('id', 'name')->orderBy('name')->get();
        $radiologyType      = LabInvestigationType::firstOrCreate(['name' => 'Radiology']);
        $investigationTypes = collect([$radiologyType]);
        $categories         = LabInvestigationCategory::where('type_id', $radiologyType->id)
            ->select('id', 'type_id', 'name')->orderBy('name')->get();
        $investigations = $categories->isNotEmpty()
            ? LabInvestigation::whereIn('category_id', $categories->pluck('id'))
                ->select('id', 'category_id', 'name')->orderBy('name')->get()
            : collect();

        return view('icu.radiology-order.create', compact(
            'admission',
            'doctors',
            'investigationTypes',
            'categories',
            'investigations'
        ));
    }

    public function store(Request $request, $admissionId)
    {
        $validated = $this->validateRequest($request);

        try {
            $admission = IcuAdmission::findOrFail($admissionId);

            DB::transaction(function () use ($validated, $admission, $request) {
                $order = LabInvestigationOrder::create([
                    'ipd_id'       => $admission->ipdIdForCharge(),
                    'case_id'      => $admission->case_id,
                    'patient_id'   => $admission->patient_id,
                    'doctor_id'    => $validated['doctor_id'] ?? null,
                    'datetime'     => $validated['datetime'] ?? null,
                    'remarks'      => $validated['remarks'] ?? null,
                    'priority'     => $validated['priority'] ?? 'Regular',
                    'collected_by' => $validated['collected_by'] ?? null,
                    'lab_name'     => $validated['lab_name'] ?? null,
                    'source'       => $admission->unitKey(),
                    'type'         => $this->orderType,
                    'generated_by' => auth()->id(),
                ]);

                $typeId = $request->input('lab_inv_type_id');

                foreach ($validated['requests'] as $row) {
                    LabInvestigationOrderRequest::create([
                        'lab_inv_order_id'    => $order->id,
                        'lab_inv_id'          => $row['lab_inv'],
                        'lab_inv_type_id'     => $typeId,
                        'lab_inv_category_id' => $row['lab_inv_category_id'] ?? null,
                    ]);

                    $investigation = LabInvestigation::find($row['lab_inv']);
                    if (! $investigation) {
                        continue;
                    }

                    $unitPrice = (float) ($investigation->price ?? 0);
                    $amount    = $unitPrice * 1;

                    PatientCharge::create([
                        'case_id'       => $admission->case_id,
                        'charge_module' => 'icu',
                        'ipd_id'        => $admission->ipdIdForCharge(),
                        'radiology_id'  => $order->id,
                        'doctor_id'     => $order->doctor_id,
                        'department_id' => null,
                        'charge_item'   => $investigation->name,
                        'unit_price'    => $unitPrice,
                        'quantity'      => 1,
                        'amount'        => $amount,
                        'vat'           => 0,
                        'tax'           => 0,
                        'net_amount'    => $amount,
                        'date'          => $order->datetime ?? now(),
                        'notes'         => $order->remarks,
                        'status'        => 'pending',
                        'created_by'    => auth()->id(),
                    ]);
                }
            });

            return redirect()->route('icu.admissions.show', $admissionId)
                ->with('success', 'Radiology order saved successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Failed to save radiology order: ' . $e->getMessage());
        }
    }

    public function show($admissionId, $orderId)
    {
        $admission = IcuAdmission::with('patient')->findOrFail($admissionId);
        $order     = LabInvestigationOrder::with([
            'requests.labInvestigation',
            'requests.labInvestigationType',
            'requests.labInvestigationCategory',
            'doctor',
            'generatedBy',
        ])->where('case_id', $admission->case_id)
            ->where('source', $admission->unitKey())
            ->where('type', $this->orderType)
            ->findOrFail($orderId);

        return view('icu.radiology-order.show', compact('admission', 'order'));
    }

    public function edit($admissionId, $orderId)
    {
        $admission = IcuAdmission::with('patient')->findOrFail($admissionId);
        $order     = LabInvestigationOrder::with('requests')
            ->where('case_id', $admission->case_id)
            ->where('source', $admission->unitKey())
            ->where('type', $this->orderType)
            ->findOrFail($orderId);
        $doctors            = Doctor::select('id', 'name')->orderBy('name')->get();
        $radiologyType      = LabInvestigationType::firstOrCreate(['name' => 'Radiology']);
        $investigationTypes = collect([$radiologyType]);
        $categories         = LabInvestigationCategory::where('type_id', $radiologyType->id)
            ->select('id', 'type_id', 'name')->orderBy('name')->get();
        $investigations = $categories->isNotEmpty()
            ? LabInvestigation::whereIn('category_id', $categories->pluck('id'))
                ->select('id', 'category_id', 'name')->orderBy('name')->get()
            : collect();

        return view('icu.radiology-order.edit', compact(
            'admission',
            'order',
            'doctors',
            'investigationTypes',
            'categories',
            'investigations'
        ));
    }

    public function update(Request $request, $admissionId, $orderId)
    {
        $validated = $this->validateRequest($request);

        try {
            $admission = IcuAdmission::findOrFail($admissionId);
            $order     = LabInvestigationOrder::where('case_id', $admission->case_id)
                ->where('source', $admission->unitKey())
                ->where('type', $this->orderType)
                ->findOrFail($orderId);

            DB::transaction(function () use ($validated, $order, $admission, $request) {
                $order->update([
                    'doctor_id'    => $validated['doctor_id'] ?? null,
                    'datetime'     => $validated['datetime'] ?? null,
                    'remarks'      => $validated['remarks'] ?? null,
                    'priority'     => $validated['priority'] ?? 'Regular',
                    'collected_by' => $validated['collected_by'] ?? null,
                    'lab_name'     => $validated['lab_name'] ?? null,
                ]);

                $typeId = $request->input('lab_inv_type_id');

                $order->requests()->delete();
                PatientCharge::where('charge_module', 'icu')
                    ->where('radiology_id', $order->id)
                    ->delete();

                foreach ($validated['requests'] as $row) {
                    LabInvestigationOrderRequest::create([
                        'lab_inv_order_id'    => $order->id,
                        'lab_inv_id'          => $row['lab_inv'],
                        'lab_inv_type_id'     => $typeId,
                        'lab_inv_category_id' => $row['lab_inv_category_id'] ?? null,
                    ]);

                    $investigation = LabInvestigation::find($row['lab_inv']);
                    if (! $investigation) {
                        continue;
                    }

                    $unitPrice = (float) ($investigation->price ?? 0);
                    $amount    = $unitPrice * 1;

                    PatientCharge::create([
                        'case_id'       => $admission->case_id,
                        'charge_module' => 'icu',
                        'ipd_id'        => $admission->ipdIdForCharge(),
                        'radiology_id'  => $order->id,
                        'doctor_id'     => $order->doctor_id,
                        'department_id' => null,
                        'charge_item'   => $investigation->name,
                        'unit_price'    => $unitPrice,
                        'quantity'      => 1,
                        'amount'        => $amount,
                        'vat'           => 0,
                        'tax'           => 0,
                        'net_amount'    => $amount,
                        'date'          => $order->datetime ?? now(),
                        'notes'         => $order->remarks,
                        'status'        => 'pending',
                        'created_by'    => auth()->id(),
                    ]);
                }
            });

            return redirect()->route('icu.admissions.show', $admissionId)
                ->with('success', 'Radiology order updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Failed to update radiology order: ' . $e->getMessage());
        }
    }

    public function destroy($admissionId, $orderId)
    {
        try {
            $admission = IcuAdmission::findOrFail($admissionId);
            $order     = LabInvestigationOrder::where('case_id', $admission->case_id)
                ->where('source', $admission->unitKey())
                ->where('type', $this->orderType)
                ->findOrFail($orderId);

            DB::transaction(function () use ($order) {
                PatientCharge::where('charge_module', 'icu')
                    ->where('radiology_id', $order->id)
                    ->delete();
                $order->requests()->delete();
                $order->delete();
            });

            return redirect()->route('icu.admissions.show', $admissionId)
                ->with('success', 'Radiology order deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete radiology order: ' . $e->getMessage());
        }
    }

    protected function validateRequest(Request $request): array
    {
        return $request->validate([
            'doctor_id'                      => 'nullable|exists:doctors,id',
            'datetime'                       => 'nullable|date',
            'collected_by'                   => 'nullable|string|max:255',
            'lab_name'                       => 'nullable|string|max:255',
            'remarks'                        => 'nullable|string',
            'priority'                       => 'nullable|in:Regular,Urgent,STAT',
            'requests'                       => 'required|array|min:1',
            'requests.*.lab_inv'             => 'required|exists:lab_investigations,id',
            'requests.*.lab_inv_category_id' => 'nullable|exists:lab_investigation_categories,id',
        ]);
    }
}
