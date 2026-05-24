<?php

namespace App\Modules\Ambulance\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Ambulance\Models\AmbulanceTrip;
use App\Modules\Ambulance\Models\ClinicalNote;
use Illuminate\Http\Request;

class ClinicalNoteController extends Controller
{
    public function create(AmbulanceTrip $trip)
    {
        // CLIN-BR-001: only capture during active transport
        if ($trip->status === 'COMPLETED') {
            return redirect()->route('amb.trips.show', $trip)
                ->with('error', 'Cannot add clinical notes to a completed trip.');
        }

        return view('backend.ambulance.clinical_notes.create', compact('trip'));
    }

    public function store(Request $request, AmbulanceTrip $trip)
    {
        if ($trip->status === 'COMPLETED') {
            return redirect()->route('amb.trips.show', $trip)
                ->with('error', 'Trip is completed. Notes are read-only.');
        }

        $data = $request->validate([
            'bp'                     => 'nullable|string|max:20',
            'pulse'                  => 'nullable|integer|min:0|max:300',
            'spo2'                   => 'nullable|numeric|min:0|max:100',
            'temperature'            => 'nullable|numeric|min:90|max:110',
            'respiratory_rate'       => 'nullable|integer|min:0|max:100',
            'oxygen_given'           => 'boolean',
            'ventilator_used'        => 'boolean',
            'emergency_intervention' => 'required|in:NONE,CPR,OXYGEN,IV_SUPPORT,DEFIBRILLATION,OTHER',
            'clinical_notes'         => 'nullable|string|max:2000',
        ]);

        $data['trip_id']     = $trip->id;
        $data['recorded_by'] = auth()->id();
        $data['oxygen_given']    = $request->boolean('oxygen_given');
        $data['ventilator_used'] = $request->boolean('ventilator_used');

        ClinicalNote::create($data);

        return redirect()->route('amb.trips.show', $trip)
            ->with('success', 'Clinical notes saved.');
    }
}
