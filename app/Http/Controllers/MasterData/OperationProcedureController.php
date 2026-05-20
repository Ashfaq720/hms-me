<?php
namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\MasterData\Operation;
use App\Models\MasterData\OperationProcedure;
use Illuminate\Http\Request;

class OperationProcedureController extends Controller
{
    public function index()
    {
        $operationProcedures = OperationProcedure::with('operation')->get();
        $operations = Operation::all();
        return view('master-data.operation-procedure.index', compact('operationProcedures', 'operations'));
    }

    public function create() {}

    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'operation_id' => 'required|exists:operations,id',
        ]);

        OperationProcedure::create([
            'name'         => $request->name,
            'operation_id' => $request->operation_id,
        ]);

        return redirect()->route('operation-procedures.index')
            ->with('success', 'Operation Procedure created successfully.');
    }

    public function show(OperationProcedure $operationProcedure) {}

    public function edit(OperationProcedure $operationProcedure) {}

    public function update(Request $request, OperationProcedure $operationProcedure)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'operation_id' => 'required|exists:operations,id',
        ]);

        $operationProcedure->update([
            'name'         => $request->name,
            'operation_id' => $request->operation_id,
        ]);

        return redirect()->route('operation-procedures.index')
            ->with('success', 'Operation Procedure updated successfully.');
    }

    public function destroy(OperationProcedure $operationProcedure)
    {
        $operationProcedure->delete();
        return redirect()->route('operation-procedures.index')
            ->with('success', 'Operation Procedure deleted successfully.');
    }
}
