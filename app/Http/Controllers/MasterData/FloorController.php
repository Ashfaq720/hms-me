<?php
namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Floor;
use Illuminate\Http\Request;

class FloorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $floors = Floor::all();
        return view('floor.index', compact('floors'));
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
            'name' => 'required|string|max:255',
        ]);

        Floor::create([
            'name' => $request->name,
        ]);

        return redirect()->route('floors.index')
            ->with('success', 'Floor created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Floor $floor)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Floor $floor)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Floor $floor)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $floor->update([
            'name' => $request->name,
        ]);

        return redirect()->route('floors.index')
            ->with('success', 'Floor updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Floor $floor)
    {
        $floor->delete();
        return redirect()->route('floors.index')
            ->with('success', 'Floor deleted successfully.');
    }
}
