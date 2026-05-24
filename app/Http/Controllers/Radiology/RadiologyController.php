<?php

namespace App\Http\Controllers\Radiology;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\CaseReference;
use App\Models\Ipd\LabInvestigationOrder;
use App\Models\Ipd\LabInvestigationOrderRequest;
use App\Models\LabInvestigation;
use App\Models\LabInvestigationCategory;
use App\Models\LabInvestigationType;
use App\Models\Patient;
use App\Models\PatientCharge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RadiologyController extends Controller
{
    public function index()
    {
        $orders = LabInvestigationOrder::with([
                'patient',
                'doctor',
                'generatedBy',
                'requests.labInvestigation',
                'requests.labInvestigationCategory',
                'requests.labInvestigationType',
            ])
            ->where('type', 'radiology')
            ->latest('datetime')
            ->get();

        $patients       = Patient::select('id', 'patient_name', 'mrn')->orderBy('patient_name')->get();
        $doctors        = Doctor::select('id', 'name')->orderBy('name')->get();
        $radiologyType  = LabInvestigationType::where('name', 'Radiology')->select('id', 'name')->first();
        $categories     = $radiologyType
            ? LabInvestigationCategory::where('type_id', $radiologyType->id)
                ->select('id', 'type_id', 'name')->orderBy('name')->get()
            : collect();
        $investigations = $categories->isNotEmpty()
            ? LabInvestigation::whereIn('category_id', $categories->pluck('id'))
                ->select('id', 'category_id', 'name')->orderBy('name')->get()
            : collect();

        return view('radiology.index', compact(
            'orders', 'patients', 'doctors', 'radiologyType', 'categories', 'investigations'
        ));
    }

    public function show($id)
    {
        $order = LabInvestigationOrder::with([
                'patient',
                'doctor',
                'generatedBy',
                'requests.labInvestigation',
                'requests.labInvestigationCategory',
                'requests.labInvestigationType',
            ])
            ->where('type', 'radiology')
            ->findOrFail($id);

        return view('radiology.show', compact('order'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id'                     => 'required|exists:patients,id',
            'case_id'                        => 'nullable|exists:case_references,id',
            'opd_id'                         => 'nullable|exists:opd_patients,id',
            'doctor_id'                      => 'nullable|exists:doctors,id',
            'datetime'                       => 'nullable|date',
            'collected_by'                   => 'nullable|string|max:255',
            'lab_name'                       => 'nullable|string|max:255',
            'remarks'                        => 'nullable|string',
            'lab_inv_type_id'                => 'required|exists:lab_investigation_types,id',
            'requests'                       => 'required|array|min:1',
            'requests.*.lab_inv'             => 'required|exists:lab_investigations,id',
            'requests.*.lab_inv_category_id' => 'nullable|exists:lab_investigation_categories,id',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $caseId = $validated['case_id'] ?? null;
                if (empty($caseId)) {
                    $caseId = CaseReference::create([])->id;
                }

                $order = LabInvestigationOrder::create([
                    'patient_id'   => $validated['patient_id'],
                    'case_id'      => $caseId,
                    'doctor_id'    => $validated['doctor_id'] ?? null,
                    'datetime'     => $validated['datetime'] ?? null,
                    'remarks'      => $validated['remarks'] ?? null,
                    'collected_by' => $validated['collected_by'] ?? null,
                    'lab_name'     => $validated['lab_name'] ?? null,
                    'source'       => 'radiology',
                    'type'         => 'radiology',
                    'generated_by' => auth()->id(),
                ]);

                foreach ($validated['requests'] as $row) {
                    LabInvestigationOrderRequest::create([
                        'lab_inv_order_id'    => $order->id,
                        'lab_inv_id'          => $row['lab_inv'],
                        'lab_inv_type_id'     => $validated['lab_inv_type_id'],
                        'lab_inv_category_id' => $row['lab_inv_category_id'] ?? null,
                    ]);

                    $investigation = LabInvestigation::find($row['lab_inv']);
                    if (!$investigation) continue;

                    $unitPrice = (float) ($investigation->price ?? 0);
                    $quantity  = 1;
                    $amount    = $unitPrice * $quantity;

                    PatientCharge::create([
                        'case_id'       => $order->case_id,
                        'charge_module' => 'radiology',
                        'radiology_id'  => $order->id,
                        'opd_id'        => $validated['opd_id'] ?? null,
                        'doctor_id'     => $order->doctor_id,
                        'charge_item'   => $investigation->name,
                        'unit_price'    => $unitPrice,
                        'quantity'      => $quantity,
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

            $redirectTo = $request->input('redirect_to');
            $target = ($redirectTo && str_starts_with($redirectTo, '/'))
                ? $redirectTo
                : route('radiology.index');

            return redirect($target)->with('success', 'Radiology order saved successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Failed to save radiology order: ' . $e->getMessage());
        }
    }

    public function uploadFile(Request $request, $orderId, $requestId)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
        ]);

        $orderRequest = LabInvestigationOrderRequest::where('lab_inv_order_id', $orderId)
            ->findOrFail($requestId);

        if ($orderRequest->file && Storage::disk('public')->exists($orderRequest->file)) {
            Storage::disk('public')->delete($orderRequest->file);
        }

        $path = $request->file('file')->store('radiology/files', 'public');
        $orderRequest->update([
            'file'   => $path,
            'status' => 'Result Ready',
        ]);

        return redirect()->route('radiology.show', $orderId)
            ->with('success', 'File uploaded successfully.');
    }

    public function updateStatus(Request $request, $orderId, $requestId)
    {
        $validated = $request->validate([
            'status' => 'required|in:Pending,Sample Collected,In Process,Completed,Result Ready',
        ]);

        $orderRequest = LabInvestigationOrderRequest::where('lab_inv_order_id', $orderId)
            ->findOrFail($requestId);

        $orderRequest->update(['status' => $validated['status']]);

        return response()->json([
            'success' => true,
            'status'  => $orderRequest->status,
        ]);
    }

    public function deleteFile($orderId, $requestId)
    {
        $orderRequest = LabInvestigationOrderRequest::where('lab_inv_order_id', $orderId)
            ->findOrFail($requestId);

        if ($orderRequest->file && Storage::disk('public')->exists($orderRequest->file)) {
            Storage::disk('public')->delete($orderRequest->file);
        }

        $orderRequest->update(['file' => null]);

        return redirect()->route('radiology.show', $orderId)
            ->with('success', 'File removed.');
    }
}
