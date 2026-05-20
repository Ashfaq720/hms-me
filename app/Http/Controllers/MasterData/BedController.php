<?php
namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Bed;
use App\Models\BedGroup;
use App\Models\BedType;
use Illuminate\Http\Request;

class BedController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data      = Bed::all();
        $bedTypes  = BedType::all();
        $bedGroups = BedGroup::all();
        return view('bed.index', compact('data', 'bedTypes', 'bedGroups'));
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
            'name'         => 'required|string|max:255',
            'rent'         => 'required|integer',
            'bed_type_id'  => 'required|exists:bed_types,id',
            'bed_group_id' => 'required|exists:bed_groups,id',
            'is_reserved'  => 'required|boolean',
        ]);

        Bed::create([
            'name'         => $request->name,
            'rent'         => $request->rent,
            'bed_type_id'  => $request->bed_type_id,
            'bed_group_id' => $request->bed_group_id,
            'is_reserved'  => $request->is_reserved,
        ]);

        return redirect()->route('beds.index')->with('success', 'Bed created successfully.');
    }

/**
 * Display the specified resource.
 */
    public function show(Bed $bed)
    {
        //
    }

/**
 * Show the form for editing the specified resource.
 */
    public function edit(Bed $bed)
    {
        //
    }

/**
 * Update the specified resource in storage.
 */
    public function update(Request $request, Bed $bed)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'rent'         => 'required|integer',
            'bed_type_id'  => 'required|exists:bed_types,id',
            'bed_group_id' => 'required|exists:bed_groups,id',
            'is_reserved'  => 'required|boolean',
        ]);

        $bed->update([
            'name'         => $request->name,
            'rent'         => $request->rent,
            'bed_type_id'  => $request->bed_type_id,
            'bed_group_id' => $request->bed_group_id,
            'is_reserved'  => $request->is_reserved,
        ]);

        return redirect()->route('beds.index')->with('success', 'Bed updated successfully.');
    }

/**
 * Remove the specified resource from storage.
 */
    public function destroy(Bed $bed)
    {
        $bed->delete();
        return redirect()->route('beds.index')->with('success', 'Bed deleted successfully.');
    }
}
