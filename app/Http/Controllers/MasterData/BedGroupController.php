<?php
namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\BedGroup;
use App\Models\Floor;
use Illuminate\Http\Request;

class BedGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data   = BedGroup::all();
        $floors = Floor::all();
        return view('bedGroup.index', compact('data', 'floors'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'floor_id' => 'required|exists:floors,id',
        ]);

        BedGroup::create([
            'name'     => $request->name,
            'floor_id' => $request->floor_id,
        ]);

        return redirect()->route('bed-groups.index')
            ->with('success', 'Bed Group created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(BedGroup $bedGroup)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BedGroup $bedGroup)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BedGroup $bedGroup)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'floor_id' => 'required|exists:floors,id',
        ]);

        $bedGroup->update([
            'name'     => $request->name,
            'floor_id' => $request->floor_id,
        ]);

        return redirect()->route('bed-groups.index')
            ->with('success', 'Bed Group updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BedGroup $bedGroup)
    {
        $bedGroup->delete();
        return redirect()->route('bed-groups.index')
            ->with('success', 'Bed Group deleted successfully.');
    }
}
