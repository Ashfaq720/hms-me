<div class="table-responsive">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h6 class="mb-0">Operation History</h6>
        </div>
        <div>
            @if (strcasecmp($iPDPatient->status, 'Admitted') === 0)
                <a data-size="lg" class="btn btn-primary px-2 w-100 w-sm-auto"
                    data-url="{{ route('ipd-patients.case-operations', $iPDPatient->id) }}" data-ajax-popup="true"
                    data-title="Add Operation" data-bs-toggle="tooltip" title="Add Operation"
                    data-original-title="Add Operation"><i class="bi bi-plus-lg me-1"></i>
                    Add Operation</a>
            @endif
        </div>
    </div>

    <table class="table table-sm table-bordered align-middle">
        <thead class="table-light">
            <tr>
                <th width="3%">SN</th>
                <th width="8%">Date</th>
                <th>Category</th>
                <th width="10%">Operation</th>
                <th width="10%">Procedure</th>
                <th width="8%">Theatre</th>
                <th width="8%">Surgeon</th>
                <th width="8%">Anesthesiologist</th>
                <th width="6%">Status</th>
                <th width="5%">Pre-Op</th>
                <th width="5%">Vitals</th>
                <th width="5%">Consent</th>
                <th width="10%">Diagnosis</th>
                <th width="5%">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($iPDPatient->operationHistories->sortByDesc('date') as $op)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $op->date ? $op->date->format('d M Y') : 'N/A' }}</td>
                    <td>{{ $op->operationType->name ?? '-' }}</td>
                    <td>{{ $op->operation->name ?? '-' }}</td>
                    <td>{{ $op->operationProcedure->name ?? '-' }}</td>
                    <td>{{ $op->operationTheatre->name ?? '-' }}</td>
                    <td>{{ $op->mainSurgeon->name ?? '-' }}</td>
                    <td>{{ $op->anesthesiologist->name ?? '-' }}</td>
                    <td>
                        @if ($op->status === 'Completed')
                            <span class="badge bg-success">{{ $op->status }}</span>
                        @elseif($op->status === 'In Progress')
                            <span class="badge bg-warning text-dark">{{ $op->status }}</span>
                        @elseif($op->status === 'Cancelled')
                            <span class="badge bg-danger">{{ $op->status }}</span>
                        @else
                            <span class="badge bg-info">{{ $op->status }}</span>
                        @endif
                    </td>
                    <td class="text-center">{!! $op->pre_op ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-x-circle text-muted"></i>' !!}</td>
                    <td class="text-center">{!! $op->vitals ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-x-circle text-muted"></i>' !!}</td>
                    <td class="text-center">{!! $op->consent ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-x-circle text-muted"></i>' !!}</td>
                    <td>{{ Str::limit($op->diagnosis, 40) ?? '-' }}</td>
                    <td class="text-center">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item"
                                        href="{{ route('ipd-patients.case-operations.show', [$iPDPatient->id, $op->id]) }}">
                                        <i class="bi bi-eye text-info me-2"></i> View
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" data-size="lg"
                                        data-url="{{ route('ipd-patients.case-operations.edit', [$iPDPatient->id, $op->id]) }}"
                                        data-ajax-popup="true" data-title="Edit Operation" href="#">
                                        <i class="bi bi-pencil text-primary me-2"></i> Edit
                                    </a>
                                </li>
                                <li>
                                    <form
                                        action="{{ route('ipd-patients.case-operations.destroy', [$iPDPatient->id, $op->id]) }}"
                                        method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this operation record?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item">
                                            <i class="bi bi-trash text-danger me-2"></i> Delete
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="13" class="text-center text-muted">No operation records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Detail Cards --}}
    {{-- @foreach ($iPDPatient->operationHistories->sortByDesc('date') as $op)
        <div class="card mb-3 border">
            <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                <div>
                    <strong>{{ $op->operation->name ?? 'Operation' }}</strong>
                    <span class="text-muted ms-2">{{ $op->date ? $op->date->format('d M Y') : '' }}</span>
                    @if ($op->start_datetime && $op->end_datetime)
                        <span class="text-muted ms-2">
                            <i class="bi bi-clock"></i>
                            {{ $op->start_datetime->format('h:i A') }} - {{ $op->end_datetime->format('h:i A') }}
                        </span>
                    @endif
                </div>
                <div>
                    @if ($op->status === 'Completed')
                        <span class="badge bg-success">{{ $op->status }}</span>
                    @elseif($op->status === 'In Progress')
                        <span class="badge bg-warning text-dark">{{ $op->status }}</span>
                    @elseif($op->status === 'Cancelled')
                        <span class="badge bg-danger">{{ $op->status }}</span>
                    @else
                        <span class="badge bg-info">{{ $op->status }}</span>
                    @endif
                </div>
            </div>
            <div class="card-body py-2">
                <div class="row g-2 small">
                    <div class="col-md-3"><strong>Type:</strong> {{ $op->operationType->name ?? '-' }}</div>
                    <div class="col-md-3"><strong>Procedure:</strong> {{ $op->operationProcedure->name ?? '-' }}</div>
                    <div class="col-md-3"><strong>Theatre:</strong> {{ $op->operationTheatre->name ?? '-' }}</div>
                    <div class="col-md-3"><strong>OT Technician:</strong> {{ $op->ot_technician ?? '-' }}</div>

                    <div class="col-md-3"><strong>Assign Doctor:</strong> {{ $op->assignDoctor->name ?? '-' }}</div>
                    <div class="col-md-3"><strong>Assistant Doctor:</strong> {{ $op->assistantDoctor->name ?? '-' }}</div>
                    <div class="col-md-3"><strong>Main Surgeon:</strong> {{ $op->mainSurgeon->name ?? '-' }}</div>
                    <div class="col-md-3"><strong>Anesthesiologist:</strong> {{ $op->anesthesiologist->name ?? '-' }}</div>

                    @if ($op->diagnosis)
                        <div class="col-12"><strong>Diagnosis:</strong> {{ $op->diagnosis }}</div>
                    @endif
                    @if ($op->remarks)
                        <div class="col-12"><strong>Remarks:</strong> {{ $op->remarks }}</div>
                    @endif
                </div>
            </div>
        </div>
    @endforeach --}}
</div>
