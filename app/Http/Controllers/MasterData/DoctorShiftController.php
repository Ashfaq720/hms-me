<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Shift;
use Illuminate\Http\Request;

class DoctorShiftController extends Controller
{
    public function index()
    {
        $shifts  = Shift::where('is_active', 1)->orderBy('id')->get();
        $doctors = Doctor::with('shifts:id')->orderBy('name')->get();

        return view('doctor-shifts.index', compact('shifts', 'doctors'));
    }

    public function toggle(Request $request)
    {
        $data = $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'shift_id'  => 'required|exists:shifts,id',
            'assigned'  => 'required|boolean',
        ]);

        $doctor = Doctor::findOrFail($data['doctor_id']);

        if ($data['assigned']) {
            $doctor->shifts()->syncWithoutDetaching([$data['shift_id']]);
        } else {
            $doctor->shifts()->detach($data['shift_id']);
        }

        return response()->json([
            'success'  => true,
            'assigned' => (bool) $data['assigned'],
        ]);
    }
}
