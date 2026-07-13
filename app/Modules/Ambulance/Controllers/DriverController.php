<?php

namespace App\Modules\Ambulance\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Ambulance\Models\Driver;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    public function index()
    {
        $drivers = Driver::orderBy('name')->get();
        return view('backend.ambulance.drivers.index', compact('drivers'));
    }

    public function create()
    {
        return view('backend.ambulance.drivers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'nid'            => 'required|string|unique:amb_drivers|max:50',
            'phone'          => 'nullable|string|max:20',
            'license_number' => 'nullable|string|max:100',
            'license_type'   => 'nullable|string|max:100',
            'license_expiry' => 'nullable|date',
            'shift'          => 'nullable|in:MORNING,EVENING,NIGHT',
            'availability'   => 'required|in:AVAILABLE,ASSIGNED,OFF_DUTY',
            'status'         => 'required|in:ACTIVE,SUSPENDED',
        ]);

        Driver::create($data);
        return redirect()->route('amb.drivers.index')->with('success', 'Driver added successfully.');
    }

    public function show(Driver $driver)
    {
        $driver->load(['trips' => fn($q) => $q->with(['ambulance', 'request'])->latest()->limit(5)]);
        return view('backend.ambulance.drivers.show', compact('driver'));
    }

    public function edit(Driver $driver)
    {
        return view('backend.ambulance.drivers.edit', compact('driver'));
    }

    public function update(Request $request, Driver $driver)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'nid'            => 'required|string|unique:amb_drivers,nid,' . $driver->id . '|max:50',
            'phone'          => 'nullable|string|max:20',
            'license_number' => 'nullable|string|max:100',
            'license_type'   => 'nullable|string|max:100',
            'license_expiry' => 'nullable|date',
            'shift'          => 'nullable|in:MORNING,EVENING,NIGHT',
            'availability'   => 'required|in:AVAILABLE,ASSIGNED,OFF_DUTY',
            'status'         => 'required|in:ACTIVE,SUSPENDED',
        ]);

        $driver->update($data);
        return redirect()->route('amb.drivers.show', $driver)->with('success', 'Driver updated successfully.');
    }

    public function destroy(Driver $driver)
    {
        if ($driver->availability === 'ASSIGNED') {
            return back()->with('error', 'Cannot delete a driver who is currently assigned to a trip.');
        }

        $driver->delete();
        return redirect()->route('amb.drivers.index')->with('success', 'Driver deleted.');
    }
}
