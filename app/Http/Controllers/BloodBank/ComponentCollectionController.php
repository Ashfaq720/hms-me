<?php

namespace App\Http\Controllers\BloodBank;

use App\Http\Controllers\Controller;
use App\Models\BloodBank\ComponentCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ComponentCollectionController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'blood_group_id'      => 'required|exists:blood_groups,id',
            'blood_collection_id' => 'required|exists:blood_collections,id',
            'donor_id'            => 'required|exists:blood_donors,id',
            'datetime'            => 'required|date',
            'components'          => 'required|array|min:1',
            'components.*.component_id' => 'required|exists:components,id',
            'components.*.volume'       => 'nullable|numeric|min:0',
            'components.*.unit'         => 'nullable|string|max:20',
            'components.*.lot'          => 'nullable|string|max:100',
            'components.*.institution'  => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->components as $comp) {
                ComponentCollection::create([
                    'blood_group_id'      => $request->blood_group_id,
                    'blood_collection_id' => $request->blood_collection_id,
                    'donor_id'            => $request->donor_id,
                    'component_id'        => $comp['component_id'],
                    'volume'              => $comp['volume'] ?? 0,
                    'unit'                => $comp['unit'] ?? 'ML',
                    'lot'                 => $comp['lot'] ?? null,
                    'institution'         => $comp['institution'] ?? null,
                    'notes'               => $request->notes ?? null,
                    'datetime'            => $request->datetime,
                    'created_by'          => Auth::id(),
                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', 'Components added successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Failed to add components. Please try again.');
        }
    }

    public function destroy(ComponentCollection $component_collection)
    {
        $component_collection->delete();

        return redirect()->back()->with('success', 'Component collection deleted successfully.');
    }
}
