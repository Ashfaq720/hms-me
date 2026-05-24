<?php

namespace App\Http\Controllers\OT\Setup;

use App\Http\Controllers\Controller;
use App\Models\Ot\OtAnesthesiaType;
use Illuminate\Http\Request;

class OtAnesthesiaTypeController extends Controller
{
    public function index()
    {
        abort_if(! auth()->user()?->can('ot_setup_access') && ! auth()->user()?->hasRole('Super Admin'), 403);
        $items = OtAnesthesiaType::orderBy('name')->paginate(30);

        return view('ot.setup.anesthesia-types.index', compact('items'));
    }

    public function create()
    {
        return view('ot.setup.anesthesia-types.create');
    }

    public function store(Request $request)
    {
        OtAnesthesiaType::create($this->validateData($request));

        return redirect()->route('ot.setup.anesthesia-types.index')->with('success', 'Created.');
    }

    public function show($id)
    {
        $item = OtAnesthesiaType::findOrFail($id);

        return view('ot.setup.anesthesia-types.show', compact('item'));
    }

    public function edit($id)
    {
        $item = OtAnesthesiaType::findOrFail($id);

        return view('ot.setup.anesthesia-types.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = OtAnesthesiaType::findOrFail($id);
        $item->update($this->validateData($request, $item->id));

        return redirect()->route('ot.setup.anesthesia-types.index')->with('success', 'Updated.');
    }

    public function destroy($id)
    {
        OtAnesthesiaType::findOrFail($id)->delete();

        return back()->with('success', 'Deleted.');
    }

    protected function validateData(Request $request, $excludeId = null): array
    {
        return $request->validate([
            'name' => 'required|string|max:255|unique:ot_anesthesia_types,name' . ($excludeId ? ",{$excludeId}" : ''),
            'code' => 'nullable|string|max:30',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);
    }
}
