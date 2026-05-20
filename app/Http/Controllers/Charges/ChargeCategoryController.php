<?php

namespace App\Http\Controllers\Charges;

use App\Http\Controllers\Controller;
use App\Models\Charges\ChargeCategory;
use App\Models\Charges\ChargeType;
use App\Models\Charges\UniteType;
use Illuminate\Http\Request;

class ChargeCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $chargeCategories = ChargeCategory::orderBy('id', 'desc')->get();

        return view('charge-categories.index', compact('chargeCategories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $chargeTypes = ChargeType::get();
        return view('charge-categories.create', compact('chargeTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'charge_type_id' => 'required',
            'description'    => 'nullable|string',
        ]);

        ChargeCategory::create([
            'name' => $request->name,
            'charge_type_id' => $request->charge_type_id,
            'description' => $request->description,
        ]);

        return redirect()->route('admin.charge-categories.index')
            ->with('success', 'Charge Categories created successfully.');
    }


    public function edit($id)
    {
        $chargeCategory = ChargeCategory::findOrFail($id);
        $chargeTypes = ChargeType::get();
        return view('charge-categories.edit', compact('chargeCategory','chargeTypes'));
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

        return redirect()->route('admin.charge-categories.index')
            ->with('success', 'Charge Type updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $chargeType = ChargeType::findOrFail($id);
        $chargeType->delete();

        return redirect()->route('admin.charge-categories.index')
            ->with('success', 'Unite Type delete successfully.');
    }
}
