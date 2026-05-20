<?php

namespace App\Modules\Ambulance\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Ambulance\Models\Paramedic;
use Illuminate\Http\Request;

class ParamedicController extends Controller
{
    public function index()
    {
        $paramedics = Paramedic::all();  // Fetch all paramedics
        return view('backend.ambulance.paramedics.index', compact('paramedics'));
    }

    public function create()
    {
        return view('backend.ambulance.paramedics.create');
    }

    public function store(Request $request)
    {
        // Validate incoming request data
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'nid' => 'required|string|unique:amb_paramedics',
            'phone' => 'nullable|string|max:15',
            'certification' => 'required|in:BLS,ACLS',
            'cert_expiry' => 'nullable|date',
            'status' => 'required|in:ACTIVE,SUSPENDED',
        ]);

        // Store the paramedic record
        Paramedic::create($data);

        return redirect()->route('amb.paramedics.index')->with('success', 'Paramedic added successfully!');
    }

    public function show(Paramedic $paramedic)
    {
        return view('backend.ambulance.paramedics.show', compact('paramedic'));
    }

    public function edit(Paramedic $paramedic)
    {
        return view('backend.ambulance.paramedics.edit', compact('paramedic'));
    }

    public function update(Request $request, Paramedic $paramedic)
    {
        // Validate incoming request data
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'nid' => 'required|string|unique:amb_paramedics,nid,' . $paramedic->id,
            'phone' => 'nullable|string|max:15',
            'certification' => 'required|in:BLS,ACLS',
            'cert_expiry' => 'nullable|date',
            'status' => 'required|in:ACTIVE,SUSPENDED',
        ]);

        // Update the paramedic record
        $paramedic->update($data);

        return redirect()->route('amb.paramedics.index')->with('success', 'Paramedic updated successfully!');
    }

    public function destroy(Paramedic $paramedic)
    {
        // Delete the paramedic record
        $paramedic->delete();
        return redirect()->route('amb.paramedics.index')->with('success', 'Paramedic deleted successfully!');
    }
}
