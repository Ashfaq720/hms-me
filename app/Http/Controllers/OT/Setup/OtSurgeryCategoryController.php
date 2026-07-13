<?php

namespace App\Http\Controllers\OT\Setup;

use App\Http\Controllers\Controller;
use App\Models\Ot\OtSurgeryCategory;
use Illuminate\Http\Request;

class OtSurgeryCategoryController extends Controller
{
    public function index()
    {
        abort_if(! auth()->user()?->can('ot_setup_access') && ! auth()->user()?->hasRole('Super Admin'), 403);
        $items = OtSurgeryCategory::orderBy('name')->paginate(30);

        return view('ot.setup.surgery-categories.index', compact('items'));
    }

    public function create()
    {
        return view('ot.setup.surgery-categories.create');
    }

    public function store(Request $request)
    {
        OtSurgeryCategory::create($this->validateData($request));

        return redirect()->route('ot.setup.surgery-categories.index')->with('success', 'Category created.');
    }

    public function show($id)
    {
        $item = OtSurgeryCategory::findOrFail($id);

        return view('ot.setup.surgery-categories.show', compact('item'));
    }

    public function edit($id)
    {
        $item = OtSurgeryCategory::findOrFail($id);

        return view('ot.setup.surgery-categories.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = OtSurgeryCategory::findOrFail($id);
        $item->update($this->validateData($request, $item->id));

        return redirect()->route('ot.setup.surgery-categories.index')->with('success', 'Updated.');
    }

    public function destroy($id)
    {
        OtSurgeryCategory::findOrFail($id)->delete();

        return back()->with('success', 'Deleted.');
    }

    protected function validateData(Request $request, $excludeId = null): array
    {
        return $request->validate([
            'name' => 'required|string|max:255|unique:ot_surgery_categories,name' . ($excludeId ? ",{$excludeId}" : ''),
            'code' => 'nullable|string|max:30',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);
    }
}
