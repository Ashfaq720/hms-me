<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\LabInvestigationType;
use Illuminate\Http\Request;

class LabInvestigationTypeController extends Controller
{
    public function index()
    {
        $data = LabInvestigationType::all();
        return view('lab-investigation-type.index', compact('data'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        LabInvestigationType::create([
            'name' => $request->name,
            'notes' => $request->notes,
        ]);

        return redirect()->route('lab-investigation-types.index')
            ->with('success', 'Lab Investigation Type created successfully.');
    }

    public function update(Request $request, LabInvestigationType $labInvestigationType)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $labInvestigationType->update([
            'name' => $request->name,
            'notes' => $request->notes,
        ]);

        return redirect()->route('lab-investigation-types.index')
            ->with('success', 'Lab Investigation Type updated successfully.');
    }

    public function destroy(LabInvestigationType $labInvestigationType)
    {
        $labInvestigationType->delete();
        return redirect()->route('lab-investigation-types.index')
            ->with('success', 'Lab Investigation Type deleted successfully.');
    }
}
