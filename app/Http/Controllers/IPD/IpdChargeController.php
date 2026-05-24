<?php
namespace App\Http\Controllers\IPD;

use App\Http\Controllers\Controller;
use App\Models\Charges\Charge;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\IpdPatient;
use App\Models\PatientCharge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IpdChargeController extends Controller
{
    public function create($ipdPatientId)
    {
        $ipdPatient  = IpdPatient::with('patient')->findOrFail($ipdPatientId);
        $doctors     = Doctor::select('id', 'name')->get();
        $departments = Department::select('id', 'name')->get();
        $charges     = Charge::with(['chargeType', 'chargeCategory'])->get();

        return view('ipd_patients.charges.create', compact('ipdPatient', 'doctors', 'departments', 'charges'));
    }

    public function store(Request $request, $ipdPatientId)
    {
        $ipdPatient = IpdPatient::findOrFail($ipdPatientId);

        $validated = $request->validate([
            'items'             => 'required|array|min:1',
            'items.*.charge_id' => 'required|exists:charges,id',
            'items.*.quantity'  => 'required|integer|min:1',
            'items.*.date'      => 'required|date',
            'items.*.notes'     => 'nullable|string',
            'doctor_id'         => 'nullable|exists:doctors,id',
            'department_id'     => 'nullable|exists:departments,id',
        ]);

        $doctorId     = $validated['doctor_id'] ?? $ipdPatient->doctor_id;
        $departmentId = $validated['department_id'] ?? $ipdPatient->department_id;

        DB::beginTransaction();
        try {
            foreach ($validated['items'] as $item) {
                $charge    = Charge::findOrFail($item['charge_id']);
                $unitPrice = $charge->standard_charge;
                $quantity  = $item['quantity'];
                $amount    = $unitPrice * $quantity;
                $taxRate   = $charge->tax ?? 0;
                $tax       = round($amount * $taxRate / 100, 2);
                $netAmount = $amount + $tax;

                PatientCharge::create([
                    'case_id'       => $ipdPatient->case_id,
                    'charge_module' => 'ipd',
                    'ipd_id'        => $ipdPatient->id,
                    'doctor_id'     => $doctorId,
                    'department_id' => $departmentId,
                    'charge_item'   => $charge->charge_name,
                    'charge_id'     => $charge->id,
                    'unit_price'    => $unitPrice,
                    'quantity'      => $quantity,
                    'amount'        => $amount,
                    'vat'           => 0,
                    'tax'           => $tax,
                    'net_amount'    => $netAmount,
                    'date'          => $item['date'],
                    'notes'         => $item['notes'] ?? null,
                    'created_by'    => auth()->id(),
                ]);
            }

            DB::commit();

            $message = count($validated['items']) . ' charge(s) added successfully.';

            if ($request->input('source') === 'billing') {
                return back()->with('success', $message);
            }

            // return redirect()
            //     ->route('ipd-patients.ipd-patients.show', $ipdPatient->id)
            //     ->with('success', $message);

            return redirect(route('ipd-patients.ipd-patients.show', $ipdPatient->id) . '?tab=charges')
                ->with('success', $message);
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to save charges: ' . $e->getMessage());
        }
    }

    public function edit($ipdPatientId, $chargeId)
    {
        $ipdPatient    = IpdPatient::with('patient')->findOrFail($ipdPatientId);
        $patientCharge = PatientCharge::where('ipd_id', $ipdPatientId)->findOrFail($chargeId);
        $doctors       = Doctor::select('id', 'name')->get();
        $departments   = Department::select('id', 'name')->get();
        $charges       = Charge::with(['chargeType', 'chargeCategory'])->get();

        return view('ipd_patients.charges.edit', compact('ipdPatient', 'patientCharge', 'doctors', 'departments', 'charges'));
    }

    public function update(Request $request, $ipdPatientId, $chargeId)
    {
        $ipdPatient    = IpdPatient::findOrFail($ipdPatientId);
        $patientCharge = PatientCharge::where('ipd_id', $ipdPatientId)->findOrFail($chargeId);

        $validated = $request->validate([
            'charge_id'     => 'required|exists:charges,id',
            'doctor_id'     => 'nullable|exists:doctors,id',
            'department_id' => 'nullable|exists:departments,id',
            'quantity'      => 'required|integer|min:1',
            'date'          => 'required|date',
            'notes'         => 'nullable|string',
        ]);

        $charge    = Charge::findOrFail($validated['charge_id']);
        $unitPrice = $charge->standard_charge;
        $quantity  = $validated['quantity'];
        $amount    = $unitPrice * $quantity;
        $taxRate   = $charge->tax ?? 0;
        $tax       = round($amount * $taxRate / 100, 2);
        $netAmount = $amount + $tax;

        $patientCharge->update([
            'doctor_id'     => $validated['doctor_id'] ?? $ipdPatient->doctor_id,
            'department_id' => $validated['department_id'] ?? $ipdPatient->department_id,
            'charge_item'   => $charge->charge_name,
            'charge_id'     => $charge->id,
            'unit_price'    => $unitPrice,
            'quantity'      => $quantity,
            'amount'        => $amount,
            'vat'           => 0,
            'tax'           => $tax,
            'net_amount'    => $netAmount,
            'date'          => $validated['date'],
            'notes'         => $validated['notes'] ?? null,
        ]);

        // $message = count($validated['items']) . ' charge(s) added successfully.';

        if ($request->input('source') === 'billing') {
            return back()->with('success', 'Charge updated successfully');
        }

        // return redirect()
        //     ->route('ipd-patients.ipd-patients.show', $ipdPatient->id)
        //     ->with('success', 'Charge updated successfully.');

        return redirect(route('ipd-patients.ipd-patients.show', $ipdPatient->id) . '?tab=charges')
            ->with('success', 'Charge updated successfully.');
    }

    public function destroy(Request $request, $ipdPatientId, $chargeId)
    {
        $patientCharge = PatientCharge::where('ipd_id', $ipdPatientId)->findOrFail($chargeId);
        $patientCharge->delete();

        if ($request->input('source') === 'billing') {
            return back()->with('success', 'Charge deleted successfully');
        }

        // return redirect()
        //     ->route('ipd-patients.ipd-patients.show', $ipdPatientId)
        //     ->with('success', 'Charge deleted successfully.');

        return redirect(route('ipd-patients.ipd-patients.show', $ipdPatientId) . '?tab=charges')
            ->with('success', 'Charge deleted successfully.');
    }
}
