<?php

namespace App\Http\Controllers\OPD;

use App\Http\Controllers\Controller;
use App\Models\ConsultationNote;
use App\Models\OpdPatient;
use Illuminate\Http\Request;

class ConsultationNoteController extends Controller
{
    public function store(Request $request, OpdPatient $opdPatient)
    {
        $validated = $request->validate([
            'subjective'        => 'nullable|string',
            'objective'         => 'nullable|string',
            'assessment'        => 'nullable|string',
            'plan'              => 'nullable|string',
            'icd10_code'        => 'nullable|string|max:20',
            'icd10_description' => 'nullable|string|max:255',
        ]);

        ConsultationNote::updateOrCreate(
            ['opd_patient_id' => $opdPatient->id],
            array_merge($validated, [
                'status'     => 'draft',
                'created_by' => auth()->id(),
            ])
        );

        return redirect(route('opd-patients.show', $opdPatient->id) . '?tab=soap')
            ->with('success', 'SOAP note saved.');
    }

    public function close(Request $request, OpdPatient $opdPatient)
    {
        $note = ConsultationNote::where('opd_patient_id', $opdPatient->id)->firstOrFail();

        $note->update([
            'status'    => 'closed',
            'closed_at' => now(),
            'closed_by' => auth()->id(),
        ]);

        return redirect(route('opd-patients.show', $opdPatient->id) . '?tab=soap')
            ->with('success', 'Consultation closed successfully.');
    }
}
