<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy\MedicineUnit;
use Illuminate\Http\Request;

class MedicineUnitController extends Controller
{
    public function index()
    {
        $unite_types = MedicineUnit::orderBy('id', 'desc')->get();

        return view('pharmacy.medicine-unit.index', compact('unite_types'));
    }

    public function create()
    {
        return view('pharmacy.medicine-unit.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'   => 'required|string|max:255|unique:medicine_units,name',
            'status' => 'nullable',
        ]);

        MedicineUnit::create([
            'name'   => $request->name,
            'status' => $request->has('status') ? 1 : 0,
        ]);

        return redirect()->route('admin.medicine-units.index')
            ->with('success', 'Unite Type created successfully.');
    }

    public function edit($id)
    {
        $uniteType = MedicineUnit::findOrFail($id);

        return view('pharmacy.medicine-unit.edit', compact('uniteType'));
    }

    public function update(Request $request, $id)
    {
        $uniteType = MedicineUnit::findOrFail($id);

        $request->validate([
            'name'   => 'required|string|max:255|unique:medicine_units,name,' . $uniteType->id,
            'status' => 'nullable',
        ]);

        $uniteType->update([
            'name'   => $request->name,
            'status' => $request->has('status') ? 1 : 0,
        ]);

        return redirect()->route('admin.medicine-units.index')
            ->with('success', 'Unite Type updated successfully.');
    }

    public function destroy($id)
    {
        $uniteType = MedicineUnit::findOrFail($id);
        $uniteType->delete();

        return redirect()->route('admin.medicine-units.index')
            ->with('success', 'Unite Type deleted successfully.');
    }
}
