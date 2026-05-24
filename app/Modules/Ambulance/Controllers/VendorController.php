<?php

namespace App\Modules\Ambulance\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Ambulance\Models\Vendor;
use App\Modules\Ambulance\Models\VendorContract;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index()
    {
        $vendors = Vendor::withCount('ambulances')->orderBy('vendor_name')->paginate(20);
        return view('backend.ambulance.vendors.index', compact('vendors'));
    }

    public function create()
    {
        return view('backend.ambulance.vendors.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'vendor_code'          => 'required|unique:amb_vendors,vendor_code|max:50',
            'vendor_name'          => 'required|string|max:200',
            'contact_person'       => 'nullable|string|max:100',
            'phone'                => 'nullable|string|max:20',
            'email'                => 'nullable|email|max:100',
            'ambulance_type'       => 'required|in:BASIC,EMERGENCY,ALS,ICU,NEONATAL,MIXED',
            'rate_contract_type'   => 'required|in:PER_KM,FIXED,PACKAGE',
            'base_rate'            => 'nullable|numeric|min:0',
            'sla_response_minutes' => 'required|integer|min:1',
            'is_active'            => 'boolean',
            'notes'                => 'nullable|string|max:1000',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        Vendor::create($data);

        return redirect()->route('amb.vendors.index')->with('success', 'Vendor created successfully.');
    }

    public function show(Vendor $vendor)
    {
        $vendor->load(['contracts' => fn($q) => $q->orderByDesc('contract_start'), 'ambulances']);
        return view('backend.ambulance.vendors.show', compact('vendor'));
    }

    public function edit(Vendor $vendor)
    {
        return view('backend.ambulance.vendors.edit', compact('vendor'));
    }

    public function update(Request $request, Vendor $vendor)
    {
        $data = $request->validate([
            'vendor_code'          => 'required|unique:amb_vendors,vendor_code,' . $vendor->id . '|max:50',
            'vendor_name'          => 'required|string|max:200',
            'contact_person'       => 'nullable|string|max:100',
            'phone'                => 'nullable|string|max:20',
            'email'                => 'nullable|email|max:100',
            'ambulance_type'       => 'required|in:BASIC,EMERGENCY,ALS,ICU,NEONATAL,MIXED',
            'rate_contract_type'   => 'required|in:PER_KM,FIXED,PACKAGE',
            'base_rate'            => 'nullable|numeric|min:0',
            'sla_response_minutes' => 'required|integer|min:1',
            'is_active'            => 'boolean',
            'notes'                => 'nullable|string|max:1000',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        $vendor->update($data);

        return redirect()->route('amb.vendors.index')->with('success', 'Vendor updated successfully.');
    }

    public function destroy(Vendor $vendor)
    {
        $vendor->delete();
        return redirect()->route('amb.vendors.index')->with('success', 'Vendor deleted.');
    }

    // --- Contracts ---

    public function contractCreate(Vendor $vendor)
    {
        return view('backend.ambulance.vendors.contract_create', compact('vendor'));
    }

    public function contractStore(Request $request, Vendor $vendor)
    {
        $data = $request->validate([
            'contract_ref'         => 'nullable|unique:amb_vendor_contracts,contract_ref|max:50',
            'rate_type'            => 'required|in:PER_KM,FIXED,PACKAGE',
            'rate_amount'          => 'required|numeric|min:0',
            'per_km_rate'          => 'nullable|numeric|min:0',
            'sla_response_minutes' => 'required|integer|min:1',
            'contract_start'       => 'required|date',
            'contract_end'         => 'required|date|after:contract_start',
            'terms'                => 'nullable|string',
        ]);

        $data['vendor_id']   = $vendor->id;
        $data['created_by']  = auth()->id();
        $data['status']      = 'ACTIVE';

        VendorContract::create($data);

        return redirect()->route('amb.vendors.show', $vendor)->with('success', 'Contract added.');
    }
}
