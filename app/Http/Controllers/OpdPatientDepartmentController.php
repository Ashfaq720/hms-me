<?php

// app/Http/Controllers/OpdPatientDepartmenController.php
namespace App\Http\Controllers;

use App\Models\OpdPatientDepartment;
use App\Models\Patient;
use App\Models\Doctor;
use App\Http\Requests\OpdPatientDepartmentStoreRequest;
use App\Http\Requests\OpdPatientDepartmentUpdateRequest;

class OpdPatientDepartmentController extends Controller
{
    public function index()
    {
        $rows = OpdPatientDepartment::with(['patient','doctor'])
            ->latest('appointment_date')
            ->paginate(20);

        return view('opd_patient_departments.index', compact('rows'));
    }

    public function create()
    {
        $patients = Patient::select('id','patient_name')->orderBy('patient_name')->get();
        $doctors  = Doctor::select('id','name')->orderBy('name')->get();

        return view('opd_patient_departments.create', compact('patients','doctors'));
    }

    public function store(OpdPatientDepartmentStoreRequest $request)
    {
        $data = $request->validated();

        OpdPatientDepartment::create($data);

        return redirect()
            ->route('opd-patient-departments.index')
            ->with('success', 'OPD record created successfully.');
    }

    public function show(OpdPatientDepartment $opdPatientDepartment)
    {
        $opdPatientDepartment->load(['patient','doctor']);
        return view('opd_patient_departments.show', compact('opdPatientDepartment'));
    }

    public function edit(OpdPatientDepartment $opdPatientDepartment)
    {
        $patients = Patient::select('id','patient_name')->orderBy('patient_name')->get();
        $doctors  = Doctor::select('id','name')->orderBy('name')->get();

        return view('opd_patient_departments.edit', compact('opdPatientDepartment','patients','doctors'));
    }

    public function update(OpdPatientDepartmentUpdateRequest $request, OpdPatientDepartment $opdPatientDepartment)
    {
        $data = $request->validated();

        $opdPatientDepartment->update($data);

        return redirect()
            ->route('opd-patient-departments.index')
            ->with('success', 'OPD record updated successfully.');
    }

    public function destroy(OpdPatientDepartment $opdPatientDepartment)
    {
        $opdPatientDepartment->delete();

        return redirect()
            ->route('opd-patient-departments.index')
            ->with('success', 'OPD record deleted successfully.');
    }
}
