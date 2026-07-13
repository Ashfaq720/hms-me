<?php
namespace App\Http\Controllers\IPD;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\IpdPatient;
use App\Models\Ipd\OperationHistory;
use App\Models\MasterData\Operation;
use App\Models\MasterData\OperationProcedure;
use App\Models\MasterData\OperationTheatre;
use App\Models\MasterData\OperationType;
use App\Models\PatientCharge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CaseOperationController extends Controller
{
    public function create($id)
    {
        $ipdPatient     = IpdPatient::with('patient')->findOrFail($id);
        $doctors        = Doctor::select('id', 'name')->orderBy('name')->get();
        $operationTypes = OperationType::select('id', 'name')->orderBy('name')->get();
        $operations     = Operation::select('id', 'name')->orderBy('name')->get();
        $procedures     = OperationProcedure::select('id', 'operation_id', 'name')->orderBy('name')->get();
        $theatres       = OperationTheatre::select('id', 'name')->orderBy('name')->get();

        return view('ipd_patients.case-operation.create', compact(
            'id',
            'ipdPatient',
            'doctors',
            'operationTypes',
            'operations',
            'procedures',
            'theatres'
        ));
    }

    public function store(Request $request, $id)
    {
        $validated = $request->validate([
            'operation_type_id'      => 'nullable|exists:operation_types,id',
            'operation_id'           => 'nullable|exists:operations,id',
            'operation_procedure_id' => 'nullable|exists:operation_procedures,id',
            'operation_theatre_id'   => 'nullable|exists:operation_theatres,id',
            'date'                   => 'required|date',
            'start_datetime'         => 'nullable|date',
            'end_datetime'           => 'nullable|date|after_or_equal:start_datetime',
            'pre_op'                 => 'nullable|boolean',
            'vitals'                 => 'nullable|boolean',
            'consent'                => 'nullable|boolean',
            'equipment'              => 'nullable|boolean',
            'diagnosis'              => 'nullable|string',
            'assign_doctor_id'       => 'nullable|exists:doctors,id',
            'assistant_doctor_id'    => 'nullable|exists:doctors,id',
            'main_surgeon_id'        => 'nullable|exists:doctors,id',
            'anesthesiologist_id'    => 'nullable|exists:doctors,id',
            'ot_technician'          => 'nullable|string|max:255',
            'remarks'                => 'nullable|string',
            'status'                 => 'required|in:Scheduled,In Progress,Completed,Cancelled',
        ]);

        DB::beginTransaction();
        try {
            $ipdPatient = IpdPatient::findOrFail($id);

            $validated['ipd_id']        = $ipdPatient->id;
            $validated['case_id']       = $ipdPatient->case_id;
            $validated['customer_type'] = 'Ipd';
            $validated['pre_op']        = $request->boolean('pre_op');
            $validated['vitals']        = $request->boolean('vitals');
            $validated['consent']       = $request->boolean('consent');
            $validated['equipment']     = $request->boolean('equipment');

            OperationHistory::create($validated);

            // Add Operation Charge to patient charges
            if ($validated['operation_id']) {
                $operation = Operation::find($validated['operation_id']);
                if ($operation && $operation->charge > 0) {
                    PatientCharge::create([
                        'case_id'       => $ipdPatient->case_id,
                        'charge_module' => 'ipd',
                        'ipd_id'        => $ipdPatient->id,
                        'doctor_id'     => $ipdPatient->doctor_id,
                        'department_id' => $ipdPatient->department_id,
                        'charge_item'   => 'Operation Charge - ' . $operation->name,
                        'unit_price'    => $operation->charge,
                        'quantity'      => 1,
                        'amount'        => $operation->charge,
                        'vat'           => 0,
                        'tax'           => 0,
                        'net_amount'    => $operation->charge,
                        'date'          => $validated['date'],
                        'created_by'    => auth()->id(),
                    ]);
                }
            }

            // Add Operation Theatre Charge to patient charges
            if ($validated['operation_theatre_id']) {
                $theatre = OperationTheatre::find($validated['operation_theatre_id']);
                if ($theatre && $theatre->charge > 0) {
                    PatientCharge::create([
                        'case_id'       => $ipdPatient->case_id,
                        'charge_module' => 'ipd',
                        'ipd_id'        => $ipdPatient->id,
                        'doctor_id'     => $ipdPatient->doctor_id,
                        'department_id' => $ipdPatient->department_id,
                        'charge_item'   => 'Operation Theatre - ' . $theatre->name,
                        'unit_price'    => $theatre->charge,
                        'quantity'      => 1,
                        'amount'        => $theatre->charge,
                        'vat'           => 0,
                        'tax'           => 0,
                        'net_amount'    => $theatre->charge,
                        'date'          => $validated['date'],
                        'created_by'    => auth()->id(),
                    ]);
                }
            }

            DB::commit();

            // return redirect()->route('ipd-patients.ipd-patients.show', $id)
            //     ->with('success', 'Operation record saved successfully.');

            return redirect(route('ipd-patients.ipd-patients.show', $id) . '?tab=operation')
                ->with('success', 'Operation record saved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()
                ->with('error', 'Failed to save operation record: ' . $e->getMessage());
        }
    }

    public function show($id, $operationId)
    {
        $ipdPatient = IpdPatient::with('patient')->findOrFail($id);
        $operation  = OperationHistory::with([
            'operationType',
            'operation',
            'operationProcedure',
            'operationTheatre',
            'assignDoctor',
            'assistantDoctor',
            'mainSurgeon',
            'anesthesiologist',
        ])->where('ipd_id', $id)->findOrFail($operationId);

        return view('ipd_patients.case-operation.show', compact('ipdPatient', 'operation'));
    }

    public function edit($id, $operationId)
    {
        $ipdPatient     = IpdPatient::with('patient')->findOrFail($id);
        $operation      = OperationHistory::where('ipd_id', $id)->findOrFail($operationId);
        $doctors        = Doctor::select('id', 'name')->orderBy('name')->get();
        $operationTypes = OperationType::select('id', 'name')->orderBy('name')->get();
        $operations     = Operation::select('id', 'name')->orderBy('name')->get();
        $procedures     = OperationProcedure::select('id', 'operation_id', 'name')->orderBy('name')->get();
        $theatres       = OperationTheatre::select('id', 'name')->orderBy('name')->get();

        return view('ipd_patients.case-operation.edit', compact(
            'id',
            'ipdPatient',
            'operation',
            'doctors',
            'operationTypes',
            'operations',
            'procedures',
            'theatres'
        ));
    }

    public function update(Request $request, $id, $operationId)
    {
        $validated = $request->validate([
            'operation_type_id'      => 'nullable|exists:operation_types,id',
            'operation_id'           => 'nullable|exists:operations,id',
            'operation_procedure_id' => 'nullable|exists:operation_procedures,id',
            'operation_theatre_id'   => 'nullable|exists:operation_theatres,id',
            'date'                   => 'required|date',
            'start_datetime'         => 'nullable|date',
            'end_datetime'           => 'nullable|date|after_or_equal:start_datetime',
            'pre_op'                 => 'nullable|boolean',
            'vitals'                 => 'nullable|boolean',
            'consent'                => 'nullable|boolean',
            'equipment'              => 'nullable|boolean',
            'diagnosis'              => 'nullable|string',
            'assign_doctor_id'       => 'nullable|exists:doctors,id',
            'assistant_doctor_id'    => 'nullable|exists:doctors,id',
            'main_surgeon_id'        => 'nullable|exists:doctors,id',
            'anesthesiologist_id'    => 'nullable|exists:doctors,id',
            'ot_technician'          => 'nullable|string|max:255',
            'remarks'                => 'nullable|string',
            'status'                 => 'required|in:Scheduled,In Progress,Completed,Cancelled',
        ]);

        try {
            $operation = OperationHistory::where('ipd_id', $id)->findOrFail($operationId);

            $validated['pre_op']    = $request->boolean('pre_op');
            $validated['vitals']    = $request->boolean('vitals');
            $validated['consent']   = $request->boolean('consent');
            $validated['equipment'] = $request->boolean('equipment');

            $operation->update($validated);

            // return redirect()->route('ipd-patients.ipd-patients.show', $id)
            //     ->with('success', 'Operation record updated successfully.');

            return redirect(route('ipd-patients.ipd-patients.show', $id) . '?tab=operation')
                ->with('success', 'Operation record updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'Failed to update operation record: ' . $e->getMessage());
        }
    }

    public function destroy($id, $operationId)
    {
        try {
            $operation = OperationHistory::where('ipd_id', $id)->findOrFail($operationId);
            $operation->delete();

            // return redirect()->route('ipd-patients.ipd-patients.show', $id)
            //     ->with('success', 'Operation record deleted successfully.');

            return redirect(route('ipd-patients.ipd-patients.show', $id) . '?tab=operation')
                ->with('success', 'Operation record deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete operation record: ' . $e->getMessage());
        }
    }
}
