<?php
namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\MasterData\OperationTheatre;
use Illuminate\Http\Request;

class OperationTheatreController extends Controller
{
    public function index()
    {
        $operationTheatres = OperationTheatre::all();
        return view('master-data.operation-theatre.index', compact('operationTheatres'));
    }

    public function create() {}

    public function store(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'charge'  => 'required|numeric',
            'remarks' => 'nullable|string|max:255',
        ]);

        OperationTheatre::create([
            'name'    => $request->name,
            'charge'  => $request->charge,
            'remarks' => $request->remarks,
        ]);

        return redirect()->route('operation-theatres.index')
            ->with('success', 'Operation Theatre created successfully.');
    }

    public function show(OperationTheatre $operationTheatre) {}

    public function edit(OperationTheatre $operationTheatre) {}

    public function update(Request $request, OperationTheatre $operationTheatre)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'charge'  => 'required|numeric',
            'remarks' => 'nullable|string|max:255',
        ]);

        $operationTheatre->update([
            'name'    => $request->name,
            'charge'  => $request->charge,
            'remarks' => $request->remarks,
        ]);

        return redirect()->route('operation-theatres.index')
            ->with('success', 'Operation Theatre updated successfully.');
    }

    public function destroy(OperationTheatre $operationTheatre)
    {
        $operationTheatre->delete();
        return redirect()->route('operation-theatres.index')
            ->with('success', 'Operation Theatre deleted successfully.');
    }
}
