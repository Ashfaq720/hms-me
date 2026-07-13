<div class="card-body bg-white p-3">

    {{-- Toolbar --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div class="search-box-wrap">
            <input type="text" class="form-control form-control-sm search-input"
                placeholder="Search today's appointments...">
        </div>
    </div>

    {{-- Today Appointment Table --}}
    <div class="table-responsive apt-table-wrap">
        <table class="table table-sm align-middle apt-table mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Patient Name</th>
                    <th>Doctor</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Priority</th>
                    <th>Specialist</th>
                    <th>Fees</th>
                    <th>Source</th>
                    <th>Apt. Status</th>
                    <th>Visit Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($today_appointments ?? [] as $key => $ap)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $ap->patient?->patient_name ?? 'N/A' }}</td>
                        <td>{{ $ap->doctorRelation?->name ?? 'N/A' }}</td>
                        <td>{{ $ap->date?->format('d M Y h:i A') ?? '-' }}</td>
                        <td>{{ $ap->time ?? '-' }}</td>
                        <td>
                            @if ($ap->priority === 'Urgent')
                                <span class="badge bg-danger-subtle text-danger">Urgent</span>
                            @else
                                <span class="badge bg-info-subtle text-info">Normal</span>
                            @endif
                        </td>
                        <td>{{ $ap->specialist }}</td>
                        <td>{{ $ap->amount }}</td>
                        <td>{{ $ap->source }}</td>
                        <td>
                            @php($as = $ap->appointment_status ?? 'Pending')
                            <span class="badge status-badge
                                {{ match($as) {
                                    'Approved'  => 'bg-success-subtle text-success',
                                    'Rejected'  => 'bg-danger-subtle text-danger',
                                    'Cancelled' => 'bg-secondary-subtle text-secondary',
                                    default     => 'bg-warning-subtle text-warning',
                                } }}">
                                {{ $as }}
                            </span>
                        </td>
                        <td>
                            @php($vs = $ap->visit_status ?? 'booked')
                            <span class="badge status-badge
                                {{ match($vs) {
                                    'booked'          => 'bg-primary-subtle text-primary',
                                    'checked_in'      => 'bg-info-subtle text-info',
                                    'in_consultation' => 'bg-warning-subtle text-warning',
                                    'completed'       => 'bg-success-subtle text-success',
                                    'cancelled'       => 'bg-danger-subtle text-danger',
                                    'no_show'         => 'bg-secondary-subtle text-secondary',
                                    default           => 'bg-secondary-subtle text-secondary',
                                } }}">
                                {{ str_replace('_', ' ', ucfirst($vs)) }}
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('appointments.edit', $ap->id) }}"
                                    class="btn btn-sm btn-outline-warning action-btn" title="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                </a>

                                <form action="{{ route('appointments.destroy', $ap->id) }}?tab=today"
                                    method="POST" class="d-inline"
                                    onsubmit="return confirm('Delete this appointment?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger action-btn"
                                        title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="p-0 border-0">
                            <div class="empty-state-wrapper">
                                <div class="empty-title">No data available in table</div>
                                <div class="empty-illustration">
                                    <div class="folder-box">
                                        <div class="paper paper-1"></div>
                                        <div class="paper paper-2"></div>
                                        <div class="paper paper-3"></div>
                                        <div class="folder-front"></div>
                                    </div>
                                </div>
                                <div class="empty-message">
                                    <i class="bi bi-arrow-left-short"></i>
                                    Add new appointment or search with different criteria.
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 table-footer mt-2 pt-3 border-top">
        <div class="record-info">
            Records: {{ isset($today_appointments) ? $today_appointments->count() : 0 }}
        </div>
    </div>
</div>
