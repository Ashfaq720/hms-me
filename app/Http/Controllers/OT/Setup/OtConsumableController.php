<?php

namespace App\Http\Controllers\OT\Setup;

use App\Http\Controllers\Controller;
use App\Models\Ot\OtConsumable;
use Illuminate\Http\Request;

class OtConsumableController extends Controller
{
    public function index()
    {
        abort_if(! auth()->user()?->can('ot_setup_access') && ! auth()->user()?->hasRole('Super Admin'), 403);
        $items = OtConsumable::orderBy('name')->paginate(30);

        return view('ot.setup.consumables.index', compact('items'));
    }

    public function create()
    {
        return view('ot.setup.consumables.create');
    }

    public function store(Request $request)
    {
        OtConsumable::create($this->validateData($request));

        return redirect()->route('ot.setup.consumables.index')->with('success', 'Created.');
    }

    public function show($id)
    {
        $item = OtConsumable::findOrFail($id);

        return view('ot.setup.consumables.show', compact('item'));
    }

    public function edit($id)
    {
        $item = OtConsumable::findOrFail($id);

        return view('ot.setup.consumables.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = OtConsumable::findOrFail($id);
        $item->update($this->validateData($request, $item->id));

        return redirect()->route('ot.setup.consumables.index')->with('success', 'Updated.');
    }

    public function destroy($id)
    {
        OtConsumable::findOrFail($id)->delete();

        return back()->with('success', 'Deleted.');
    }

    protected function validateData(Request $request, $excludeId = null): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:ot_consumables,code' . ($excludeId ? ",{$excludeId}" : ''),
            'type' => 'required|in:consumable,implant,instrument,medicine',
            'unit' => 'nullable|string|max:20',
            'rate' => 'nullable|numeric|min:0',
            'is_implant' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);
    }
}
