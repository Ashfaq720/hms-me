<?php

namespace App\Http\Controllers\ICU;

use App\Http\Controllers\Controller;
use App\Models\Icu\IcuAdmission;
use App\Models\Icu\IcuInfectionExposureLog;
use App\Models\Icu\IcuInfectionRecord;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class IcuExposureController extends Controller
{
    public function index($admissionId)
    {
        $admission = IcuAdmission::findOrFail($admissionId);

        $logs = IcuInfectionExposureLog::with('infectionRecord')
            ->where('icu_admission_id', $admission->id)
            ->orderByDesc('exposure_time')
            ->get();

        $infectionRecords = IcuInfectionRecord::where('icu_admission_id', $admission->id)
            ->orderByDesc('id')->get();

        return view('icu.exposure.index', compact('admission', 'logs', 'infectionRecords'));
    }

    public function store(Request $request, $admissionId)
    {
        $request->validate([
            'infection_record_id'  => ['nullable', 'integer', 'exists:icu_infection_records,id'],
            'exposure_type'        => ['required', Rule::in(['SamePatient', 'SameBed', 'SameUnit', 'SameStaff', 'SameEquipment', 'Other'])],
            'related_patient_id'   => ['nullable', 'integer'],
            'related_bed_id'       => ['nullable', 'integer'],
            'related_equipment_id' => ['nullable', 'integer'],
            'related_staff_id'     => ['nullable', 'integer'],
            'exposure_time'        => ['required', 'date'],
            'remarks'              => ['nullable', 'string', 'max:500'],
        ]);

        $admission = IcuAdmission::findOrFail($admissionId);

        IcuInfectionExposureLog::create([
            'icu_admission_id'     => $admission->id,
            'icu_case_id'          => $admission->icu_case_id,
            'patient_id'           => $admission->patient_id,
            'infection_record_id'  => $request->infection_record_id,
            'exposure_type'        => $request->exposure_type,
            'related_patient_id'   => $request->related_patient_id,
            'related_bed_id'       => $request->related_bed_id,
            'related_equipment_id' => $request->related_equipment_id,
            'related_staff_id'     => $request->related_staff_id,
            'exposure_time'        => $request->exposure_time,
            'remarks'              => $request->remarks,
            'created_by'           => auth()->id(),
        ]);

        return back()->with('success', 'Exposure recorded.');
    }
}
