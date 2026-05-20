<?php

namespace App\Http\Controllers\OPD;

use App\Http\Controllers\Controller;
use App\Models\Charges\Charge;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\OpdPatient;
use App\Models\PatientCharge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OpdChargeController extends Controller
{
    public function create($opdPatientId)
    {
        $opdPatient  = OpdPatient::with('patient')->findOrFail($opdPatientId);
        $doctors     = Doctor::select('id', 'name')->get();
        $departments = Department::select('id', 'name')->get();
        $charges     = Charge::with(['chargeType', 'chargeCategory'])->get();

        return view('opd_patients.charges.create', compact('opdPatient', 'doctors', 'departments', 'charges'));
    }

    public function store(Request $request, $opdPatientId)
    {
        $opdPatient = OpdPatient::findOrFail($opdPatientId);

        $validated = $request->validate([
            'items'              => 'required|array|min:1',
            'items.*.charge_id'  => 'required|exists:charges,id',
            'items.*.quantity'   => 'required|integer|min:1',
            'items.*.date'       => 'required|date',
            'items.*.notes'      => 'nullable|string',
            'doctor_id'          => 'nullable|exists:doctors,id',
            'department_id'      => 'nullable|exists:departments,id',
        ]);

        $doctorId     = $validated['doctor_id'] ?? $opdPatient->doctor_id;
        $departmentId = $validated['department_id'] ?? $opdPatient->department_id;

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
                    'case_id'       => $opdPatient->case_id,
                    'charge_module' => 'opd',
                    'opd_id'        => $opdPatient->id,
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

            return redirect(route('opd-patients.show', $opdPatient->id) . '?tab=charges')
                ->with('success', $message);
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to save charges: ' . $e->getMessage());
        }
    }

    public function edit($opdPatientId, $chargeId)
    {
        $opdPatient    = OpdPatient::with('patient')->findOrFail($opdPatientId);
        $patientCharge = PatientCharge::where('opd_id', $opdPatientId)->findOrFail($chargeId);
        $doctors       = Doctor::select('id', 'name')->get();
        $departments   = Department::select('id', 'name')->get();
        $charges       = Charge::with(['chargeType', 'chargeCategory'])->get();

        return view('opd_patients.charges.edit', compact('opdPatient', 'patientCharge', 'doctors', 'departments', 'charges'));
    }

    public function update(Request $request, $opdPatientId, $chargeId)
    {
        $opdPatient    = OpdPatient::findOrFail($opdPatientId);
        $patientCharge = PatientCharge::where('opd_id', $opdPatientId)->findOrFail($chargeId);

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
            'doctor_id'     => $validated['doctor_id'] ?? $opdPatient->doctor_id,
            'department_id' => $validated['department_id'] ?? $opdPatient->department_id,
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

        if ($request->input('source') === 'billing') {
            return back()->with('success', 'Charge updated successfully.');
        }

        return redirect(route('opd-patients.show', $opdPatient->id) . '?tab=charges')
            ->with('success', 'Charge updated successfully.');
    }

    public function destroy(Request $request, $opdPatientId, $chargeId)
    {
        $patientCharge = PatientCharge::where('opd_id', $opdPatientId)->findOrFail($chargeId);
        $patientCharge->delete();

        if ($request->input('source') === 'billing') {
            return back()->with('success', 'Charge deleted successfully.');
        }

        return redirect(route('opd-patients.show', $opdPatientId) . '?tab=charges')
            ->with('success', 'Charge deleted successfully.');
    }
}
