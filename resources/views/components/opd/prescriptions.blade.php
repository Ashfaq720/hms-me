@props(['opdPatient'])

@php($prescriptions = $opdPatient->prescriptions ?? collect())

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold">
            <i class="bi bi-capsule me-2 text-primary"></i>Prescriptions
            <span class="badge bg-primary-subtle text-primary ms-2">{{ $prescriptions->count() }}</span>
        </h6>
        <a href="{{ route('opd-patients.prescriptions.create', $opdPatient->id) }}" class="btn btn-sm btn-primary">
            <i class="bi bi-plus"></i> Add Prescription
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0 custom-table">
                <thead>
                    <tr>
                        <th>SN</th>
                        <th>Prescription No</th>
                        <th>Date</th>
                        <th>Doctor</th>
                        <th>Symptoms</th>
                        <th>Medicines</th>
                        <th>Lab Investigations</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($prescriptions as $prescription)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $prescription->prescription_no ?? 'N/A' }}</td>
                            <td>{{ $prescription->date ? $prescription->date->format('d M Y') : 'N/A' }}</td>
                            <td>{{ $prescription->doctor->name ?? 'N/A' }}</td>
                            <td>
                                @foreach ($prescription->symptoms as $ps)
                                    <span class="badge bg-info text-dark">{{ $ps->symptom->name ?? 'N/A' }}</span>
                                @endforeach
                            </td>
                            <td>
                                @foreach ($prescription->medicines as $pm)
                                    <span class="badge bg-success">{{ $pm->medicine->medicine_name ?? 'N/A' }}</span>
                                @endforeach
                            </td>
                            <td>
                                @foreach ($prescription->labInvestigations as $pl)
                                    <span class="badge bg-warning text-dark">{{ $pl->labInvestigation->name ?? 'N/A' }}</span>
                                @endforeach
                            </td>
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    <a href="{{ route('opd-patients.prescriptions.show', [$opdPatient->id, $prescription->id]) }}"
                                        class="btn btn-sm btn-outline-info" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('opd-patients.prescriptions.pdf', [$opdPatient->id, $prescription->id]) }}"
                                        class="btn btn-sm btn-outline-warning" title="Download PDF" target="_blank">
                                        <i class="bi bi-file-earmark-pdf"></i>
                                    </a>
                                    <form action="{{ route('opd-patients.prescriptions.destroy', [$opdPatient->id, $prescription->id]) }}"
                                        method="POST" class="d-inline"
                                        onsubmit="return confirm('Are you sure you want to delete this prescription?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-3">No prescriptions available.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
