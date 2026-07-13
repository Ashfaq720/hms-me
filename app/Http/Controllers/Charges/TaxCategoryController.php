<?php

namespace App\Http\Controllers\Charges;

use App\Http\Controllers\Controller;
use App\Models\Charges\TaxCategory;
use App\Models\Charges\UniteType;
use Illuminate\Http\Request;

class TaxCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $taxCategory = TaxCategory::orderBy('id', 'desc')->get();

        return view('tax-category.index', compact('taxCategory'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('tax-category.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'percentage' => 'required|string|max:255',
        ]);

        TaxCategory::create([
            'name' => $request->name,
            'percentage' => $request->percentage
        ]);

        return redirect()->route('admin.tax-categories.index')
            ->with('success', 'Tax Category created successfully.');
    }


    public function edit($id)
    {
        $taxCategory = TaxCategory::findOrFail($id);
        return view('tax-category.edit', compact('taxCategory'));
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'percentage' => 'required|string|max:255',
        ]);

        $tax_category = TaxCategory::findOrFail($id);

        $tax_category->update([
            'name' => $request->name,
            'percentage' => $request->percentage,
        ]);

        return redirect()->route('admin.tax-categories.index')
            ->with('success', 'Tax Category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $tax_category = TaxCategory::findOrFail($id);
        $tax_category->delete();

        return redirect()->route('admin.tax-categories.index')
            ->with('success', 'Tax Category delete successfully.');
    }
}
