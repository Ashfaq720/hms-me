<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Symptom;
use Illuminate\Http\Request;

class SymptomController extends Controller
{
    public function index()
    {
        $data = Symptom::all();
        return view('symptom.index', compact('data'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        Symptom::create([
            'name' => $request->name,
            'notes' => $request->notes,
        ]);

        return redirect()->route('symptoms.index')
            ->with('success', 'Symptom created successfully.');
    }

    public function update(Request $request, Symptom $symptom)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $symptom->update([
            'name' => $request->name,
            'notes' => $request->notes,
        ]);

        return redirect()->route('symptoms.index')
            ->with('success', 'Symptom updated successfully.');
    }

    public function destroy(Symptom $symptom)
    {
        $symptom->delete();
        return redirect()->route('symptoms.index')
            ->with('success', 'Symptom deleted successfully.');
    }
}
