<?php

namespace App\Http\Controllers\FrontDesk;

use App\Http\Controllers\Controller;
use App\Models\FrontDesk\ErPatient;
use App\Models\FrontDesk\VitalCheck;
use App\Models\IpdPatient;
use App\Models\OpdPatient;
use App\Models\Patient;
use Illuminate\Http\Request;

class LiveVitalsController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $today = now()->toDateString();

        $opdQueue = OpdPatient::with(['patient', 'doctor', 'department'])
            ->whereDate('created_at', $today)
            ->withCount([
                'vitalChecks as vitals_count' => fn($q) => $q->whereDate('checked_at', $today),
                'charges as paid_charges'     => fn($q) => $q->where('is_paid', true),
            ])
            ->latest('id')
            ->get()
            ->map(fn($r) => (object)[
                'source_type'   => 'OPD',
                'source_id'     => $r->id,
                'patient_id'    => $r->patient_id,
                'patient'       => $r->patient,
                'doctor'        => $r->doctor,
                'department'    => $r->department,
                'token'         => $r->token_no ?? '-',
                'registered_at' => $r->created_at,
                'vitals_done'   => $r->vitals_count > 0,
                'billing_ok'    => $r->paid_charges > 0,
            ]);

        $ipdQueue = IpdPatient::with(['patient', 'doctor', 'department'])
            ->whereDate('admission_date', $today)
            ->withCount([
                'vitalChecks as vitals_count' => fn($q) => $q->whereDate('checked_at', $today),
                'charges as paid_charges'     => fn($q) => $q->where('is_paid', true),
            ])
            ->latest('id')
            ->get()
            ->map(fn($r) => (object)[
                'source_type'   => 'IPD',
                'source_id'     => $r->id,
                'patient_id'    => $r->patient_id,
                'patient'       => $r->patient,
                'doctor'        => $r->doctor,
                'department'    => $r->department,
                'token'         => $r->ipd_no ?? '-',
                'registered_at' => $r->admission_date ?? $r->created_at,
                'vitals_done'   => $r->vitals_count > 0,
                'billing_ok'    => $r->paid_charges > 0,
            ]);

        $erQueue = ErPatient::with(['patient'])
            ->whereDate('created_at', $today)
            ->latest('id')
            ->get()
            ->map(function ($r) use ($today) {
                $vitals = VitalCheck::where('patient_id', $r->patient_id)
                    ->where('patient_type', 'ER')
                    ->whereDate('checked_at', $today)
                    ->count();

                return (object)[
                    'source_type'   => 'ER',
                    'source_id'     => $r->id,
                    'patient_id'    => $r->patient_id,
                    'patient'       => $r->patient,
                    'doctor'        => null,
                    'department'    => null,
                    'token'         => 'ER',
                    'registered_at' => $r->created_at,
                    'vitals_done'   => $vitals > 0,
                    'billing_ok'    => false,
                ];
            });

        $fullQueue = collect()
            ->merge($opdQueue)
            ->merge($ipdQueue)
            ->merge($erQueue)
            ->sortBy(fn($r) => [$r->vitals_done ? 1 : 0, $r->registered_at])
            ->values();

        $stats = [
            'waiting' => $fullQueue->filter(fn($r) => ! $r->vitals_done)->count(),
            'done'    => $fullQueue->filter(fn($r) => $r->vitals_done)->count(),
            'billed'  => $fullQueue->filter(fn($r) => $r->billing_ok)->count(),
            'total'   => $fullQueue->count(),
        ];

        // Manual pagination (the merged collection from 3 sources can't use Eloquent paginate)
        $perPage  = 25;
        $page     = (int) $request->input('page', 1);
        $queue = new \Illuminate\Pagination\LengthAwarePaginator(
            $fullQueue->forPage($page, $perPage)->values(),
            $fullQueue->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('front-desk.live-vitals.index', compact('queue', 'stats'));
    }

    public function patientLookup(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        if ($q === '') {
            return response()->json(['ok' => false, 'message' => 'Query required'], 422);
        }

        $patient = Patient::where('health_card_no', $q)
            ->orWhere('mrn', $q)
            ->orWhere('mobileno', 'like', "%{$q}%")
            ->orWhere('patient_name', 'like', "%{$q}%")
            ->first();

        if (! $patient) {
            return response()->json(['ok' => false, 'message' => 'Patient not found'], 404);
        }

        $today      = now()->toDateString();
        $sourceType = null;
        $sourceId   = null;
        $token      = null;

        $opd = OpdPatient::where('patient_id', $patient->id)
            ->whereDate('created_at', $today)
            ->latest('id')
            ->first();

        if ($opd) {
            $sourceType = 'OPD';
            $sourceId   = $opd->id;
            $token      = $opd->token_no;
        }

        if (! $sourceId) {
            $ipd = IPDPatient::where('patient_id', $patient->id)
                ->whereDate('admission_date', $today)
                ->latest('id')
                ->first();

            if ($ipd) {
                $sourceType = 'IPD';
                $sourceId   = $ipd->id;
                $token      = $ipd->ipd_no;
            }
        }

        if (! $sourceId) {
            $er = ErPatient::where('patient_id', $patient->id)
                ->whereDate('created_at', $today)
                ->latest('id')
                ->first();

            if ($er) {
                $sourceType = 'ER';
                $sourceId   = $er->id;
                $token      = 'ER';
            }
        }

        $vitalsToday = VitalCheck::where('patient_id', $patient->id)
            ->whereDate('checked_at', $today)
            ->latest('id')
            ->first();

        return response()->json([
            'ok'          => true,
            'patient'     => [
                'id'             => $patient->id,
                'name'           => $patient->patient_name,
                'mrn'            => $patient->mrn,
                'health_card_no' => $patient->health_card_no,
                'gender'         => $patient->gender,
                'mobileno'       => $patient->mobileno,
                'blood_group'    => $patient->blood_group,
            ],
            'source_type' => $sourceType,
            'source_id'   => $sourceId,
            'token'       => $token,
            'vitals_done' => (bool) $vitalsToday,
        ]);
    }

    public function fetchMachine(Request $request)
    {
        // Mock response — replace with real device SDK / serial-bridge URL in production
        return response()->json([
            'ok'        => true,
            'device_id' => $request->input('device_id', 'DEVICE-01'),
            'vitals'    => [
                'blood_pressure'   => rand(108, 138) . '/' . rand(68, 90),
                'temperature'      => number_format(rand(364, 379) / 10, 1),
                'heart_rate'       => rand(60, 100),
                'respiratory_rate' => rand(14, 20),
                'spo2'             => rand(96, 100),
                'weight'           => number_format(rand(450, 900) / 10, 1),
                'height'           => null,
            ],
            'fetched_at' => now()->format('H:i:s'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'patient_id'       => ['required', 'integer'],
            'patient_type'     => ['required', 'in:OPD,IPD,ER'],
            'source_id'        => ['nullable', 'integer'],
            'patient_token'    => ['nullable', 'string', 'max:30'],
            'gender'           => ['nullable', 'string', 'max:20'],
            'age'              => ['nullable', 'integer', 'min:0', 'max:150'],
            'weight'           => ['nullable', 'numeric', 'min:0'],
            'height'           => ['nullable', 'numeric', 'min:0'],
            'blood_pressure'   => ['nullable', 'string', 'max:20'],
            'temperature'      => ['nullable', 'numeric'],
            'heart_rate'       => ['nullable', 'integer', 'min:0'],
            'respiratory_rate' => ['nullable', 'integer', 'min:0'],
            'spo2'             => ['nullable', 'integer', 'min:0', 'max:100'],
            'remarks'          => ['nullable', 'string', 'max:3000'],
            'machine_fetched'  => ['nullable', 'boolean'],
            'machine_device_id' => ['nullable', 'string', 'max:100'],
        ]);

        $opdId = $ipdId = $erId = null;

        match ($data['patient_type']) {
            'OPD' => $opdId = $data['source_id'] ?? null,
            'IPD' => $ipdId = $data['source_id'] ?? null,
            'ER'  => $erId  = $data['source_id'] ?? null,
        };

        VitalCheck::create([
            'patient_id'        => $data['patient_id'],
            'patient_type'      => $data['patient_type'],
            'opd_patient_id'    => $opdId,
            'ipd_patient_id'    => $ipdId,
            'er_patient_id'     => $erId,
            'patient_token'     => $data['patient_token'] ?? null,
            'gender'            => $data['gender'] ?? null,
            'age'               => $data['age'] ?? null,
            'weight'            => $data['weight'] ?? null,
            'height'            => $data['height'] ?? null,
            'blood_pressure'    => $data['blood_pressure'] ?? null,
            'temperature'       => $data['temperature'] ?? null,
            'heart_rate'        => $data['heart_rate'] ?? null,
            'respiratory_rate'  => $data['respiratory_rate'] ?? null,
            'spo2'              => $data['spo2'] ?? null,
            'remarks'           => $data['remarks'] ?? null,
            'machine_fetched'   => $request->boolean('machine_fetched'),
            'machine_device_id' => $data['machine_device_id'] ?? null,
            'checked_by'        => auth()->id(),
            'checked_at'        => now(),
        ]);

        return redirect()->route('front_desk.live-vitals.index')
            ->with('success', 'Vitals recorded successfully.');
    }
}
