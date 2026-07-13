<?php
namespace App\Http\Controllers\IPD;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\IpdPatient;
use App\Models\Ipd\LabInvestigationOrder;
use App\Models\Ipd\LabInvestigationOrderRequest;
use App\Models\LabInvestigation;
use App\Models\LabInvestigationCategory;
use App\Models\LabInvestigationType;
use App\Models\PatientCharge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RadiologyOrderController extends Controller
{
    protected string $orderType = 'radiology';

    public function create($id)
    {
        $ipdPatient         = IpdPatient::with('patient')->findOrFail($id);
        $doctors            = Doctor::select('id', 'name')->orderBy('name')->get();
        $radiologyType      = LabInvestigationType::firstOrCreate(['name' => 'Radiology']);
        $investigationTypes = collect([$radiologyType]);
        $categories         = LabInvestigationCategory::where('type_id', $radiologyType->id)
            ->select('id', 'type_id', 'name')->orderBy('name')->get();
        $investigations = $categories->isNotEmpty()
            ? LabInvestigation::whereIn('category_id', $categories->pluck('id'))
                ->select('id', 'category_id', 'name')->orderBy('name')->get()
            : collect();

        return view('ipd_patients.radiology-order.create', compact(
            'id',
            'ipdPatient',
            'doctors',
            'investigationTypes',
            'categories',
            'investigations'
        ));
    }

    public function store(Request $request, $id)
    {
        $validated = $this->validateRequest($request);

        try {
            $ipdPatient = IpdPatient::findOrFail($id);

            DB::transaction(function () use ($validated, $ipdPatient, $request) {
                $order = LabInvestigationOrder::create([
                    'ipd_id'       => $ipdPatient->id,
                    'case_id'      => $ipdPatient->case_id,
                    'patient_id'   => $ipdPatient->patient_id,
                    'doctor_id'    => $validated['doctor_id'] ?? null,
                    'datetime'     => $validated['datetime'] ?? null,
                    'remarks'      => $validated['remarks'] ?? null,
                    'priority'     => $validated['priority'] ?? 'Regular',
                    'collected_by' => $validated['collected_by'] ?? null,
                    'lab_name'     => $validated['lab_name'] ?? null,
                    'source'       => 'ipd',
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
                        'case_id'       => $ipdPatient->case_id,
                        'charge_module' => 'ipd',
                        'ipd_id'        => $ipdPatient->id,
                        'radiology_id'  => $order->id,
                        'doctor_id'     => $order->doctor_id,
                        'department_id' => $ipdPatient->department_id,
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

            // return redirect()->route('ipd-patients.ipd-patients.show', $id)
            //     ->with('success', 'Radiology order saved successfully.');

            return redirect(route('ipd-patients.ipd-patients.show', $id) . '?tab=radiology')
                ->with('success', 'Radiology order saved successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Failed to save radiology order: ' . $e->getMessage());
        }
    }

    public function show($id, $orderId)
    {
        $ipdPatient = IpdPatient::with('patient')->findOrFail($id);
        $order      = LabInvestigationOrder::with([
            'requests.labInvestigation',
            'requests.labInvestigationType',
            'requests.labInvestigationCategory',
            'doctor',
            'generatedBy',
        ])->where('ipd_id', $id)->where('type', $this->orderType)->findOrFail($orderId);

        return view('ipd_patients.radiology-order.show', compact('ipdPatient', 'order'));
    }

    public function edit($id, $orderId)
    {
        $ipdPatient = IpdPatient::with('patient')->findOrFail($id);
        $order      = LabInvestigationOrder::with('requests')
            ->where('ipd_id', $id)
            ->where('type', $this->orderType)->findOrFail($orderId);
        $doctors            = Doctor::select('id', 'name')->orderBy('name')->get();
        $radiologyType      = LabInvestigationType::firstOrCreate(['name' => 'Radiology']);
        $investigationTypes = collect([$radiologyType]);
        $categories         = LabInvestigationCategory::where('type_id', $radiologyType->id)
            ->select('id', 'type_id', 'name')->orderBy('name')->get();
        $investigations = $categories->isNotEmpty()
            ? LabInvestigation::whereIn('category_id', $categories->pluck('id'))
                ->select('id', 'category_id', 'name')->orderBy('name')->get()
            : collect();

        return view('ipd_patients.radiology-order.edit', compact(
            'id',
            'ipdPatient',
            'order',
            'doctors',
            'investigationTypes',
            'categories',
            'investigations'
        ));
    }

    public function update(Request $request, $id, $orderId)
    {
        $validated = $this->validateRequest($request);

        try {
            $order = LabInvestigationOrder::where('ipd_id', $id)
                ->where('type', $this->orderType)->findOrFail($orderId);
            $ipdPatient = IpdPatient::findOrFail($id);

            DB::transaction(function () use ($validated, $order, $ipdPatient, $request) {
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
                PatientCharge::where('charge_module', 'ipd')
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
                        'case_id'       => $ipdPatient->case_id,
                        'charge_module' => 'ipd',
                        'ipd_id'        => $ipdPatient->id,
                        'radiology_id'  => $order->id,
                        'doctor_id'     => $order->doctor_id,
                        'department_id' => $ipdPatient->department_id,
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

            // return redirect()->route('ipd-patients.ipd-patients.show', $id)
            //     ->with('success', 'Radiology order updated successfully.');

            return redirect(route('ipd-patients.ipd-patients.show', $id) . '?tab=radiology')
                ->with('success', 'Radiology order updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Failed to update radiology order: ' . $e->getMessage());
        }
    }

    public function destroy($id, $orderId)
    {
        try {
            $order = LabInvestigationOrder::where('ipd_id', $id)
                ->where('type', $this->orderType)->findOrFail($orderId);
            $order->delete();

            // return redirect()->route('ipd-patients.ipd-patients.show', $id)
            //     ->with('success', 'Radiology order deleted successfully.');

            return redirect(route('ipd-patients.ipd-patients.show', $id) . '?tab=radiology')
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
