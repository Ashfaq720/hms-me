<?php

namespace App\Http\Controllers\OPD;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Ipd\LabInvestigationOrder;
use App\Models\Ipd\LabInvestigationOrderRequest;
use App\Models\LabInvestigation;
use App\Models\LabInvestigationCategory;
use App\Models\LabInvestigationType;
use App\Models\OpdPatient;
use App\Models\PatientCharge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Unified Lab Order for OPD — replaces the old separate Pathology/Radiology
 * modals with a single form that supports ALL 9 lab investigation types
 * (Pathology · Radiology · Microbiology · Histopathology · Cytology ·
 * Immunology · Endocrinology · Cardiology · Genetics).
 *
 * Mirror of \App\Http\Controllers\IPD\LabOrderController so the workflow
 * is identical across OPD and IPD.
 */
class OpdLabOrderController extends Controller
{
    public function create($id)
    {
        $opd = OpdPatient::with('patient')->findOrFail($id);

        $types          = LabInvestigationType::orderBy('name')->get(['id', 'name']);
        $categories     = LabInvestigationCategory::with('type:id,name')
            ->orderBy('name')->get(['id', 'type_id', 'name']);
        $investigations = LabInvestigation::orderBy('name')
            ->get(['id', 'category_id', 'name', 'short_name', 'sample_type', 'price', 'report_time_hours']);
        $doctors        = Doctor::orderBy('name')->get(['id', 'name']);

        return view('opd_patients.lab-order.create', compact(
            'opd', 'types', 'categories', 'investigations', 'doctors'
        ));
    }

    public function store(Request $request, $id)
    {
        $data = $request->validate([
            'doctor_id'              => 'nullable|exists:doctors,id',
            'datetime'               => 'nullable|date',
            'priority'               => 'nullable|in:Regular,Urgent,STAT',
            'remarks'                => 'nullable|string|max:1000',
            'lab_name'               => 'nullable|string|max:100',
            'requests'               => 'required|array|min:1',
            'requests.*.lab_inv'     => 'required|exists:lab_investigations,id',
            'requests.*.type_id'     => 'nullable|exists:lab_investigation_types,id',
            'requests.*.category_id' => 'nullable|exists:lab_investigation_categories,id',
        ]);

        $opd = OpdPatient::findOrFail($id);

        // Group requested investigations by underlying type so each LabInvestigationOrder
        // remains single-type (keeps the existing pathology/radiology-specific views happy).
        $invIdToType = [];
        $invIdToCat  = [];
        foreach ($data['requests'] as $r) {
            $inv = LabInvestigation::with('category.type')->find($r['lab_inv']);
            if (! $inv) continue;
            $invIdToType[$inv->id] = optional(optional($inv->category)->type)->name ?: 'pathology';
            $invIdToCat[$inv->id]  = $inv->category_id;
        }
        $byType = collect($data['requests'])->groupBy(
            fn ($r) => strtolower($invIdToType[$r['lab_inv']] ?? 'pathology')
        );

        $created = [];
        DB::transaction(function () use ($byType, $data, $opd, &$created, $invIdToCat) {
            foreach ($byType as $type => $rows) {
                $order = LabInvestigationOrder::create([
                    'order_number' => strtoupper(substr($type, 0, 4)) . '-OPD-' . $opd->id . '-' . str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT),
                    'opd_id'       => $opd->id,
                    'case_id'      => $opd->case_id,
                    'patient_id'   => $opd->patient_id,
                    'doctor_id'    => $data['doctor_id'] ?? null,
                    'datetime'     => $data['datetime'] ?? now(),
                    'remarks'      => $data['remarks'] ?? null,
                    'priority'     => $data['priority'] ?? 'Regular',
                    'lab_name'     => $data['lab_name'] ?? null,
                    'source'       => 'opd',
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
                            'case_id'       => $opd->case_id,
                            'charge_module' => 'opd',
                            'opd_id'        => $opd->id,
                            'pathology_id'  => $order->id,
                            'doctor_id'     => $order->doctor_id,
                            'department_id' => $opd->department_id,
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

        return redirect(route('opd-patients.show', $opd->id) . '?tab=lab')
            ->with('success', count($created) . ' lab order(s) created: ' . implode(', ', $created));
    }

    private function normalizeType(string $t): string
    {
        $t = strtolower($t);
        if (str_contains($t, 'radiology')) return 'radiology';
        return 'pathology';
    }
}
