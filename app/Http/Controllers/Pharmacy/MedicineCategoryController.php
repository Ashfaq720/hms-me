<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy\MedicineCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MedicineCategoryController extends Controller
{
    public function index()
    {
        $medicine_categories = MedicineCategory::latest()->get();
        return view('pharmacy.medicine-category.index', compact('medicine_categories'));
    }

    public function create()
    {
        return view('pharmacy.medicine-category.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:medicine_categories,name',
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.medicine-categories.index')
                ->withErrors($validator)
                ->withInput()
                ->with('modal_type', 'create');
        }

        MedicineCategory::create([
            'name' => $request->name,
            'status' => $request->has('status') ? 1 : 0,
        ]);

        return redirect()->route('admin.medicine-categories.index')
            ->with('success', 'Medicine category created successfully.');
    }

    public function edit($id)
    {
        $medicineCategory = MedicineCategory::findOrFail($id);
        return view('pharmacy.medicine-category.edit', compact('medicineCategory'));
    }

    public function update(Request $request, $id)
    {
        $medicineCategory = MedicineCategory::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:medicine_categories,name,' . $medicineCategory->id,
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.medicine-categories.index')
                ->withErrors($validator)
                ->withInput()
                ->with('modal_type', 'edit')
                ->with('edit_id', $medicineCategory->id);
        }

        $medicineCategory->update([
            'name' => $request->name,
            'status' => $request->has('status') ? 1 : 0,
        ]);

        return redirect()->route('admin.medicine-categories.index')
            ->with('success', 'Medicine category updated successfully.');
    }

    public function destroy($id)
    {
        MedicineCategory::findOrFail($id)->delete();

        return redirect()->route('admin.medicine-categories.index')
            ->with('success', 'Medicine category deleted successfully.');
    }
}
