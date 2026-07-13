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

/**
 * Unified Lab Order — replaces the separate Pathology + Radiology controllers
 * with a single form that supports ALL 9 lab investigation types and adds
 * multiple investigations in one order. Each investigation auto-creates a
 * patient_charge row priced from lab_investigations.price.
 */
class LabOrderController extends Controller
{
    public function create(Request $request, $id)
    {
        $ipd = IpdPatient::with('patient')->findOrFail($id);

        $types = LabInvestigationType::orderBy('name')->get(['id', 'name']);
        $categories = LabInvestigationCategory::with('type:id,name')
            ->orderBy('name')->get(['id', 'type_id', 'name']);
        $investigations = LabInvestigation::orderBy('name')
            ->get(['id', 'category_id', 'name', 'short_name', 'sample_type', 'price', 'report_time_hours']);
        $doctors = Doctor::orderBy('name')->get(['id', 'name']);

        return view('ipd_patients.lab-order.create', compact(
            'ipd', 'types', 'categories', 'investigations', 'doctors'
        ));
    }

    public function store(Request $request, $id)
    {
        $data = $request->validate([
            'doctor_id'         => 'nullable|exists:doctors,id',
            'datetime'          => 'nullable|date',
            'priority'          => 'nullable|in:Regular,Urgent,STAT',
            'remarks'           => 'nullable|string|max:1000',
            'lab_name'          => 'nullable|string|max:100',
            'requests'          => 'required|array|min:1',
            'requests.*.lab_inv'      => 'required|exists:lab_investigations,id',
            'requests.*.type_id'      => 'nullable|exists:lab_investigation_types,id',
            'requests.*.category_id'  => 'nullable|exists:lab_investigation_categories,id',
        ]);

        $ipd = IpdPatient::findOrFail($id);

        // Group requests by the type of the underlying investigation, so each
        // resulting LabInvestigationOrder is single-type. This keeps the existing
        // pathology/radiology-specific views working.
        $invIdToType = [];
        $invIdToCat  = [];
        foreach ($data['requests'] as $r) {
            $inv = LabInvestigation::with('category.type')->find($r['lab_inv']);
            if (! $inv) continue;
            $invIdToType[$inv->id] = optional(optional($inv->category)->type)->name ?: 'pathology';
            $invIdToCat[$inv->id]  = $inv->category_id;
        }
        $byType = collect($data['requests'])->groupBy(fn ($r) => strtolower($invIdToType[$r['lab_inv']] ?? 'pathology'));

        $created = [];
        DB::transaction(function () use ($byType, $data, $ipd, &$created, $invIdToCat) {
            foreach ($byType as $type => $rows) {
                $order = LabInvestigationOrder::create([
                    'order_number' => strtoupper(substr($type, 0, 4)) . '-IPD-' . $ipd->id . '-' . str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT),
                    'ipd_id'       => $ipd->id,
                    'case_id'      => $ipd->case_id,
                    'patient_id'   => $ipd->patient_id,
                    'doctor_id'    => $data['doctor_id'] ?? null,
                    'datetime'     => $data['datetime'] ?? now(),
                    'remarks'      => $data['remarks'] ?? null,
                    'priority'     => $data['priority'] ?? 'Regular',
                    'lab_name'     => $data['lab_name'] ?? null,
                    'source'       => 'ipd',
                    'type'         => $this->normalizeType($type),
                    'generated_by' => auth()->id(),
                ]);
                foreach ($rows as $r) {
                    $catId = $invIdToCat[$r['lab_inv']] ?? null;
                    LabInvestigationOrderRequest::create([
                        'lab_inv_order_id'    => $order->id,
                        'lab_inv_id'          => $r['lab_inv'],
                        'lab_inv_type_id'     => $r['type_id'] ?? null,
                        'lab_inv_category_id' => $catId,
                    ]);
                    $inv = LabInvestigation::find($r['lab_inv']);
                    if ($inv && $inv->price > 0) {
                        PatientCharge::create([
                            'case_id'       => $ipd->case_id,
                            'charge_module' => 'ipd',
                            'ipd_id'        => $ipd->id,
                            'pathology_id'  => $order->id,
                            'doctor_id'     => $order->doctor_id,
                            'department_id' => $ipd->department_id,
                            'charge_item'   => $inv->name,
                            'unit_price'    => (float) $inv->price,
                            'quantity'      => 1,
                            'amount'        => (float) $inv->price,
                            'net_amount'    => (float) $inv->price,
                            'date'          => $order->datetime ?? now(),
                            'notes'         => $order->remarks,
                            'status'        => 'pending',
                            'created_by'    => auth()->id(),
                        ]);
                    }
                }
                $created[] = $order->order_number;
            }
        });

        return redirect(route('ipd-patients.show', $ipd->id) . '?tab=lab')
            ->with('success', count($created) . ' lab order(s) created: ' . implode(', ', $created));
    }

    /**
     * Normalize the type string for the lab_investigation_order.type enum.
     * Anything not in the strict enum falls back to 'pathology' or 'radiology'.
     */
    private function normalizeType(string $t): string
    {
        $t = strtolower($t);
        if (str_contains($t, 'radiology')) return 'radiology';
        return 'pathology'; // covers pathology/micro/histo/cyto/immuno/endocrino/cardio/genetics
    }
}
