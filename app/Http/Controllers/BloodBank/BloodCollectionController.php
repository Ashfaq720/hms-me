<?php

namespace App\Http\Controllers\BloodBank;

use App\Http\Controllers\Controller;
use App\Models\BloodBank\BloodCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BloodCollectionController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'donor_id'       => 'required|exists:blood_donors,id',
            'blood_group_id' => 'required|exists:blood_groups,id',
            'donate_date'    => 'required|date',
            'bag_no'         => 'nullable|string|unique:blood_collections,bag_no',
            'volume'         => 'required|numeric|min:0',
            'unit'           => 'required|string|max:20',
            'lot'            => 'nullable|string|max:100',
            'charge_id'      => 'nullable|exists:charges,id',
            'charge_name'    => 'nullable|string|max:255',
        ]);

        $validated['created_by'] = Auth::id();

        BloodCollection::create($validated);

        return redirect()->back()->with('success', 'Blood collection recorded successfully.');
    }

    public function update(Request $request, BloodCollection $blood_collection)
    {
        $validated = $request->validate([
            'donor_id'       => 'required|exists:blood_donors,id',
            'blood_group_id' => 'required|exists:blood_groups,id',
            'donate_date'    => 'required|date',
            'bag_no'         => 'nullable|string|unique:blood_collections,bag_no,' . $blood_collection->id,
            'volume'         => 'required|numeric|min:0',
            'unit'           => 'required|string|max:20',
            'lot'            => 'nullable|string|max:100',
            'charge_id'      => 'nullable|exists:charges,id',
            'charge_name'    => 'nullable|string|max:255',
        ]);

        $validated['updated_by'] = Auth::id();

        $blood_collection->update($validated);

        return redirect()->back()->with('success', 'Blood collection updated successfully.');
    }

    public function destroy(BloodCollection $blood_collection)
    {
        $blood_collection->delete();

        return redirect()->back()->with('success', 'Blood collection deleted successfully.');
    }
}
