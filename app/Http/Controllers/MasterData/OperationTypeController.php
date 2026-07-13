<?php
namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\MasterData\OperationType;
use Illuminate\Http\Request;

class OperationTypeController extends Controller
{
    public function index()
    {
        $operationTypes = OperationType::all();
        return view('master-data.operation-type.index', compact('operationTypes'));
    }

    public function create() {}

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        OperationType::create([
            'name' => $request->name,
        ]);

        return redirect()->route('operation-types.index')
            ->with('success', 'Operation Type created successfully.');
    }

    public function show(OperationType $operationType) {}

    public function edit(OperationType $operationType) {}

    public function update(Request $request, OperationType $operationType)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $operationType->update([
            'name' => $request->name,
        ]);

        return redirect()->route('operation-types.index')
            ->with('success', 'Operation Type updated successfully.');
    }

    public function destroy(OperationType $operationType)
    {
        $operationType->delete();
        return redirect()->route('operation-types.index')
            ->with('success', 'Operation Type deleted successfully.');
    }
}
