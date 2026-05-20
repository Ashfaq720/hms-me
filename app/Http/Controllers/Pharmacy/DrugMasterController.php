<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy\MedicalGroup;
use App\Models\Pharmacy\Medicine;
use App\Models\Pharmacy\MedicineCategory;
use App\Models\Pharmacy\MedicineGeneric;
use App\Models\Pharmacy\MedicineUnit;
use Illuminate\Http\Request;

class DrugMasterController extends Controller
{
    public function index(Request $request)
    {
        $query = Medicine::with(['category', 'company', 'medicalGroup', 'unit']);

        if ($request->filled('medicine_name')) {
            $query->where('medicine_name', 'like', '%' . $request->medicine_name . '%');
        }

        if ($request->filled('generic_name')) {
            $query->where('medicine_composition', 'like', '%' . $request->generic_name . '%');
        }

        if ($request->filled('category_id')) {
            $query->where('medicine_category_id', $request->category_id);
        }

        if ($request->filled('group_id')) {
            $query->where('medical_group_id', $request->group_id);
        }

        if ($request->filled('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        $medicines = $query->latest()->get();

        $categories = MedicineCategory::orderBy('name')->get();
        $groups = MedicalGroup::orderBy('name')->get();
        $generics = MedicineGeneric::orderBy('name')->get();
        $units = MedicineUnit::orderBy('name')->get();

        $activeCount = Medicine::where('status', 1)->count();
        $inactiveCount = Medicine::where('status', 0)->count();

        return view('pharmacy.drug-master.index', compact(
            'medicines',
            'categories',
            'groups',
            'generics',
            'units',
            'activeCount',
            'inactiveCount'
        ));
    }
}
