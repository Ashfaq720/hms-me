<?php
namespace App\Http\Controllers\ICU;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Icu\IcuAdmission;
use App\Models\Ipd\MedicineOrder;
use App\Models\Pharmacy\Medicine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IcuMedicineOrderController extends Controller
{
    protected string $source = 'icu';

    public function index($admissionId)
    {
        $admission = IcuAdmission::with('patient')->findOrFail($admissionId);

        $orders = MedicineOrder::with(['medicine.unit', 'medicine.category', 'medicine.medicalGroup', 'prescribedBy'])
            ->where('case_id', $admission->case_id)
            ->where('source', $this->source)
            ->orderByDesc('id')
            ->get();

        return view('icu.medicine-orders.index', compact('admission', 'orders'));
    }

    public function create($admissionId)
    {
        $admission = IcuAdmission::with('patient')->findOrFail($admissionId);
        $medicines = Medicine::where('status', true)
            ->select('id', 'medicine_unit_id', 'medicine_category_id', 'medical_group_id', 'medicine_name')
            ->with(['unit', 'category', 'medicalGroup'])
            ->get();
        $doctors = Doctor::select('id', 'name')->orderBy('name')->get();

        return view('icu.medicine-orders.create', compact('admission', 'medicines', 'doctors'));
    }

    public function store(Request $request, $admissionId)
    {
        $admission = IcuAdmission::findOrFail($admissionId);

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
                    'prescribed_by' => $order['prescribed_by'] ?? $admission->referring_doctor_id,
                    'patient_id'    => $admission->patient_id,
                    'ipd_id'        => $admission->ipdIdForCharge(),
                    'case_id'       => $admission->case_id,
                    'source'        => $this->source,
                    'status'        => $order['status'] ?? 'pending',
                    'order_by'      => $order['order_by'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()->route('icu.admissions.medicine-orders.index', $admission->id)
                ->with('success', 'Medicine orders created successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to save medicine orders: ' . $e->getMessage());
        }
    }

    public function edit($admissionId, $orderId)
    {
        $admission     = IcuAdmission::with('patient')->findOrFail($admissionId);
        $medicineOrder = MedicineOrder::where('case_id', $admission->case_id)
            ->where('source', $this->source)
            ->findOrFail($orderId);
        $medicines = Medicine::where('status', true)->select('id', 'medicine_name')->get();
        $doctors   = Doctor::select('id', 'name')->orderBy('name')->get();

        return view('icu.medicine-orders.edit', compact('admission', 'medicineOrder', 'medicines', 'doctors'));
    }

    public function update(Request $request, $admissionId, $orderId)
    {
        $admission     = IcuAdmission::findOrFail($admissionId);
        $medicineOrder = MedicineOrder::where('case_id', $admission->case_id)
            ->where('source', $this->source)
            ->findOrFail($orderId);

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
            'prescribed_by' => $validated['prescribed_by'] ?? $admission->referring_doctor_id,
            'status'        => $validated['status'] ?? 'pending',
            'order_by'      => $validated['order_by'] ?? null,
        ]);

        return redirect()->route('icu.admissions.medicine-orders.index', $admission->id)
            ->with('success', 'Medicine order updated successfully.');
    }

    public function destroy($admissionId, $orderId)
    {
        $admission = IcuAdmission::findOrFail($admissionId);
        $order     = MedicineOrder::where('case_id', $admission->case_id)
            ->where('source', $this->source)
            ->findOrFail($orderId);
        $order->delete();

        return redirect()->route('icu.admissions.medicine-orders.index', $admission->id)
            ->with('success', 'Medicine order deleted successfully.');
    }
}
