<?php

namespace App\Modules\Ambulance\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Ambulance\Models\Ambulance;
use App\Modules\Ambulance\Models\Vendor;
use Illuminate\Http\Request;

class AmbulanceController extends Controller
{
    // Display a listing of the ambulances
    public function index()
    {
        $ambulances = Ambulance::all();  // Get all ambulances from the database
        return view('backend.ambulance.ambulances.index', compact('ambulances')); // Pass to the view
    }

    // Show the form for creating a new ambulance
    public function create()
    {
        $vendors = Vendor::all(); // Get all vendors (if outsourced)
        return view('backend.ambulance.ambulances.create', compact('vendors')); // Return the create view
    }

    // Store a newly created ambulance in storage
    public function store(Request $request)
    {
        // Validate the incoming request data
        $data = $request->validate([
            'reg_no' => 'required|unique:amb_ambulances',
            'type' => 'required|in:BLS,EMERGENCY,ALS,ICU,NEONATAL',
            'status' => 'required|in:AVAILABLE,ON_TRIP,MAINTENANCE',
            'ownership' => 'required|in:HOSPITAL,OUTSOURCED',
            'vendor_id' => 'nullable|exists:amb_vendors,id',
            'stretcher_capacity' => 'required|integer',
            'attendants_capacity' => 'required|integer',
            'oxygen_capacity' => 'nullable|string',
            'fitness_expiry' => 'nullable|date',
            'insurance_expiry' => 'nullable|date',
        ]);

        // Create a new ambulance entry in the database
        Ambulance::create($data);

        // Redirect back to the index page with a success message
        return redirect()->route('amb.ambulances.index')->with('success', 'Ambulance created successfully!');
    }

    // Display the specified ambulance
    public function show(Ambulance $ambulance)
    {
        return view('backend.ambulance.ambulances.show', compact('ambulance')); // Show the ambulance details
    }

    // Show the form for editing the specified ambulance
    public function edit(Ambulance $ambulance)
    {
        $vendors = Vendor::all(); // Get all vendors for outsourced ambulances
        return view('backend.ambulance.ambulances.edit', compact('ambulance', 'vendors')); // Return the edit view
    }

    // Update the specified ambulance in storage
    public function update(Request $request, Ambulance $ambulance)
    {
        // Validate the incoming request data
        $data = $request->validate([
            'reg_no' => 'required|unique:amb_ambulances,reg_no,' . $ambulance->id,
            'type' => 'required|in:BLS,EMERGENCY,ALS,ICU,NEONATAL',
            'status' => 'required|in:AVAILABLE,ON_TRIP,MAINTENANCE',
            'ownership' => 'required|in:HOSPITAL,OUTSOURCED',
            'vendor_id' => 'nullable|exists:amb_vendors,id',
            'stretcher_capacity' => 'required|integer',
            'attendants_capacity' => 'required|integer',
            'oxygen_capacity' => 'nullable|string',
            'fitness_expiry' => 'nullable|date',
            'insurance_expiry' => 'nullable|date',
        ]);

        // Update the ambulance record in the database
        $ambulance->update($data);

        // Redirect back to the index page with a success message
        return redirect()->route('amb.ambulances.index')->with('success', 'Ambulance updated successfully!');
    }

    // Remove the specified ambulance from storage
    public function destroy(Ambulance $ambulance)
    {
        // Delete the ambulance from the database
        $ambulance->delete();

        // Redirect back to the index page with a success message
        return redirect()->route('amb.ambulances.index')->with('success', 'Ambulance deleted successfully!');
    }
}
