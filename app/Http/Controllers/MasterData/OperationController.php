<?php
namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\MasterData\Operation;
use Illuminate\Http\Request;

class OperationController extends Controller
{
    public function index()
    {
        $operations = Operation::all();
        return view('master-data.operation.index', compact('operations'));
    }

    public function create() {}

    public function store(Request $request)
    {
        $request->validate([
            'name'   => 'required|string|max:255',
            'charge' => 'required|numeric',
        ]);

        Operation::create([
            'name'   => $request->name,
            'charge' => $request->charge,
        ]);

        return redirect()->route('operations.index')
            ->with('success', 'Operation created successfully.');
    }

    public function show(Operation $operation) {}

    public function edit(Operation $operation) {}

    public function update(Request $request, Operation $operation)
    {
        $request->validate([
            'name'   => 'required|string|max:255',
            'charge' => 'required|numeric',
        ]);

        $operation->update([
            'name'   => $request->name,
            'charge' => $request->charge,
        ]);

        return redirect()->route('operations.index')
            ->with('success', 'Operation updated successfully.');
    }

    public function destroy(Operation $operation)
    {
        $operation->delete();
        return redirect()->route('operations.index')
            ->with('success', 'Operation deleted successfully.');
    }
}
