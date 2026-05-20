<?php

namespace App\Http\Controllers\Charges;

use App\Http\Controllers\Controller;
use App\Models\Charges\ChargeType;
use App\Models\Charges\UniteType;
use Illuminate\Http\Request;

class ChargeTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $chargeTypes = ChargeType::orderBy('id', 'desc')->get();

        return view('charge-types.index', compact('chargeTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('charge-types.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        ChargeType::create([
            'name' => $request->name,
        ]);

        return redirect()->route('admin.charge-types.index')
            ->with('success', 'Charge Type created successfully.');
    }


    public function edit($id)
    {
        $chargeType = ChargeType::findOrFail($id);
        return view('charge-types.edit', compact('chargeType'));
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $chargeType = ChargeType::findOrFail($id);

        $chargeType->update([
            'name' => $request->name,
        ]);

        return redirect()->route('admin.charge-types.index')
            ->with('success', 'Charge Type updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $chargeType = ChargeType::findOrFail($id);
        $chargeType->delete();

        return redirect()->route('admin.charge-types.index')
            ->with('success', 'Unite Type delete successfully.');
    }
}
