<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\LabInvestigationCategory;
use App\Models\LabInvestigationType;
use Illuminate\Http\Request;

class LabInvestigationCategoryController extends Controller
{
    public function index()
    {
        $data = LabInvestigationCategory::with(['type'])->get();
        $types = LabInvestigationType::all();
        return view('lab-investigation-category.index', compact('data', 'types'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type_id' => 'required|exists:lab_investigation_types,id',
            'notes' => 'nullable|string',
        ]);

        LabInvestigationCategory::create([
            'name' => $request->name,
            'type_id' => $request->type_id,
            'notes' => $request->notes,
        ]);

        return redirect()->route('lab-investigation-categories.index')
            ->with('success', 'Lab Investigation Category created successfully.');
    }

    public function update(Request $request, LabInvestigationCategory $labInvestigationCategory)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type_id' => 'required|exists:lab_investigation_types,id',
            'notes' => 'nullable|string',
        ]);

        $labInvestigationCategory->update([
            'name' => $request->name,
            'type_id' => $request->type_id,
            'notes' => $request->notes,
        ]);

        return redirect()->route('lab-investigation-categories.index')
            ->with('success', 'Lab Investigation Category updated successfully.');
    }

    public function destroy(LabInvestigationCategory $labInvestigationCategory)
    {
        $labInvestigationCategory->delete();
        return redirect()->route('lab-investigation-categories.index')
            ->with('success', 'Lab Investigation Category deleted successfully.');
    }
}
