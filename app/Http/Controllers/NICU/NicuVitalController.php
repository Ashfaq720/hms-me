<?php

namespace App\Http\Controllers\NICU;

use App\Http\Controllers\Controller;
use App\Models\Nicu\NicuVital;
use App\Models\NicuAdmission;
use Illuminate\Http\Request;

class NicuVitalController extends Controller
{
    public function index(Request $request)
    {
        $this->gate('nicu_access');

        $q = NicuVital::with(['admission.baby:id,patient_name', 'recordedBy:id,name']);

        if ($request->filled('admission_id')) {
            $q->where('nicu_admission_id', $request->get('admission_id'));
        }

        $vitals = $q->latest('recorded_at')->paginate(30)->appends($request->query());
        $activeAdmissions = NicuAdmission::with('baby:id,patient_name')
            ->whereIn('status', [NicuAdmission::STATUS_ADMITTED, NicuAdmission::STATUS_IN_PROGRESS])
            ->orderBy('admission_no')->get();

        return view('nicu.vitals.index', compact('vitals', 'activeAdmissions'));
    }

    public function store(Request $request)
    {
        $this->gate('nicu_edit');

        $data = $request->validate([
            'nicu_admission_id' => ['required', 'exists:nicu_admissions,id'],
            'recorded_at'       => ['required', 'date'],
            'heart_rate'        => ['nullable', 'integer', 'min:30', 'max:300'],
            'respiratory_rate'  => ['nullable', 'integer', 'min:5', 'max:120'],
            'temperature_c'     => ['nullable', 'numeric', 'min:30', 'max:42'],
            'spo2'              => ['nullable', 'integer', 'min:50', 'max:100'],
            'systolic'          => ['nullable', 'integer', 'min:30', 'max:200'],
            'diastolic'         => ['nullable', 'integer', 'min:20', 'max:150'],
            'blood_glucose'     => ['nullable', 'numeric', 'min:0', 'max:600'],
            'notes'             => ['nullable', 'string', 'max:2000'],
        ]);

        $data['recorded_by'] = auth()->id();
        NicuVital::create($data);

        return back()->with('success', 'Vitals recorded.');
    }

    protected function gate(string $permission): void
    {
        if (auth()->check() && ! auth()->user()->can($permission)) {
            abort(403, "Missing permission: {$permission}");
        }
    }
}
