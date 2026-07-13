<?php
namespace App\Http\Controllers\FrontDesk;

use App\Http\Controllers\Controller;
use App\Models\FrontDesk\ErPatient;
use App\Models\FrontDesk\VitalCheck;
use App\Models\IpdPatient;
use App\Models\OpdPatient;
use App\Models\Patient;
use Illuminate\Http\Request;

class VitalCheckController extends Controller
{
    private function patientModelClass(): string
    {
        return config('frontdesk.patient_model', \App\Models\Patient::class);
    }

    // modal view
    public function create()
    {
        $patients = Patient::select('id', 'patient_name', 'mobileno')->get();
        return view('front-desk.vitals.create', compact('patients'));
    }

    /**
     * Lookup by:
     * - token OR MRN OR phone OR patient_id
     * Returns patient basic info for autofill.
     */
    public function lookup(Request $request)
    {
        $q = trim((string) $request->get('q'));
        if ($q === '') {
            return response()->json(['ok' => true, 'data' => null]);
        }

        $patientModel = $this->patientModelClass();

        // patient_id direct
        $p = null;
        if (ctype_digit($q)) {
            $p = $patientModel::find((int) $q);
        }

        // MRN / phone / name
        if (! $p) {
            $p = $patientModel::query()
                ->where('mrn', 'like', "%{$q}%")
                ->orWhere('temp_id', 'like', "%{$q}%")
                ->orWhere('phone', 'like', "%{$q}%")
                ->orWhere('mobile', 'like', "%{$q}%")
                ->orWhere('name', 'like', "%{$q}%")
                ->orderByDesc('id')
                ->first();
        }

        if (! $p) {
            return response()->json(['ok' => false, 'message' => 'Patient not found'], 404);
        }

        return response()->json([
            'ok'   => true,
            'data' => [
                'patient_id' => $p->id,
                'mrn'        => $p->mrn ?? null,
                'name'       => $p->name ?? '',
                'gender'     => $p->gender ?? null,
                'age'        => $p->age ?? null, // যদি আপনার patient টেবিলে age না থাকে, UI তে manually দিবেন
                'phone'      => $p->phone ?? ($p->mobile ?? ''),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'patient_id'       => ['required', 'integer'],
            'patient_type'     => ['required', 'in:OPD,Ipd,ER'],
            'patient_token'    => ['nullable', 'string', 'max:30'],

            'gender'           => ['nullable', 'string', 'max:20'],
            'age'              => ['nullable', 'integer', 'min:0', 'max:150'],

            'weight'           => ['nullable', 'numeric', 'min:0'],
            'height'           => ['nullable', 'numeric', 'min:0'],
            'blood_pressure'   => ['nullable', 'string', 'max:20'],
            'temperature'      => ['nullable', 'numeric', 'min:0'],
            'heart_rate'       => ['nullable', 'integer', 'min:0'],
            'respiratory_rate' => ['nullable', 'integer', 'min:0'],
            'spo2'             => ['nullable', 'integer', 'min:0', 'max:100'],
            'remarks'          => ['nullable', 'string', 'max:3000'],
        ]);

        $opdId = $ipdId = $erId = null;

        if ($data['patient_type'] === 'OPD') {
            $opdId = OpdPatient::where('patient_id', $data['patient_id'])->latest('id')->value('id');

        } elseif ($data['patient_type'] === 'Ipd') {
            $ipdId = IpdPatient::where('patient_id', $data['patient_id'])->latest('id')->value('id');
        } else {
            $erId = ErPatient::where('patient_id', $data['patient_id'])->latest('id')->value('id');
        }

        VitalCheck::create([

            'patient_id'       => $data['patient_id'],
            'patient_type'     => $data['patient_type'],
            'opd_patient_id'   => $opdId,
            'ipd_patient_id'   => $ipdId,
            'er_patient_id'    => $erId,

            'patient_token'    => $data['patient_token'] ?? null,
            'gender'           => $data['gender'] ?? null,
            'age'              => $data['age'] ?? null,
            'weight'           => $data['weight'] ?? null,
            'height'           => $data['height'] ?? null,
            'blood_pressure'   => $data['blood_pressure'] ?? null,
            'temperature'      => $data['temperature'] ?? null,
            'heart_rate'       => $data['heart_rate'] ?? null,
            'respiratory_rate' => $data['respiratory_rate'] ?? null,
            'spo2'             => $data['spo2'] ?? null,
            'remarks'          => $data['remarks'] ?? null,

            'checked_by'       => auth()->id(),
            'checked_at'       => now(),
        ]);

        return back()->with('success', 'Vital check saved successfully.');

    }
}
