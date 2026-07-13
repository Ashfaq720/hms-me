<?php

namespace App\Modules\Ambulance\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Ambulance\Models\Ambulance;
use App\Modules\Ambulance\Models\AmbulanceEquipment;
use App\Modules\Ambulance\Models\Vendor;
use Illuminate\Http\Request;

class AmbulanceController extends Controller
{
    public function index()
    {
        $ambulances = Ambulance::with('vendor')
            ->withCount('equipment')
            ->orderBy('reg_no')
            ->get();

        return view('backend.ambulance.ambulances.index', compact('ambulances'));
    }

    public function create()
    {
        $vendors = Vendor::where('is_active', true)->orderBy('vendor_name')->get();
        return view('backend.ambulance.ambulances.create', compact('vendors'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'reg_no'              => 'required|unique:amb_ambulances|max:50',
            'type'                => 'required|in:BLS,GENERAL,EMERGENCY,ALS,ICU,NEONATAL',
            'ownership'           => 'required|in:HOSPITAL,OUTSOURCED',
            'vendor_id'           => 'nullable|exists:amb_vendors,id',
            'stretcher_capacity'  => 'required|integer|min:1',
            'attendants_capacity' => 'required|integer|min:0',
            'oxygen_capacity'     => 'nullable|string|max:50',
            'status'              => 'required|in:AVAILABLE,ON_TRIP,MAINTENANCE',
            'fitness_expiry'      => 'nullable|date',
            'insurance_expiry'    => 'nullable|date',
            'license_validity'    => 'nullable|date',
        ]);

        // Outsourced ambulance must have vendor
        if ($data['ownership'] === 'OUTSOURCED' && empty($data['vendor_id'])) {
            return back()->withInput()->withErrors(['vendor_id' => 'Vendor is required for outsourced ambulance.']);
        }

        Ambulance::create($data);
        return redirect()->route('amb.ambulances.index')->with('success', 'Ambulance created successfully.');
    }

    public function show(Ambulance $ambulance)
    {
        $ambulance->load(['vendor', 'equipment', 'trips' => fn($q) => $q->latest()->limit(5)]);
        return view('backend.ambulance.ambulances.show', compact('ambulance'));
    }

    public function edit(Ambulance $ambulance)
    {
        $vendors = Vendor::where('is_active', true)->orderBy('vendor_name')->get();
        return view('backend.ambulance.ambulances.edit', compact('ambulance', 'vendors'));
    }

    public function update(Request $request, Ambulance $ambulance)
    {
        $data = $request->validate([
            'reg_no'              => 'required|unique:amb_ambulances,reg_no,' . $ambulance->id . '|max:50',
            'type'                => 'required|in:BLS,GENERAL,EMERGENCY,ALS,ICU,NEONATAL',
            'ownership'           => 'required|in:HOSPITAL,OUTSOURCED',
            'vendor_id'           => 'nullable|exists:amb_vendors,id',
            'stretcher_capacity'  => 'required|integer|min:1',
            'attendants_capacity' => 'required|integer|min:0',
            'oxygen_capacity'     => 'nullable|string|max:50',
            'status'              => 'required|in:AVAILABLE,ON_TRIP,MAINTENANCE',
            'fitness_expiry'      => 'nullable|date',
            'insurance_expiry'    => 'nullable|date',
            'license_validity'    => 'nullable|date',
        ]);

        if ($data['ownership'] === 'OUTSOURCED' && empty($data['vendor_id'])) {
            return back()->withInput()->withErrors(['vendor_id' => 'Vendor is required for outsourced ambulance.']);
        }

        $ambulance->update($data);
        return redirect()->route('amb.ambulances.show', $ambulance)->with('success', 'Ambulance updated successfully.');
    }

    public function destroy(Ambulance $ambulance)
    {
        // Prevent deleting ambulance on active trip
        if ($ambulance->status === 'ON_TRIP') {
            return back()->with('error', 'Cannot delete an ambulance that is currently on a trip.');
        }
        $ambulance->delete();
        return redirect()->route('amb.ambulances.index')->with('success', 'Ambulance deleted.');
    }

    // --- Equipment management (inline on show page) ---

    public function equipmentStore(Request $request, Ambulance $ambulance)
    {
        $data = $request->validate([
            'equipment_name' => 'required|string|max:100',
            'equipment_type' => 'nullable|string|max:100',
            'quantity'       => 'required|integer|min:1',
            'condition'      => 'required|in:GOOD,NEEDS_SERVICE,OUT_OF_ORDER',
            'last_checked'   => 'nullable|date',
        ]);

        $data['ambulance_id'] = $ambulance->id;
        AmbulanceEquipment::create($data);

        return back()->with('success', 'Equipment added.');
    }

    public function equipmentDestroy(Ambulance $ambulance, AmbulanceEquipment $equipment)
    {
        $equipment->delete();
        return back()->with('success', 'Equipment removed.');
    }
}
