<?php
namespace App\Http\Controllers;

use App\Models\Specialist;
use Illuminate\Http\Request;

class SpecialistController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $specialists = Specialist::all();
        return view('specialists.index', compact('specialists'));
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

        Specialist::create([
            'name' => $request->name,
        ]);

        return redirect()->route('specialists.index')
            ->with('success', 'Specialist created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Specialist $specialist)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Specialist $specialist)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Specialist $specialist)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $specialist->update([
            'name' => $request->name,
        ]);

        return redirect()->route('specialists.index')
            ->with('success', 'Specialist updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Specialist $specialist)
    {
        $specialist->delete();
        return redirect()->route('specialists.index')
            ->with('success', 'Specialist deleted successfully.');
    }
}
