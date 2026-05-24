<?php

namespace App\Http\Controllers\Charges;

use App\Http\Controllers\Controller;
use App\Models\Charges\Charge;
use App\Models\Charges\ChargeCategory;
use App\Models\Charges\ChargeType;
use App\Models\Charges\TaxCategory;
use App\Models\Charges\UniteType;
use App\Models\department;
use Illuminate\Http\Request;

class HospitalChargeController extends Controller
{
    public function index(Request $request)
    {
        $charges = Charge::with(['chargeType', 'chargeCategory', 'uniteType', 'taxCategory'])
                         ->latest('id')
                         ->paginate(25)
                         ->withQueryString();

        return view('charges.index', compact('charges'));
    }

    public function create()
    {
        $chargeTypes      = ChargeType::pluck('name', 'id');
        $chargeCategories = ChargeCategory::pluck('name', 'id');
        $unitTypes        = UniteType::pluck('name', 'id');
        $taxCategories    = TaxCategory::pluck('name', 'id');

        return view('charges.create', compact(
            'chargeTypes',
            'chargeCategories',
            'unitTypes',
            'taxCategories'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'charge_type_id'      => 'required|exists:charge_types,id',
            'charge_category_id'  => 'required|exists:charge_categories,id',
            'unite_type_id'       => 'required|exists:unite_types,id',
            'tax_category_id'     => 'required|exists:tax_categories,id',
            'charge_name'         => 'required|string|max:255',
            'tax'                 => 'nullable|numeric|min:0',
            'standard_charge'     => 'required|numeric|min:0',
            'description'         => 'nullable|string',
        ]);

        Charge::create($validated);

        return redirect()
            ->route('admin.charges.index')
            ->with('success', 'Charge created successfully.');
    }

    public function edit($id)
    {
        $charge = Charge::findOrFail($id);

        $chargeTypes      = ChargeType::pluck('name', 'id');
        $chargeCategories = ChargeCategory::pluck('name', 'id');
        $unitTypes        = UniteType::pluck('name', 'id');
        $taxCategories    = TaxCategory::pluck('name', 'id');

        return view('charges.edit', compact(
            'charge',
            'chargeTypes',
            'chargeCategories',
            'unitTypes',
            'taxCategories'
        ));
    }

    public function update(Request $request, $id)
    {
        $charge = Charge::findOrFail($id);

        $validated = $request->validate([
            'charge_type_id'      => 'required|exists:charge_types,id',
            'charge_category_id'  => 'required|exists:charge_categories,id',
            'unite_type_id'        => 'required|exists:unite_types,id',
            'tax_category_id'     => 'required|exists:tax_categories,id',
            'charge_name'         => 'required|string|max:255',
            'tax'                 => 'nullable|numeric|min:0',
            'standard_charge'     => 'required|numeric|min:0',
            'description'         => 'nullable|string',
        ]);

        $charge->update($validated);

        return redirect()
            ->route('admin.charges.index')
            ->with('success', 'Charge updated successfully.');
    }

    public function destroy($id)
    {
        $charge = Charge::findOrFail($id);
        $charge->delete();

        return redirect()
            ->route('admin.charges.index')
            ->with('success', 'Charge deleted successfully.');
    }

    public function getChargeCategories(Request $request)
    {
        $chargeCategories = ChargeCategory::where('charge_type_id', $request->charge_type_id)
            ->pluck('name', 'id');

        return response()->json($chargeCategories);
    }

    public function getChargesByCategory($categoryId)
    {
        $charges = Charge::where('charge_category_id', $categoryId)
            ->orderBy('charge_name')
            ->get(['id', 'charge_name']);

        return response()->json($charges);
    }

    public function getChargeDetails($chargeId)
    {
        $charge = Charge::with(['chargeType', 'chargeCategory', 'uniteType', 'taxCategory'])
            ->findOrFail($chargeId);

        return response()->json([
            'id' => $charge->id,
            'charge_name' => $charge->charge_name,
            'charge_type_id' => $charge->charge_type_id,
            'charge_category_id' => $charge->charge_category_id,
            'unite_type_id' => $charge->unite_type_id,
            'tax_category_id' => $charge->tax_category_id,
            'standard_charge' => (float) $charge->standard_charge,
            'tax' => (float) $charge->tax,
            'description' => $charge->description,
        ]);
    }
}
