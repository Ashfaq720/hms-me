@php
    $p = $patient->patient;
@endphp

<div class="p-4 bg-white">
    <div class="row g-4">
        <div class="col-md-6">
            <table class="table table-borderless mb-0">
                <tr>
                    <th width="40%" class="fw-bold">OPD Checkup ID</th>
                    <td>{{ $patient->checkup_id ?? 'CHKID' . $patient->id }}</td>
                </tr>
                <tr>
                    <th class="fw-bold">Case ID</th>
                    <td>{{ $patient->case_id ?? '' }}</td>
                </tr>
                <tr>
                    <th class="fw-bold">Old Patient</th>
                    <td>{{ ucfirst($patient->old_patient ?? '') }}</td>
                </tr>
                <tr>
                    <th class="fw-bold">Gender</th>
                    <td>{{ $p->gender ?? '' }}</td>
                </tr>
                <tr>
                    <th class="fw-bold">Phone</th>
                    <td>{{ $p->mobileno ?? '' }}</td>
                </tr>
                <tr>
                    <th class="fw-bold">Address</th>
                    <td>{{ $p->address ?? '' }}</td>
                </tr>
                <tr>
                    <th class="fw-bold">Blood Group</th>
                    <td>{{ $p->blood_group ?? '' }}</td>
                </tr>
                <tr>
                    <th class="fw-bold">Appointment Date</th>
                    <td>
                        {{ !empty($patient->appointment_date) ? \Carbon\Carbon::parse($patient->appointment_date)->format('m/d/Y h:i A') : '' }}
                    </td>
                </tr>
                <tr>
                    <th class="fw-bold">Casualty</th>
                    <td>{{ ucfirst($patient->casualty ?? 'No') }}</td>
                </tr>
                <tr>
                    <th class="fw-bold">TPA</th>
                    <td>{{ $p->organization_name ?? '' }}</td>
                </tr>
                <tr>
                    <th class="fw-bold">Is Antenatal</th>
                    <td>{{ ucfirst($patient->is_antenatal ?? 'No') }}</td>
                </tr>
                <tr>
                    <th class="fw-bold">Note</th>
                    <td>{{ $patient->note ?? '' }}</td>
                </tr>
                <tr>
                    <th class="fw-bold">Symptoms</th>
                    <td>{{ $patient->symptoms ?? '' }}</td>
                </tr>
                <tr>
                    <th class="fw-bold">Previous Medical Issue</th>
                    <td>{{ $patient->previous_medical_issue ?? '' }}</td>
                </tr>
            </table>
        </div>

        <div class="col-md-6">
            <table class="table table-borderless mb-0">
                <tr>
                    <th width="40%" class="fw-bold">OPD ID</th>
                    <td>{{ $patient->opd_no ?? 'OPDN' . $patient->id }}</td>
                </tr>
                <tr>
                    <th class="fw-bold">Patient Name</th>
                    <td>{{ $p->patient_name ?? '' }}</td>
                </tr>
                <tr>
                    <th class="fw-bold">Guardian Name</th>
                    <td>{{ $p->guardian_name ?? '' }}</td>
                </tr>
                <tr>
                    <th class="fw-bold">Marital Status</th>
                    <td>{{ $p->marital_status ?? '' }}</td>
                </tr>
                <tr>
                    <th class="fw-bold">Email</th>
                    <td>{{ $p->email ?? '' }}</td>
                </tr>
                <tr>
                    <th class="fw-bold">Age</th>
                    <td>{{ $p->age ?? '' }}</td>
                </tr>
                <tr>
                    <th class="fw-bold">Known Allergies</th>
                    <td>{{ $patient->known_allergies ?? '' }}</td>
                </tr>
                <tr>
                    <th class="fw-bold">Case</th>
                    <td>{{ $patient->case ?? '' }}</td>
                </tr>
                <tr>
                    <th class="fw-bold">Reference</th>
                    <td>{{ $patient->reference ?? '' }}</td>
                </tr>
                <tr>
                    <th class="fw-bold">Consultant Doctor</th>
                    <td>{{ $patient->doctor->name ?? '' }} {{ $patient->doctor->surname ?? '' }}</td>
                </tr>
            </table>
        </div>
    </div>
</div>
