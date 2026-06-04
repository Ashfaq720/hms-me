<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\AppointmentPriority;
use Illuminate\Http\Request;

class AppointmentPriorityController extends Controller
{
    public function index()
    {
        $priorities = AppointmentPriority::orderBy('id', 'desc')->get();
        return view('appointment-priorities.index', compact('priorities'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        AppointmentPriority::create([
            'name'      => $request->name,
            'is_active' => 1,
        ]);

        return redirect()->route('appointment-priorities.index')
            ->with('success', 'Appointment Priority created successfully.');
    }

    public function update(Request $request, AppointmentPriority $appointment_priority)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $appointment_priority->update([
            'name' => $request->name,
        ]);

        return redirect()->route('appointment-priorities.index')
            ->with('success', 'Appointment Priority updated successfully.');
    }

    public function destroy(AppointmentPriority $appointment_priority)
    {
        $appointment_priority->delete();
        return redirect()->route('appointment-priorities.index')
            ->with('success', 'Appointment Priority deleted successfully.');
    }
}
