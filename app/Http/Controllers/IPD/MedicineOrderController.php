<?php
namespace App\Http\Controllers\IPD;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\IpdPatient;
use App\Models\Ipd\MedicineOrder;
use App\Models\Pharmacy\Medicine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MedicineOrderController extends Controller
{
    public function create($id)
    {
        $ipdPatient = IpdPatient::with(['patient', 'doctor'])->findOrFail($id);
        $medicines  = Medicine::where('status', true)->select('id', 'medicine_unit_id', 'medicine_category_id', 'medical_group_id', 'medicine_name')->with(['unit', 'category', 'medicalGroup'])->get();
        $doctors    = Doctor::select('id', 'name')->orderBy('name')->get();

        return view('ipd_patients.medicine-orders.create', compact('ipdPatient', 'medicines', 'doctors'));
    }

    public function store(Request $request, $id)
    {
        $ipdPatient = IpdPatient::findOrFail($id);

        $validated = $request->validate([
            'orders'                 => 'required|array|min:1',
            'orders.*.medicine_id'   => 'required|exists:medicines,id',
            'orders.*.qty'           => 'required|integer|min:1',
            'orders.*.prescribed_by' => 'nullable|exists:doctors,id',
            'orders.*.status'        => 'nullable|string|max:255',
            'orders.*.order_by'      => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            foreach ($validated['orders'] as $order) {
                MedicineOrder::create([
                    'medicine_id'   => $order['medicine_id'],
                    'qty'           => $order['qty'],
                    'prescribed_by' => $order['prescribed_by'] ?? $ipdPatient->doctor_id,
                    'patient_id'    => $ipdPatient->patient_id,
                    'ipd_id'        => $ipdPatient->id,
                    'case_id'       => $ipdPatient->case_id,
                    'status'        => $order['status'] ?? 'pending',
                    'order_by'      => $order['order_by'] ?? null,
                ]);
            }

            DB::commit();

            // return redirect()
            //     ->route('ipd-patients.ipd-patients.show', $ipdPatient->id)
            //     ->with('success', 'Medicine orders created successfully.');

            return redirect(route('ipd-patients.ipd-patients.show', $ipdPatient->id) . '?tab=medicine-order')
                ->with('success', 'Medicine orders created successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to save medicine orders: ' . $e->getMessage());
        }
    }

    public function edit($ipdPatientId, $orderId)
    {
        $ipdPatient    = IpdPatient::with(['patient', 'doctor'])->findOrFail($ipdPatientId);
        $medicineOrder = MedicineOrder::where('ipd_id', $ipdPatientId)->findOrFail($orderId);
        $medicines     = Medicine::where('status', true)->select('id', 'medicine_name')->get();
        $doctors       = Doctor::select('id', 'name')->orderBy('name')->get();

        return view('ipd_patients.medicine-orders.edit', compact('ipdPatient', 'medicineOrder', 'medicines', 'doctors'));
    }

    public function update(Request $request, $ipdPatientId, $orderId)
    {
        $ipdPatient    = IpdPatient::findOrFail($ipdPatientId);
        $medicineOrder = MedicineOrder::where('ipd_id', $ipdPatientId)->findOrFail($orderId);

        $validated = $request->validate([
            'medicine_id'   => 'required|exists:medicines,id',
            'qty'           => 'required|integer|min:1',
            'prescribed_by' => 'nullable|exists:doctors,id',
            'status'        => 'nullable|string|max:255',
            'order_by'      => 'nullable|string|max:255',
        ]);

        $medicineOrder->update([
            'medicine_id'   => $validated['medicine_id'],
            'qty'           => $validated['qty'],
            'prescribed_by' => $validated['prescribed_by'] ?? $ipdPatient->doctor_id,
            'status'        => $validated['status'] ?? 'pending',
            'order_by'      => $validated['order_by'] ?? null,
        ]);

        // return redirect()
        //     ->route('ipd-patients.ipd-patients.show', $ipdPatient->id)
        //     ->with('success', 'Medicine order updated successfully.');

        return redirect(route('ipd-patients.ipd-patients.show', $ipdPatient->id) . '?tab=medicine-order')
            ->with('success', 'Medicine order updated successfully.');
    }

    public function destroy($ipdPatientId, $orderId)
    {
        $order = MedicineOrder::where('ipd_id', $ipdPatientId)->findOrFail($orderId);
        $order->delete();

        // return redirect()
        //     ->route('ipd-patients.ipd-patients.show', $ipdPatientId)
        //     ->with('success', 'Medicine order deleted successfully.');

        return redirect(route('ipd-patients.ipd-patients.show', $ipdPatientId) . '?tab=medicine-order')
            ->with('success', 'Medicine order deleted successfully.');
    }
}
