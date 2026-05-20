<?php

namespace App\Modules\Ambulance\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Ambulance\Models\Driver;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    public function index()
    {
        $drivers = Driver::all();
        return view('backend.ambulance.drivers.index', compact('drivers'));
    }

    public function create()
    {
        return view('backend.ambulance.drivers.create');
    }

    public function store(Request $request)
    {
        // Validate incoming data
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'nid' => 'required|string|unique:amb_drivers',
            'phone' => 'nullable|string|max:15',
            'license_number' => 'nullable|string',
            'license_type' => 'nullable|string',
            'license_expiry' => 'nullable|date',
            'status' => 'required|in:ACTIVE,SUSPENDED',
        ]);

        // Store the driver record
        Driver::create($data);

        return redirect()->route('amb.drivers.index')->with('success', 'Driver added successfully!');
    }

    public function show(Driver $driver)
    {
        return view('backend.ambulance.drivers.show', compact('driver'));
    }

    public function edit(Driver $driver)
    {
        return view('backend.ambulance.drivers.edit', compact('driver'));
    }

    public function update(Request $request, Driver $driver)
    {
        // Validate incoming data
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'nid' => 'required|string|unique:amb_drivers,nid,' . $driver->id,
            'phone' => 'nullable|string|max:15',
            'license_number' => 'nullable|string',
            'license_type' => 'nullable|string',
            'license_expiry' => 'nullable|date',
            'status' => 'required|in:ACTIVE,SUSPENDED',
        ]);

        // Update the driver record
        $driver->update($data);

        return redirect()->route('amb.drivers.index')->with('success', 'Driver updated successfully!');
    }

    public function destroy(Driver $driver)
    {
        $driver->delete();
        return redirect()->route('amb.drivers.index')->with('success', 'Driver deleted successfully!');
    }
}
