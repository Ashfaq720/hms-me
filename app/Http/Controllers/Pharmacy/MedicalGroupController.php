<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy\MedicalGroup;
use Illuminate\Http\Request;

class MedicalGroupController extends Controller
{
    public function index()
    {
        $medical_groups = MedicalGroup::orderBy('id', 'desc')->get();

        return view('pharmacy.medical-group.index', compact('medical_groups'));
    }

    public function create()
    {
        return view('pharmacy.medical-group.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'   => 'required|string|max:255|unique:medical_groups,name',
            'status' => 'nullable',
        ]);

        MedicalGroup::create([
            'name'   => $request->name,
            'status' => $request->has('status') ? 1 : 0,
        ]);

        return redirect()->route('admin.medical-groups.index')
            ->with('success', 'Medical Group created successfully.');
    }

    public function edit($id)
    {
        $medicalGroup = MedicalGroup::findOrFail($id);

        return view('pharmacy.medical-group.edit', compact('medicalGroup'));
    }

    public function update(Request $request, $id)
    {
        $medicalGroup = MedicalGroup::findOrFail($id);

        $request->validate([
            'name'   => 'required|string|max:255|unique:medical_groups,name,' . $medicalGroup->id,
            'status' => 'nullable',
        ]);

        $medicalGroup->update([
            'name'   => $request->name,
            'status' => $request->has('status') ? 1 : 0,
        ]);

        return redirect()->route('admin.medical-groups.index')
            ->with('success', 'Medical Group updated successfully.');
    }

    public function destroy($id)
    {
        $medicalGroup = MedicalGroup::findOrFail($id);
        $medicalGroup->delete();

        return redirect()->route('admin.medical-groups.index')
            ->with('success', 'Medical Group deleted successfully.');
    }
}
