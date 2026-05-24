<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\LabInvestigation;
use App\Models\LabInvestigationCategory;
use Illuminate\Http\Request;

class LabInvestigationController extends Controller
{
    public function index()
    {
        $data = LabInvestigation::with(['category'])->get();
        $categories = LabInvestigationCategory::all();
        return view('lab-investigation.index', compact('data', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'nullable|string|max:20',
            'category_id' => 'nullable|exists:lab_investigation_categories,id',
            'department' => 'nullable|string|max:255',
            'sample_type' => 'nullable|string|max:255',
            'report_time_hours' => 'nullable|integer',
            'normal_range' => 'nullable|string',
            'unit' => 'nullable|string|max:50',
            'method' => 'nullable|string|max:255',
            'preparation' => 'nullable|string',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'sort_order' => 'nullable|integer',
            'notes' => 'nullable|string',
        ]);

        LabInvestigation::create($request->only([
            'name', 'short_name', 'category_id', 'department', 'sample_type',
            'report_time_hours', 'normal_range', 'unit', 'method',
            'preparation', 'description', 'price', 'sort_order', 'notes',
        ]));

        return redirect()->route('lab-investigations.index')
            ->with('success', 'Lab Investigation created successfully.');
    }

    public function update(Request $request, LabInvestigation $labInvestigation)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'nullable|string|max:20',
            'category_id' => 'nullable|exists:lab_investigation_categories,id',
            'department' => 'nullable|string|max:255',
            'sample_type' => 'nullable|string|max:255',
            'report_time_hours' => 'nullable|integer',
            'normal_range' => 'nullable|string',
            'unit' => 'nullable|string|max:50',
            'method' => 'nullable|string|max:255',
            'preparation' => 'nullable|string',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'sort_order' => 'nullable|integer',
            'notes' => 'nullable|string',
        ]);

        $labInvestigation->update($request->only([
            'name', 'short_name', 'category_id', 'department', 'sample_type',
            'report_time_hours', 'normal_range', 'unit', 'method',
            'preparation', 'description', 'price', 'sort_order', 'notes',
        ]));

        return redirect()->route('lab-investigations.index')
            ->with('success', 'Lab Investigation updated successfully.');
    }

    public function destroy(LabInvestigation $labInvestigation)
    {
        $labInvestigation->delete();
        return redirect()->route('lab-investigations.index')
            ->with('success', 'Lab Investigation deleted successfully.');
    }
}
