<?php

namespace App\Modules\Ambulance\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Ambulance\Models\Paramedic;
use Illuminate\Http\Request;

class ParamedicController extends Controller
{
    public function index()
    {
        $paramedics = Paramedic::orderBy('name')->get();
        return view('backend.ambulance.paramedics.index', compact('paramedics'));
    }

    public function create()
    {
        return view('backend.ambulance.paramedics.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'nid'           => 'required|string|unique:amb_paramedics|max:50',
            'phone'         => 'nullable|string|max:20',
            'certification' => 'required|in:BLS,ACLS',
            'skill_level'   => 'required|in:BASIC,ADVANCED',
            'cert_expiry'   => 'nullable|date',
            'shift'         => 'nullable|in:MORNING,EVENING,NIGHT',
            'availability'  => 'required|in:AVAILABLE,ASSIGNED,OFF_DUTY',
            'status'        => 'required|in:ACTIVE,SUSPENDED',
        ]);

        Paramedic::create($data);
        return redirect()->route('amb.paramedics.index')->with('success', 'Paramedic added successfully.');
    }

    public function show(Paramedic $paramedic)
    {
        $paramedic->load(['trips' => fn($q) => $q->with(['ambulance', 'request'])->latest()->limit(5)]);
        return view('backend.ambulance.paramedics.show', compact('paramedic'));
    }

    public function edit(Paramedic $paramedic)
    {
        return view('backend.ambulance.paramedics.edit', compact('paramedic'));
    }

    public function update(Request $request, Paramedic $paramedic)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'nid'           => 'required|string|unique:amb_paramedics,nid,' . $paramedic->id . '|max:50',
            'phone'         => 'nullable|string|max:20',
            'certification' => 'required|in:BLS,ACLS',
            'skill_level'   => 'required|in:BASIC,ADVANCED',
            'cert_expiry'   => 'nullable|date',
            'shift'         => 'nullable|in:MORNING,EVENING,NIGHT',
            'availability'  => 'required|in:AVAILABLE,ASSIGNED,OFF_DUTY',
            'status'        => 'required|in:ACTIVE,SUSPENDED',
        ]);

        $paramedic->update($data);
        return redirect()->route('amb.paramedics.show', $paramedic)->with('success', 'Paramedic updated successfully.');
    }

    public function destroy(Paramedic $paramedic)
    {
        if ($paramedic->availability === 'ASSIGNED') {
            return back()->with('error', 'Cannot delete a paramedic who is currently assigned to a trip.');
        }

        $paramedic->delete();
        return redirect()->route('amb.paramedics.index')->with('success', 'Paramedic deleted.');
    }
}
