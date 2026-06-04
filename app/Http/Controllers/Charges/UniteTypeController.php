<?php

namespace App\Http\Controllers\Charges;

use App\Http\Controllers\Controller;
use App\Models\Charges\UniteType;
use Illuminate\Http\Request;

class UniteTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $unite_types = UniteType::orderBy('id', 'desc')->get();

        return view('unite-type.index', compact('unite_types'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('unite-type.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        UniteType::create([
            'name' => $request->name,
        ]);

        return redirect()->route('admin.unite-types.index')
            ->with('success', 'Unite Type created successfully.');
    }


    public function edit($id)
    {
        $uniteType = UniteType::findOrFail($id);
        return view('unite-type.edit', compact('uniteType'));
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $unite_type = UniteType::findOrFail($id);

        $unite_type->update([
            'name' => $request->name,
        ]);

        return redirect()->route('admin.unite-types.index')
            ->with('success', 'Unite Type updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $unite_type = UniteType::findOrFail($id);
        $unite_type->delete();

        return redirect()->route('admin.unite-types.index')
            ->with('success', 'Unite Type delete successfully.');
    }
}
