<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy\MedicineGeneric;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MedicineGenericController extends Controller
{
    public function index()
    {
        $medicine_generics = MedicineGeneric::latest()->get();

        return view('pharmacy.medicine-generic.index', compact('medicine_generics'));
    }

    public function create()
    {
        return view('pharmacy.medicine-generic.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:medicine_generics,name',
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.medicine-generics.index')
                ->withErrors($validator)
                ->withInput()
                ->with('modal_type', 'create');
        }

        MedicineGeneric::create([
            'name'   => $request->name,
            'status' => $request->has('status') ? 1 : 0,
        ]);

        return redirect()->route('admin.medicine-generics.index')
            ->with('success', 'Medicine generic created successfully.');
    }

    public function edit($id)
    {
        $medicineGeneric = MedicineGeneric::findOrFail($id);

        return view('pharmacy.medicine-generic.edit', compact('medicineGeneric'));
    }

    public function update(Request $request, $id)
    {
        $medicineGeneric = MedicineGeneric::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:medicine_generics,name,' . $medicineGeneric->id,
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.medicine-generics.index')
                ->withErrors($validator)
                ->withInput()
                ->with('modal_type', 'edit')
                ->with('edit_id', $medicineGeneric->id);
        }

        $medicineGeneric->update([
            'name'   => $request->name,
            'status' => $request->has('status') ? 1 : 0,
        ]);

        return redirect()->route('admin.medicine-generics.index')
            ->with('success', 'Medicine generic updated successfully.');
    }

    public function destroy($id)
    {
        $medicineGeneric = MedicineGeneric::findOrFail($id);
        $medicineGeneric->delete();

        return redirect()->route('admin.medicine-generics.index')
            ->with('success', 'Medicine generic deleted successfully.');
    }
}
