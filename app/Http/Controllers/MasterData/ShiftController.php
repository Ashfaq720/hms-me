<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index()
    {
        $shifts = Shift::orderBy('id', 'desc')->get();
        return view('shifts.index', compact('shifts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'time_from' => 'nullable|date_format:H:i',
            'time_to'   => 'nullable|date_format:H:i',
        ]);

        Shift::create([
            'name'      => $request->name,
            'time_from' => $request->time_from,
            'time_to'   => $request->time_to,
            'is_active' => 1,
        ]);

        return redirect()->route('shifts.index')
            ->with('success', 'Shift created successfully.');
    }

    public function update(Request $request, Shift $shift)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'time_from' => 'nullable|date_format:H:i',
            'time_to'   => 'nullable|date_format:H:i',
        ]);

        $shift->update([
            'name'      => $request->name,
            'time_from' => $request->time_from,
            'time_to'   => $request->time_to,
        ]);

        return redirect()->route('shifts.index')
            ->with('success', 'Shift updated successfully.');
    }

    public function destroy(Shift $shift)
    {
        $shift->delete();
        return redirect()->route('shifts.index')
            ->with('success', 'Shift deleted successfully.');
    }
}
