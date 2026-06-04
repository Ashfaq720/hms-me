<?php

namespace App\Http\Controllers\OT\Setup;

use App\Http\Controllers\Controller;
use App\Models\Ot\OtSurgeryCategory;
use App\Models\Ot\OtSurgeryType;
use Illuminate\Http\Request;

class OtSurgeryTypeController extends Controller
{
    public function index()
    {
        abort_if(! auth()->user()?->can('ot_setup_access') && ! auth()->user()?->hasRole('Super Admin'), 403);
        $items = OtSurgeryType::with('category')->orderBy('name')->paginate(30);

        return view('ot.setup.surgery-types.index', compact('items'));
    }

    public function create()
    {
        $categories = OtSurgeryCategory::where('is_active', true)->orderBy('name')->get();

        return view('ot.setup.surgery-types.create', compact('categories'));
    }

    public function store(Request $request)
    {
        OtSurgeryType::create($this->validateData($request));

        return redirect()->route('ot.setup.surgery-types.index')->with('success', 'Created.');
    }

    public function show($id)
    {
        $item = OtSurgeryType::with('category')->findOrFail($id);

        return view('ot.setup.surgery-types.show', compact('item'));
    }

    public function edit($id)
    {
        $item = OtSurgeryType::findOrFail($id);
        $categories = OtSurgeryCategory::where('is_active', true)->orderBy('name')->get();

        return view('ot.setup.surgery-types.edit', compact('item', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $item = OtSurgeryType::findOrFail($id);
        $item->update($this->validateData($request));

        return redirect()->route('ot.setup.surgery-types.index')->with('success', 'Updated.');
    }

    public function destroy($id)
    {
        OtSurgeryType::findOrFail($id)->delete();

        return back()->with('success', 'Deleted.');
    }

    protected function validateData(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:30',
            'category_id' => 'nullable|exists:ot_surgery_categories,id',
            'standard_duration_minutes' => 'required|integer|min:5|max:1440',
            'standard_charge' => 'nullable|numeric|min:0',
            'surgeon_charge' => 'nullable|numeric|min:0',
            'anesthesia_charge' => 'nullable|numeric|min:0',
            'ot_room_charge' => 'nullable|numeric|min:0',
            'recovery_charge' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);
    }
}
