<?php
namespace App\Http\Controllers\ICU;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Icu\IcuAdmission;
use App\Models\Icu\IcuProcedureOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IcuProcedureOrderController extends Controller
{
    public function index($admissionId)
    {
        $admission = IcuAdmission::with('patient')->findOrFail($admissionId);

        $orders = IcuProcedureOrder::with('doctor')
            ->where('icu_admission_id', $admission->id)
            ->orderByDesc('id')
            ->get();

        $doctors        = Doctor::select('id', 'name')->orderBy('name')->get();
        $categoryTypes  = IcuProcedureOrder::$categoryTypes;

        return view('icu.procedure-orders.index', compact('admission', 'orders', 'doctors', 'categoryTypes'));
    }

    public function store(Request $request, $admissionId)
    {
        $admission = IcuAdmission::findOrFail($admissionId);

        $validated = $request->validate([
            'doctor_id'      => 'nullable|exists:doctors,id',
            'category'       => 'required|string|max:50',
            'type'           => 'required|string|max:100',
            'priority'       => 'nullable|string|max:30',
            'start_datetime' => 'nullable|date',
            'details'        => 'nullable|string',
            'status'         => 'nullable|string|max:30',
            'remarks'        => 'nullable|string',
        ]);

        IcuProcedureOrder::create([
            'icu_admission_id' => $admission->id,
            'icu_case_id'      => $admission->icu_case_id,
            'patient_id'       => $admission->patient_id,
            'doctor_id'        => $validated['doctor_id'] ?? $admission->referring_doctor_id,
            'category'         => $validated['category'],
            'type'             => $validated['type'],
            'priority'         => $validated['priority'] ?? 'Routine',
            'start_datetime'   => $validated['start_datetime'] ?? null,
            'details'          => $validated['details'] ?? null,
            'status'           => $validated['status'] ?? 'Scheduled',
            'remarks'          => $validated['remarks'] ?? null,
            'created_by'       => Auth::id(),
        ]);

        return redirect()->route('icu.admissions.procedure-orders.index', $admission->id)
            ->with('success', 'Procedure order added successfully.');
    }

    public function edit($admissionId, $orderId)
    {
        $admission = IcuAdmission::with('patient')->findOrFail($admissionId);
        $order     = IcuProcedureOrder::where('icu_admission_id', $admission->id)->findOrFail($orderId);
        $doctors   = Doctor::select('id', 'name')->orderBy('name')->get();
        $categoryTypes = IcuProcedureOrder::$categoryTypes;

        return view('icu.procedure-orders.edit', compact('admission', 'order', 'doctors', 'categoryTypes'));
    }

    public function update(Request $request, $admissionId, $orderId)
    {
        $admission = IcuAdmission::findOrFail($admissionId);
        $order     = IcuProcedureOrder::where('icu_admission_id', $admission->id)->findOrFail($orderId);

        $validated = $request->validate([
            'doctor_id'      => 'nullable|exists:doctors,id',
            'category'       => 'required|string|max:50',
            'type'           => 'required|string|max:100',
            'priority'       => 'nullable|string|max:30',
            'start_datetime' => 'nullable|date',
            'details'        => 'nullable|string',
            'status'         => 'nullable|string|max:30',
            'remarks'        => 'nullable|string',
        ]);

        $order->update([
            'doctor_id'      => $validated['doctor_id'] ?? $admission->referring_doctor_id,
            'category'       => $validated['category'],
            'type'           => $validated['type'],
            'priority'       => $validated['priority'] ?? 'Routine',
            'start_datetime' => $validated['start_datetime'] ?? null,
            'details'        => $validated['details'] ?? null,
            'status'         => $validated['status'] ?? 'Scheduled',
            'remarks'        => $validated['remarks'] ?? null,
        ]);

        return redirect()->route('icu.admissions.procedure-orders.index', $admission->id)
            ->with('success', 'Procedure order updated successfully.');
    }

    public function destroy($admissionId, $orderId)
    {
        $admission = IcuAdmission::findOrFail($admissionId);
        $order     = IcuProcedureOrder::where('icu_admission_id', $admission->id)->findOrFail($orderId);
        $order->delete();

        return redirect()->route('icu.admissions.procedure-orders.index', $admission->id)
            ->with('success', 'Procedure order deleted successfully.');
    }
}
