<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::latest()->get();
        return view('pharmacy.supplier.index', compact('suppliers'));
    }

    public function create()
    {
        return view('pharmacy.supplier.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'supplier_name' => 'required|string|max:255',
            'contact_supplier' => 'nullable|string|max:50',
            'contact_person_name' => 'nullable|string|max:255',
            'contact_person_telephone' => 'nullable|string|max:50',
            'drug_license_number' => 'nullable|string|max:255',
            'address' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.suppliers.index')
                ->withErrors($validator)
                ->withInput()
                ->with('modal_type', 'create');
        }

        Supplier::create([
            'supplier_name' => $request->supplier_name,
            'contact_supplier' => $request->contact_supplier,
            'contact_person_name' => $request->contact_person_name,
            'contact_person_telephone' => $request->contact_person_telephone,
            'drug_license_number' => $request->drug_license_number,
            'address' => $request->address,
            'status' => $request->has('status') ? 1 : 0,
        ]);

        return redirect()->route('admin.suppliers.index')
            ->with('success', 'Supplier created successfully.');
    }

    public function edit($id)
    {
        $supplier = Supplier::findOrFail($id);
        return view('pharmacy.supplier.edit', compact('supplier'));
    }

    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'supplier_name' => 'required|string|max:255',
            'contact_supplier' => 'nullable|string|max:50',
            'contact_person_name' => 'nullable|string|max:255',
            'contact_person_telephone' => 'nullable|string|max:50',
            'drug_license_number' => 'nullable|string|max:255',
            'address' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->route('pharmacy.suppliers.index')
                ->withErrors($validator)
                ->withInput()
                ->with('modal_type', 'edit')
                ->with('edit_id', $supplier->id);
        }

        $supplier->update([
            'supplier_name' => $request->supplier_name,
            'contact_supplier' => $request->contact_supplier,
            'contact_person_name' => $request->contact_person_name,
            'contact_person_telephone' => $request->contact_person_telephone,
            'drug_license_number' => $request->drug_license_number,
            'address' => $request->address,
            'status' => $request->has('status') ? 1 : 0,
        ]);

        return redirect()->route('admin.suppliers.index')
            ->with('success', 'Supplier updated successfully.');
    }

    public function destroy($id)
    {
        Supplier::findOrFail($id)->delete();

        return redirect()->route('pharmacy.suppliers.index')
            ->with('success', 'Supplier deleted successfully.');
    }
}
